<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB, Schema};

class EscritorioVeterinario
{
    public static function url(Request $request, $institucion = null): string
    {
        $contexto = $request->query('contexto') ?: ($request->hasSession() ? $request->session()->get('admin_center_context') : null);
        $id = $institucion->id ?? $request->query('id_institucion');
        if (!$id && preg_match('/^admin:(\d+):/', (string)$contexto, $coincidencia)) $id = (int)$coincidencia[1];
        $hospital = $request->routeIs('adm_hospital.*');
        if (!$hospital && $id && Schema::hasTable('vet_establecimientos')) {
            $hospital = DB::table('vet_establecimientos')->where('id_institucion',$id)->value('tipo') === 'hospital';
        }
        return route($hospital ? 'adm_hospital.home' : 'adm_cm.home', array_filter([
            'id_institucion'=>$id, 'contexto'=>$contexto,
        ]));
    }
}
