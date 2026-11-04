<?php

namespace App\Services;

use App\Models\BoxCm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, DB};
use Illuminate\Validation\Rule;

class BoxesCentroVeterinario
{
    public function guardar(Request $request, bool $editar = false): array
    {
        $centro=app(PerfilCentroMedico::class)->centro($request);
        $datos=$request->validate([
            'id_box'=>$editar ? 'required|integer|min:1' : 'nullable|integer',
            'numero_box'=>'required|string|max:255', 'tpo_especializacion'=>'required|string|max:255|not_in:0,Otro',
            'tpo_equip_servicio'=>'required|integer|min:1|max:10', 'id_lugar_atencion'=>'nullable|integer|min:1',
        ]);
        return DB::transaction(function () use ($centro,$datos,$editar) {
            DB::table($centro->getTable())->where('id',$centro->id)->lockForUpdate()->first();
            $box=$editar ? BoxCm::where('id_institucion',$centro->id)->findOrFail($datos['id_box']) : new BoxCm();
            $lugar=$datos['id_lugar_atencion'] ?? ($box->id_lugar_atencion ?: $centro->id_lugar_atencion);
            $pertenece=(int)$lugar === (int)$centro->id_lugar_atencion || DB::table('sucursal')->where('id_institucion',$centro->id)->where('id_lugar_atencion',$lugar)->exists();
            abort_unless($lugar && $pertenece,403,'La ubicación no pertenece al centro.');
            validator(['numero_box'=>trim($datos['numero_box'])], ['numero_box'=>['required',Rule::unique('boxes_cm','numero_box')->where('id_lugar_atencion',$lugar)->ignore($box->id)]])->validate();
            $box->id_institucion=$centro->id;
            $box->id_lugar_atencion=$lugar;
            $box->numero_box=trim($datos['numero_box']);
            $box->tipo_box=$datos['tpo_especializacion'];
            $box->tipo_especializacion=$datos['tpo_especializacion'];
            $box->ubicacion=$datos['tpo_equip_servicio'];
            if (!$box->exists) { $box->estado=1; $box->seccion=2; }
            $box->id_usuario=Auth::id();
            $box->saveOrFail();
            return ['estado'=>1,'mensaje'=>$editar ? 'Box o sala actualizado correctamente.' : 'Box o sala agregado correctamente.',
                'v'=>view('fragm.boxes_cm',['boxes_servicio'=>BoxCm::where('id_institucion',$centro->id)->orderBy('numero_box')->get()])->render()];
        });
    }

    public function obtener(Request $request, int $id): array
    {
        $centro=app(PerfilCentroMedico::class)->centro($request);
        return ['estado'=>1,'box'=>BoxCm::where('id_institucion',$centro->id)->findOrFail($id)];
    }
}
