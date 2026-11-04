<?php

namespace App\Services;

use App\Models\Profesional;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB, Schema};
use Illuminate\Validation\Rule;

class ResponsablesAreasCentro
{
    public const AREAS = [
        'consultas'=>'Consultas o medicina general', 'cirugia'=>'Cirugía y pabellón',
        'hospitalizacion'=>'Hospitalización', 'laboratorio'=>'Laboratorio', 'imagenologia'=>'Imagenología',
        'especialidades'=>'Especialidades', 'vacunacion'=>'Vacunación y prevención',
        'farmacia'=>'Farmacia o expendio', 'tienda'=>'Tienda y ventas', 'peluqueria'=>'Peluquería',
        'recepcion'=>'Recepción y atención al cliente', 'administracion'=>'Administración y finanzas',
        'inventario'=>'Inventario y bodega', 'bioseguridad'=>'Bioseguridad, aseo y residuos',
        'recursos_humanos'=>'Recursos humanos o personal',
    ];

    public function listar(int $institucion)
    {
        if (!Schema::hasTable('centro_responsables_areas')) return collect();
        return DB::table('centro_responsables_areas as r')->join('profesionales as p','p.id','=','r.id_profesional')
            ->where('r.id_institucion',$institucion)->orderBy('r.area')
            ->get(['r.area','r.id_profesional','p.nombre','p.apellido_uno','p.apellido_dos','p.rut','p.email'])
            ->each(function ($fila) { $fila->area_nombre = self::AREAS[$fila->area] ?? $fila->area; });
    }

    public function guardar(Request $request): array
    {
        $datos = $request->validate(['cargo'=>['required',Rule::in(array_keys(self::AREAS))], 'responsable'=>'required|integer|min:1']);
        $centro = app(PerfilCentroMedico::class)->centro($request);
        $lugares = DB::table('sucursal')->where('id_institucion',$centro->id)->pluck('id_lugar_atencion')->push($centro->id_lugar_atencion)->filter()->unique();
        $profesional = Profesional::whereKey($datos['responsable'])->whereHas('LugaresAtencion', fn ($q) => $q->whereIn('lugares_atencion.id',$lugares))->first();
        if (!$profesional) throw \Illuminate\Validation\ValidationException::withMessages(['responsable'=>'Seleccione un profesional vinculado a este centro.']);
        DB::table('centro_responsables_areas')->upsert([
            ['id_institucion'=>$centro->id,'area'=>$datos['cargo'],'id_profesional'=>$profesional->id,'created_at'=>now(),'updated_at'=>now()],
        ], ['id_institucion','area'], ['id_profesional','updated_at']);
        return ['estado'=>1,'msj'=>'Responsable del área guardado correctamente.',
            'areas_html'=>view('fragm.responsables_areas_centro',['responsablesAreasCentro'=>$this->listar($centro->id)])->render()];
    }
}
