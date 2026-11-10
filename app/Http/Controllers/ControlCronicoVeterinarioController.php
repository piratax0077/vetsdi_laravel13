<?php

namespace App\Http\Controllers;

use App\Models\{ControlObesidad, FichaAtencion};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\{Rule, ValidationException};

class ControlCronicoVeterinarioController extends Controller
{
    private function datos(Request $request): array
    {
        return $request->validate([
            'condicion' => ['required', Rule::in(array_keys(config('controles_cronicos_vet')))],
            'otra' => 'exclude_unless:condicion,otra|required|string|max:120',
        ]);
    }

    public function historial(Request $request): array
    {
        $datos = $this->datos($request);
        $ficha = FichaAtencion::findOrFail($request->id_ficha_atencion);
        $registros = DB::table('vet_controles_cronicos')->where('id_mascota', $ficha->id_mascota)
            ->where('condicion', $datos['condicion'])
            ->when(isset($datos['otra']), fn ($query) => $query->whereRaw('LOWER(otra) = ?', [mb_strtolower($datos['otra'] ?? '')]))
            ->get(['created_at', 'mediciones', 'observaciones'])
            ->map(fn ($fila) => ['fecha' => (string) $fila->created_at, 'mediciones' => json_decode($fila->mediciones, true),
                'observaciones' => $fila->observaciones]);

        $legado = match ($datos['condicion']) {
            'cpeso' => [ControlObesidad::class, ['peso', 'variacion', 'ideal']],
            'chipertension' => [\App\Models\Hipertension::class, ['sistolica', 'diastolica']],
            // Los controles anteriores de diabetes tienen parámetros heredados de medicina humana.
            'cdiabet' => [\App\Models\Diabete::class, ['peso']],
            default => null,
        };
        if ($legado) {
            [$modelo, $campos] = $legado;
            $historicos = $modelo::whereIn('id_ficha_atencion', FichaAtencion::where('id_mascota', $ficha->id_mascota)->select('id'))
                ->get()->map(fn ($fila) => ['fecha' => (string) $fila->created_at,
                    'mediciones' => collect($fila->only($campos))->filter(fn ($valor) => is_numeric($valor))->all(),
                    'observaciones' => null])->filter(fn ($registro) => $registro['mediciones']);
            $registros = $registros->concat($historicos);
        }

        return ['registros' => $registros->sortBy('fecha')->values()];
    }

    public function medicamentos(Request $request): array
    {
        $datos = $request->validate([
            'tipo_enfermedad' => ['required', Rule::in(array_keys(config('controles_cronicos_vet')))],
        ]);
        $ficha = FichaAtencion::findOrFail($request->id_ficha_atencion);
        $fichas = FichaAtencion::where('id_mascota', $ficha->id_mascota)->select('id');
        $registros = \App\Models\MedicamentoUsoCronicoGeneral::whereIn('id_ficha_atencion', $fichas)
            ->where('tipo_enfermedad', $datos['tipo_enfermedad'])->orderBy('id')->get();

        return ['estado' => 1, 'registros' => $registros];
    }

    public function guardar(Request $request): array
    {
        $datos = $this->datos($request);
        $campos = config('controles_cronicos_vet')[$datos['condicion']][1];
        $reglas = ['observaciones' => 'nullable|string|max:2000', 'mediciones' => 'nullable|array'];
        foreach ($campos as $campo => $definicion) {
            $reglas['mediciones.'.$campo] = ['nullable', isset($definicion[2]) ? 'integer' : 'numeric',
                $campo === 'variacion' ? 'min:-1000000' : 'min:0', 'max:1000000'];
        }
        $validado = $request->validate($reglas);
        $mediciones = collect($validado['mediciones'] ?? [])->only(array_keys($campos))
            ->filter(fn ($valor) => $valor !== null && $valor !== '')->map(fn ($valor) => (float) $valor)->all();
        if (!$mediciones && empty($validado['observaciones'])) {
            throw ValidationException::withMessages(['observaciones' => 'Registra una medición o una observación para guardar el control.']);
        }

        $ficha = FichaAtencion::findOrFail($request->id_ficha_atencion);
        DB::table('vet_controles_cronicos')->insert([
            'id_mascota' => $ficha->id_mascota, 'id_ficha_atencion' => $ficha->id,
            'condicion' => $datos['condicion'], 'otra' => $datos['otra'] ?? null,
            'mediciones' => json_encode($mediciones), 'observaciones' => $validado['observaciones'] ?? null,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        return ['estado' => 1, 'mensaje' => 'Control guardado correctamente.'];
    }
}
