<?php
namespace App\Services;

use App\Models\{Asistente, Bono, FichaAtencion, HoraMedica, Profesional};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, DB};
use Illuminate\Validation\ValidationException;

class RecepcionPagoVeterinario
{
    public function autorizar(HoraMedica $hora): void
    {
        abort_unless(Auth::check(), 403);
        if (Profesional::where('id_usuario', Auth::id())->whereKey($hora->id_profesional)->exists()) return;
        $asistente = Asistente::where('id_usuario', Auth::id())->first();
        abort_unless($asistente, 403);
        app(AutorizacionesAsistentes::class)->exigir($asistente, (int) $hora->id_lugar_atencion, (int) $hora->id_profesional, 'permiso_entrega_caja');
    }

    public function recibir(Request $request, int $idHora): array
    {
        $request->validate([
            'valor_atencion' => ['nullable', 'integer', 'min:0', 'max:2147483647'],
            'convenio_veterinario_id' => ['nullable', 'integer', 'min:1'],
            'numero_bono' => ['nullable', 'string', 'max:255'],
            'numero_presupuesto' => ['nullable', 'string', 'max:100'],
        ]);
        return DB::transaction(function () use ($request, $idHora) {
            // Serializar por hora evita que dos recepciones creen dos cobros.
            $hora = HoraMedica::whereKey($idHora)->lockForUpdate()->firstOrFail();
            $this->autorizar($hora);
            $tipo = $request->integer('id_tipo_bono') ?: 1;
            $bono = Bono::where('id_referencia', $hora->id)->where('id_profesional', $hora->id_profesional)
                ->where('devuelto', 0)->orderBy('id')->lockForUpdate()->first() ?? new Bono();
            $cerrada = $hora->id_ficha_atencion && FichaAtencion::whereKey($hora->id_ficha_atencion)->where('finalizada', 1)->exists();
            // Repetir la solicitud de un pago confirmado devuelve el mismo recibo.
            if ($bono->pago_confirmado_en) return $this->respuesta($bono, $hora, (bool) $cerrada);

            $valor = $request->filled('valor_atencion') ? (int) $request->valor_atencion : (int) $bono->valor_atencion;
            $qr = trim((string) $request->numero_bono);
            $presupuesto = trim((string) $request->numero_presupuesto);
            if ($valor <= 0 && $qr === '' && $presupuesto === '' && (int) $request->id_clase_bono !== 2) {
                throw ValidationException::withMessages(['valor_atencion' => 'Ingrese el valor, código QR o número de presupuesto.']);
            }
            if ($cerrada && !$request->filled('valor_atencion') && (!$bono->exists || (int) $bono->valor_atencion <= 0) && (int) $request->id_clase_bono !== 2) {
                throw ValidationException::withMessages(['valor_atencion' => 'Ingrese el valor para confirmar el pago.']);
            }
            $idConvenio = $request->exists('convenio_veterinario_id')
                ? ($request->integer('convenio_veterinario_id') ?: null) : ($bono->id_convenio_veterinario ?: null);
            if ($bono->exists && $idConvenio && (int) $bono->id_convenio_veterinario === (int) $idConvenio) {
                // Conservar el porcentaje acordado en una recepción anterior.
                $bono->valor_atencion = $valor;
                $bono->descuento_convenio_veterinario = (int) round($valor * (float) $bono->porcentaje_convenio_veterinario / 100);
                $bono->a_pagar = $valor - $bono->descuento_convenio_veterinario;
            } else {
                app(DescuentoConvenioVeterinario::class)->aplicar($bono, $hora, $idConvenio ? (int) $idConvenio : null, $valor);
            }
            $asistente = Asistente::where('id_usuario', Auth::id())->first();
            $bono->id_profesional = $hora->id_profesional;
            $bono->id_paciente = $hora->id_paciente;
            $bono->id_asistente = $asistente->id ?? ($hora->id_asistente ?: -1);
            $bono->id_referencia = $hora->id;
            $bono->id_tipo_bono = $bono->id_tipo_bono ?: $tipo;
            $bono->id_clase_bono = $request->id_clase_bono ?: ($bono->id_clase_bono ?: 1);
            $bono->convenio = 0; // No se utiliza previsión humana.
            $bono->numero_bono = $qr ?: ($bono->numero_bono ?: ($presupuesto ? 'PRESUPUESTO-'.$presupuesto : 'PAGO-MANUAL-'.$hora->id));
            $bono->numero_boleta = $request->numero_boleta ?: ($bono->numero_boleta ?: '0');
            $bono->fecha_atencion = $bono->fecha_atencion ?: now();
            $bono->numero_sesiones = $request->numero_sesiones ?: 0;
            $bono->bonificacion = 0;
            $bono->aporte_seguro = 0;
            $bono->devuelto = 0;
            $bono->rendido = $bono->rendido ?: 0;
            $bono->estado_consulta = $cerrada ? 6 : 2;
            $bono->glosa = $cerrada ? 'PAGO CONFIRMADO - ATENCION FINALIZADA' : 'RECEPCION PENDIENTE DE ATENCION';
            if ($cerrada) $bono->pago_confirmado_en = now();
            $bono->saveOrFail();
            // La recepción nunca retrocede una atención en curso o finalizada.
            if (!$cerrada && in_array((int) $hora->id_estado, [1, 2, 8, 16], true)) {
                $hora->id_estado = 4;
                $hora->saveOrFail();
            }
            return $this->respuesta($bono, $hora, (bool) $cerrada);
        });
    }

    private function respuesta(Bono $bono, HoraMedica $hora, bool $cerrada): array
    {
        return [
            'estado' => 1, 'bono' => ['estado' => 1, 'id' => $bono->id, 'a_pagar' => $bono->a_pagar],
            'pendiente_atencion' => !$cerrada, 'cobro_habilitado' => $cerrada,
            'pago_confirmado' => (bool) $bono->pago_confirmado_en,
            'msj' => $cerrada ? 'Pago confirmado.' : 'Recepción registrada. El pago se confirma después de finalizar la atención.',
            'color_hora' => (int) $hora->id_estado === 4 ? '#A06CC1' : null,
            'estado_hora' => (int) $hora->id_estado,
            'hora_medica' => ['estado' => 1, 'fecha_consulta' => $hora->fecha_consulta],
        ];
    }
}
