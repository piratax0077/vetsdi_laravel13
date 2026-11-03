<?php

namespace App\Services;

use App\Models\Instituciones;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB, Schema};

class EstadisticasLaboratorioVeterinario
{
    public function datos(Instituciones $institucion, Request $r): array
    {
        $desde = $r->input('desde') ?: now('America/Santiago')->startOfMonth()->toDateString();
        $hasta = $r->input('hasta') ?: now('America/Santiago')->toDateString();
        $r->merge(['desde'=>$desde, 'hasta'=>$hasta]);
        $r->validate(['hasta'=>'date|after_or_equal:desde']);
        $datos = ['desde'=>$desde, 'hasta'=>$hasta, 'disponible'=>false, 'estados'=>collect(), 'examenes'=>collect()];
        $columnas = Schema::hasTable('horas_medicas') ? Schema::getColumnListing('horas_medicas') : [];
        if (array_diff(['id_lugar_atencion','fecha_consulta','id_estado','tipo_hora_medica','alias_examen','id_procedimiento'], $columnas)) return $datos;

        $lugares = collect(preg_split('/[,;]/', (string) $institucion->id_lugar_atencion))->filter()->map(fn($id)=>(int)$id);
        if (Schema::hasTable('sucursal')) $lugares = $lugares->merge(DB::table('sucursal')->where('id_institucion',$institucion->id)->pluck('id_lugar_atencion'));
        $nombres = DB::table('vet_catalogo_servicios')->where('id_institucion',$institucion->id)->where('modulo','laboratorio')->pluck('nombre');
        $nombres = $nombres->flatMap(fn($nombre)=>[$nombre, trim(preg_replace('/^(Sangre|Orina|Secreciones) · /u','',$nombre))])->unique();
        $procedimientos = Schema::hasTable('procedimientos_centro')
            ? DB::table('procedimientos_centro')->whereIn('id_lugar_atencion',$lugares)->whereIn('nombre',$nombres)->pluck('id') : collect();
        // No contar imagenología ni otros procedimientos de la misma agenda de exámenes.
        $horas = DB::table('horas_medicas')->whereIn('id_lugar_atencion',$lugares->unique())
            ->where('tipo_hora_medica','E')->where('id_estado',6)
            ->whereDate('fecha_consulta','>=',$desde)->whereDate('fecha_consulta','<=',$hasta)
            ->where(function($q) use ($procedimientos, $nombres) {
                $q->where('alias_examen','like','%laboratorio%')->orWhereIn('alias_examen',$nombres);
                foreach ($procedimientos as $id) {
                    $q->orWhere('id_procedimiento',(string)$id)
                        ->orWhere('id_procedimiento','like',$id.'|%')
                        ->orWhere('id_procedimiento','like','%|'.$id.'|%')
                        ->orWhere('id_procedimiento','like','%|'.$id);
                }
            });
        $datos['disponible'] = true;
        $datos['estados'] = (clone $horas)->selectRaw('id_estado, COUNT(*) as total')->groupBy('id_estado')->pluck('total','id_estado');
        $datos['examenes'] = (clone $horas)->select('horas_medicas.*')
            ->selectSub(DB::table('mascotas')->select('nombre')->whereColumn('mascotas.id','horas_medicas.id_mascota')->limit(1),'mascota')
            ->orderByDesc('fecha_consulta')->orderByDesc('id')
            ->paginate(20, ['*'], 'pagina_examenes')->withQueryString();
        return $datos;
    }
}
