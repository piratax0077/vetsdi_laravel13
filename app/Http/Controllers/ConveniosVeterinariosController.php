<?php

namespace App\Http\Controllers;

use App\Models\{ConvenioVeterinario, Profesional};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ConveniosVeterinariosController extends Controller
{
    private function profesional(): Profesional
    {
        abort_unless(Auth::check() && Auth::user()->hasRole('Profesional'), 403);
        return Profesional::where('id_usuario', Auth::id())->firstOrFail();
    }

    public function index()
    {
        $profesional = $this->profesional();
        $lugares = $profesional->LugaresAtencion()->get();
        $convenios = ConvenioVeterinario::where('id_profesional', $profesional->id)->orderBy('nombre')->get();
        return view('app.profesional.convenios_veterinarios', compact('profesional', 'lugares', 'convenios'));
    }

    public function disponibles(Request $request)
    {
        $request->validate(['id_hora_medica' => ['required', 'integer']]);
        $hora = \App\Models\HoraMedica::findOrFail($request->id_hora_medica);
        app(\App\Services\RecepcionPagoVeterinario::class)->autorizar($hora);
        $convenios = ConvenioVeterinario::where('id_profesional', $hora->id_profesional)->where('activo', true)
            ->where(fn ($q) => $q->whereNull('id_lugar_atencion')->orWhere('id_lugar_atencion', $hora->id_lugar_atencion))
            ->orderBy('nombre')->get(['id', 'nombre', 'porcentaje', 'id_lugar_atencion']);
        $bono = \App\Models\Bono::where('id_referencia', $hora->id)->where('id_profesional', $hora->id_profesional)->where('devuelto', 0)->orderBy('id')->first();
        return response()->json(['convenios' => $convenios, 'seleccionado' => $bono?->id_convenio_veterinario,
            'aplicado' => $bono?->id_convenio_veterinario ? ['id' => $bono->id_convenio_veterinario, 'nombre' => $bono->nombre_convenio_veterinario, 'porcentaje' => $bono->porcentaje_convenio_veterinario] : null]);
    }

    public function guardar(Request $request)
    {
        $profesional = $this->profesional();
        $datos = $request->validate([
            'id' => ['nullable', 'integer'],
            'nombre' => ['required', 'string', 'max:150'],
            'porcentaje' => ['required', 'numeric', 'between:0,100'],
            'id_lugar_atencion' => ['nullable', 'integer', Rule::in($profesional->LugaresAtencion()->pluck('lugares_atencion.id')->all())],
        ]);
        $convenio = empty($datos['id']) ? new ConvenioVeterinario() : ConvenioVeterinario::where('id_profesional', $profesional->id)->findOrFail($datos['id']);
        unset($datos['id']);
        $convenio->fill($datos);
        $convenio->id_profesional = $profesional->id;
        $convenio->activo = true;
        $convenio->saveOrFail();
        return back()->with('mensaje', 'Convenio guardado. El descuento estará disponible al recibir el pago de la atención.');
    }

    public function desactivar(int $id)
    {
        $profesional = $this->profesional();
        $convenio = ConvenioVeterinario::where('id_profesional', $profesional->id)->findOrFail($id);
        $convenio->activo = false;
        $convenio->saveOrFail();
        return back()->with('mensaje', 'Convenio desactivado. Los descuentos históricos se conservan.');
    }
}
