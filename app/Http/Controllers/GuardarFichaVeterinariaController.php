<?php

namespace App\Http\Controllers;

use App\Models\Profesional;
use App\Services\GuardarFichaVeterinaria;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class GuardarFichaVeterinariaController extends Controller
{
    public function guardar(Request $request, string $especialidad)
    {
        $profesional = Profesional::where('id_usuario', $request->user()->id)->firstOrFail();
        try {
            app(GuardarFichaVeterinaria::class)->guardar($request, $profesional->id, $especialidad);
        } catch (ValidationException $error) {
            if ($request->expectsJson()) {
                throw $error;
            }
            return back()->withErrors($error->errors())->withInput()->with('error', 'La ficha no fue guardada. Revise el diagnóstico.');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpExceptionInterface $error) {
            throw $error;
        } catch (\Throwable $error) {
            report($error);
            if ($request->expectsJson()) {
                return response()->json(['estado' => 'error', 'mensaje' => 'No se pudo guardar la ficha completa. Intente nuevamente.'], 500);
            }
            return back()->withInput()->with('error', 'No se pudo guardar la ficha completa. Intente nuevamente.');
        }

        if ($request->expectsJson()) {
            return response()->json(['estado' => 'ok', 'mensaje' => 'Ficha veterinaria guardada correctamente.']);
        }
        return redirect()->route('profesional.mi_agenda', ['lugares_atencion' => $request->id_lugar_atencion])
            ->with('success', 'Ficha veterinaria guardada correctamente.');
    }
}
