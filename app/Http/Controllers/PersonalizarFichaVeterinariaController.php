<?php

namespace App\Http\Controllers;

use App\Models\Profesional;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PersonalizarFichaVeterinariaController extends Controller
{
    public function editar(Request $request)
    {
        $profesional = Profesional::where('id_usuario', $request->user()->id)->firstOrFail();

        return view('app.profesional.personalizar_ficha_veterinaria', [
            'secciones' => config('ficha_veterinaria_secciones'),
            'visibles' => $profesional->seccionesFichaVeterinaria(),
        ]);
    }

    public function guardar(Request $request)
    {
        $profesional = Profesional::where('id_usuario', $request->user()->id)->firstOrFail();
        $data = $request->validate([
            'secciones' => ['sometimes', 'array', 'list'],
            'secciones.*' => ['required', 'string', 'distinct', Rule::in(array_keys(config('ficha_veterinaria_secciones')))],
        ]);

        $profesional->secciones_ficha_veterinaria = json_encode($data['secciones'] ?? [], JSON_THROW_ON_ERROR);
        $profesional->saveOrFail();

        return back()->with('ficha_personalizada', 'Configuración guardada.');
    }
}
