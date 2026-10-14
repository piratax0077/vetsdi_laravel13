<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB, Validator};
use Illuminate\Validation\ValidationException;

class ColeccionClinica
{
    public function reemplazar(Request $request, string $campo, array $reglas, array $predeterminados, callable $guardar): array
    {
        $lista = $request->input($campo);
        if (is_string($lista)) $lista = json_decode($lista, true);
        if (!is_array($lista) || !array_is_list($lista)) {
            throw ValidationException::withMessages([$campo=>'Envíe una lista válida. No se modificaron los registros existentes.']);
        }
        $lista = array_map(fn ($item) => is_array($item) ? array_replace($predeterminados, $item) : $item, $lista);
        $validaciones = [$campo=>'present|array|max:200', $campo.'.*'=>'array'];
        foreach ($reglas as $nombre=>$regla) $validaciones[$campo.'.*.'.$nombre] = $regla;
        Validator::make([$campo=>$lista], $validaciones)->validate();
        $request->merge([$campo=>json_encode($lista, JSON_THROW_ON_ERROR)]);
        return DB::transaction(function () use ($guardar, $campo) {
            $resultado = $guardar();
            if (!empty($resultado['falla']) || (!empty($resultado['registros']) && collect($resultado['registros'])->contains(fn ($r)=>empty($r['estado'])))) {
                throw ValidationException::withMessages([$campo=>'No se pudo guardar la lista completa. Los registros anteriores se conservan.']);
            }
            return $resultado;
        });
    }
}
