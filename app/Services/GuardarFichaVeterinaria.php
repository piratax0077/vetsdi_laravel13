<?php

namespace App\Services;

use App\Models\ControlPostQuirurgico;
use App\Models\FichaAtencion;
use App\Models\FichaCirugiaGeneralAdulto;
use App\Models\FichaVeterinariaGeneral;
use App\Models\HoraMedica;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class GuardarFichaVeterinaria
{
    public function guardar(Request $request, int $idProfesional, string $especialidad): FichaAtencion
    {
        if (is_string($request->input('descripcion_hipotesis'))) {
            $request->merge(['descripcion_hipotesis' => trim($request->input('descripcion_hipotesis'))]);
        }
        Validator::make($request->all(), [
            'descripcion_hipotesis' => 'required|string',
        ], ['descripcion_hipotesis.required' => 'El diagnóstico es obligatorio.'])->validate();

        $imagenes = $this->validarImagenes($request);
        return DB::transaction(function () use ($request, $idProfesional, $especialidad, $imagenes) {
            $hora = HoraMedica::whereKey($request->input('hora_medica'))->lockForUpdate()->firstOrFail();
            abort_unless((int) $hora->id_profesional === $idProfesional && $hora->id_mascota, 403);
            $mascota = \App\Models\Mascota::whereKey($hora->id_mascota)->lockForUpdate()->firstOrFail();
            abort_unless((int) $mascota->id_responsable === (int) $hora->id_paciente, 403, 'La hora no corresponde al tutor actual de la mascota.');
            abort_if($request->filled('id_mascota') && (int) $request->id_mascota !== (int) $hora->id_mascota, 422);

            $ficha = $hora->id_ficha_atencion ? FichaAtencion::findOrFail($hora->id_ficha_atencion) : new FichaAtencion();
            if ($ficha->exists) {
                abort_unless((int) $ficha->id_profesional === $idProfesional && (int) $ficha->id_mascota === (int) $hora->id_mascota && (int) $ficha->id_paciente === (int) $hora->id_paciente, 403);
            }
            foreach ([
                'motivo', 'antecedentes', 'examen_fisico', 'temperatura', 'pulso',
                'frecuencia_reposo', 'peso', 'talla', 'imc', 'estado_nutricional',
                'presion_bi', 'presion_bd', 'presion_de_pie', 'presion_sentado',
                'ct_estado_conciencia', 'ct_lenguaje', 'ct_traslado', 'indicaciones',
            ] as $campo) {
                if ($request->exists($campo)) {
                    $ficha->{$campo} = $request->input($campo) === '' ? null : $request->input($campo);
                }
            }
            $ficha->hipotesis_diagnostico = $request->descripcion_hipotesis;
            foreach (['temperatura', 'pulso'] as $campo) {
                if (!$request->filled($campo) && $request->filled($campo.'_examen_fisico')) {
                    $ficha->{$campo} = $request->input($campo.'_examen_fisico');
                }
            }
            $ficha->diagnostico_ce10 = $request->input('descripcion_cie', $ficha->diagnostico_ce10);
            $ficha->id_paciente = $hora->id_paciente;
            $ficha->id_profesional = $hora->id_profesional;
            $ficha->id_mascota = $hora->id_mascota;
            $ficha->id_lugar_atencion = $hora->id_lugar_atencion;
            if ($request->exists('enf_cronico') || !$ficha->exists) $ficha->cronico = $request->boolean('enf_cronico');
            if ($request->exists('confidencial') || !$ficha->exists) $ficha->confidencial = $request->boolean('confidencial');
            $ficha->ges = 0;
            if ((int) $request->input('cerrarsession') !== 2) {
                $ficha->finalizada = 1;
            } elseif (!$ficha->exists) {
                $ficha->finalizada = 0;
            }
            $ficha->saveOrFail();

            // Keep a complete clinical snapshot, including specialty fields without their own column.
            $detalle = FichaVeterinariaGeneral::where('id_fichas_atenciones', $ficha->id)->latest('id')->first()
                ?? new FichaVeterinariaGeneral();
            $anteriores = json_decode($detalle->datos ?? '{}', true) ?: [];
            $datos = array_replace($anteriores, $request->except(['_token', '_method', 'input_lista_imagenes']));
            $datos['especialidad_veterinaria'] = $especialidad;
            $datos['id_mascota'] = $hora->id_mascota;
            $datos['id_paciente_fc'] = $hora->id_paciente;
            $datos['id_profesional_fc'] = $hora->id_profesional;
            if ($especialidad === 'odontologia') {
                $datos['odontograma_veterinario'] = app(OdontologiaVeterinaria::class)->guardarEnFicha($ficha);
            }
            $datos['imagenes_atencion'] = $anteriores['imagenes_atencion'] ?? [];
            foreach ($imagenes as $imagen) {
                $nombre = 'mascota_'.$hora->id_mascota.'_'.$ficha->id.'_'.Str::uuid().'.'.$imagen['extension'];
                $ruta = 'imagenes/atenciones/'.$nombre;
                if (!Storage::disk('public')->copy($imagen['ruta'], $ruta)) {
                    throw new \RuntimeException('No se pudo guardar una imagen adjunta.');
                }
                app(Fotografias::class)->publicar($ruta);
                // Keep the upload available for retry if a later database operation fails.
                $datos['imagenes_atencion'][] = [
                    'url' => '/storage/'.$ruta, 'nombre_original' => $imagen['nombre'],
                    'nombre_archivo' => $nombre, 'extension' => $imagen['extension'],
                ];
            }
            $detalle->id_fichas_atenciones = $ficha->id;
            $detalle->id_paciente = $hora->id_paciente;
            $detalle->id_profesional = $hora->id_profesional;
            $detalle->datos = json_encode($datos, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
            $detalle->estado = 1;
            $detalle->saveOrFail();

            if ($especialidad === 'cirugia') {
                $this->guardarCirugia($request, $ficha);
            }
            $hora->id_ficha_atencion = $ficha->id;
            if ((int) $request->input('cerrarsession') !== 2) {
                $hora->id_estado = 6;
                $hora->fecha_realizacion_consulta = $hora->fecha_realizacion_consulta ?: now();
            }
            $hora->saveOrFail();

            return $ficha;
        });
    }

    private function validarImagenes(Request $request): array
    {
        $entrada = $request->input('input_lista_imagenes') ?: '[]';
        $lista = is_string($entrada) ? json_decode($entrada, true) : $entrada;
        $error = fn () => ValidationException::withMessages(['input_lista_imagenes' => 'Una imagen adjunta no es válida o ya no está disponible. Vuelve a adjuntarla.']);
        if (!is_array($lista) || !array_is_list($lista) || count($lista) > 50) throw $error();
        $resultado = [];
        foreach ($lista as $imagen) {
            if (!is_array($imagen) || !isset($imagen[1], $imagen[2]) || !is_string($imagen[1]) || !is_string($imagen[2]) || !preg_match('/^[a-zA-Z0-9_-]+\.(?:jpg|jpeg|png|gif|webp)$/i', $imagen[2])) throw $error();
            $ruta = 'imagenes/temp/'.$imagen[2];
            $disk = Storage::disk('public');
            if (!$disk->exists($ruta) || $disk->size($ruta) > 6 * 1024 * 1024) throw $error();
            $info = @getimagesize($disk->path($ruta));
            $extension = ['image/jpeg'=>'jpg', 'image/png'=>'png', 'image/gif'=>'gif', 'image/webp'=>'webp'][$info['mime'] ?? ''] ?? null;
            if (!$extension) throw $error();
            $resultado[] = ['ruta'=>$ruta, 'nombre'=>$imagen[1], 'extension'=>$extension];
        }
        return $resultado;
    }

    private function guardarCirugia(Request $request, FichaAtencion $ficha): void
    {
        $grupos = [
            [FichaCirugiaGeneralAdulto::class, ['est_grl', 'ex_seg', 'cir_grl_urgencia', 'obs_est_grl', 'rec_tto_cg', 'tipo_proc_cg', 'plan_proc_cg', 'otro', 'otro1'], ['tto_med_cg', 'pr_cg', 'plan_cirugia']],
            [ControlPostQuirurgico::class, ['eg_cpq_cg', 'hoc_cpa_cg', 'masas_cpq_cg', 'obs_egp_cpq_cg'], []],
        ];
        foreach ($grupos as [$modelo, $campos, $checks]) {
            if (!$request->hasAny(array_merge($campos, $checks))) {
                continue;
            }
            $registro = $modelo::where('id_ficha_atencion', $ficha->id)->latest('id')->first() ?? new $modelo();
            $registro->id_ficha_atencion = $ficha->id;
            $registro->id_profesional = $ficha->id_profesional;
            $registro->id_paciente = $ficha->id_paciente;
            foreach ($campos as $campo) {
                if ($request->exists($campo)) {
                    $registro->{$campo} = $request->input($campo);
                }
            }
            foreach ($checks as $campo) {
                if ($request->exists($campo) || !$registro->exists) {
                    $registro->{$campo} = $request->boolean($campo);
                }
            }
            $registro->estado = 1;
            $registro->saveOrFail();
        }
    }
}
