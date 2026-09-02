<?php

namespace App\Http\Controllers;

use App\Models\PromocionRedSocial;
use App\Models\SitioWeb;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PromocionRedSocialController extends Controller
{
    public static function planes(): array
    {
        return [
            'impulso' => [
                'nombre' => 'Impulso local', 'dias' => 7, 'monto' => 14990,
                'redes' => ['instagram', 'facebook'],
                'ideal' => 'Dar visibilidad rápida a consultas, controles y campañas locales.',
            ],
            'crecimiento' => [
                'nombre' => 'Crecimiento', 'dias' => 15, 'monto' => 29990,
                'redes' => ['instagram', 'facebook', 'tiktok'],
                'ideal' => 'Aumentar alcance, visitas al perfil y solicitudes de reserva.',
            ],
            'presencia' => [
                'nombre' => 'Presencia profesional', 'dias' => 30, 'monto' => 49990,
                'redes' => ['instagram', 'facebook', 'tiktok', 'linkedin'],
                'ideal' => 'Mantener presencia continua y posicionar servicios profesionales.',
            ],
        ];
    }

    public function index()
    {
        $sitio = SitioWeb::where('id_usuario', Auth::id())->firstOrFail();
        $campanas = PromocionRedSocial::where('id_usuario', Auth::id())->latest()->get();

        return view('sitio.promocion-redes', compact('sitio', 'campanas') + ['planes' => static::planes()]);
    }

    public function contratar(Request $request)
    {
        $planes = static::planes();
        $datos = $request->validate([
            'plan' => 'required|in:'.implode(',', array_keys($planes)),
            'objetivo' => 'required|in:reservas,marca,servicio,educacion',
            'redes' => 'required|array|min:1',
            'redes.*' => 'in:instagram,facebook,tiktok,linkedin',
        ]);
        $plan = $planes[$datos['plan']];
        $sitio = SitioWeb::where('id_usuario', Auth::id())->firstOrFail();
        $redes = collect($datos['redes'])->intersect($plan['redes'])->unique()->values()->all();

        abort_if($redes === [], 422, 'Selecciona al menos una red incluida en el plan.');

        $campana = PromocionRedSocial::create([
            'id_usuario' => Auth::id(),
            'id_sitio_web' => $sitio->id,
            'plan' => $datos['plan'],
            'nombre_plan' => $plan['nombre'],
            'redes' => $redes,
            'duracion_dias' => $plan['dias'],
            'monto' => $plan['monto'],
            'objetivo' => $datos['objetivo'],
            'estado' => 'pendiente_pago',
        ]);

        return redirect()->route('sitio.promocion.pago', $campana);
    }

    public function pago(PromocionRedSocial $campana)
    {
        $this->autorizar($campana);

        return view('sitio.promocion-pago', compact('campana'));
    }

    public function informarPago(Request $request, PromocionRedSocial $campana)
    {
        $this->autorizar($campana);
        $datos = $request->validate([
            'referencia_pago' => 'required|string|max:100',
            'comprobante' => 'required|file|mimes:pdf,jpg,jpeg,png|max:4096',
        ]);

        $campana->update([
            'metodo_pago' => 'transferencia',
            'referencia_pago' => $datos['referencia_pago'],
            'comprobante' => $request->file('comprobante')->store('promociones/comprobantes', 'public'),
            'estado' => 'pago_en_revision',
        ]);

        return redirect()->route('sitio.promocion.index')->with('mensaje', 'Comprobante recibido. La campaña quedó en revisión de pago.');
    }

    public function pagoSimulado(Request $request, PromocionRedSocial $campana)
    {
        $this->autorizar($campana);
        $request->validate([
            'acepta_simulacion' => 'accepted',
        ]);

        $inicio = now()->startOfDay();
        $campana->update([
            'metodo_pago' => 'simulacion',
            'referencia_pago' => 'SIM-'.now()->format('YmdHis').'-'.$campana->id,
            'estado' => 'activa_simulada',
            'fecha_inicio' => $inicio->toDateString(),
            'fecha_termino' => $inicio->copy()->addDays($campana->duracion_dias - 1)->toDateString(),
        ]);

        return redirect()->route('sitio.promocion.index')
            ->with('mensaje', 'Pago simulado aprobado. La campaña de prueba quedó activa; no se realizó ningún cobro real.');
    }

    private function autorizar(PromocionRedSocial $campana): void
    {
        abort_unless((int) $campana->id_usuario === (int) Auth::id(), 403);
    }
}
