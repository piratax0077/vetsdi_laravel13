<?php

namespace App\Http\Controllers;

use App\Models\ProfesionalMiEquipo;
use App\Models\ProfesionalMiEquipoProfesional;
use App\Models\Profesional;
use Illuminate\Http\Request;

class ProfesionalMiEquipoController extends Controller
{
    private function propio(): Profesional
    {
        return Profesional::where('id_usuario', \Illuminate\Support\Facades\Auth::id())->firstOrFail();
    }

    private function equipoPropio($id): ProfesionalMiEquipo
    {
        return ProfesionalMiEquipo::where('id_profesional',$this->propio()->id)->findOrFail($id);
    }

    public function verEquiposProfesional(Request $request)
    {
        $profesional=$this->propio();
        abort_if($request->filled('id_profesional') && (int)$request->id_profesional !== (int)$profesional->id,403);
        $equipos=ProfesionalMiEquipo::where('id_profesional',$profesional->id)->get();
        return ['estado'=>1,'msj'=>'registros','registros'=>$equipos->where('estado',1)->values(),'registros_'=>$equipos];
    }

    public function verDetalleEquipoProfesional(Request $request)
    {
        $request->validate(['id_profesional_mi_equipo'=>'required|integer']);
        $equipo=$this->equipoPropio($request->id_profesional_mi_equipo);
        $miembros=ProfesionalMiEquipoProfesional::where('id_profesional_mi_equipo',$equipo->id)->where('estado',1)
            ->with(['Profesional','TipoEspecialidad','SubTipoEspecialidad'])->orderBy('id')->get();
        return ['estado'=>1,'msj'=>'registros','equipo'=>['id'=>$equipo->id,'nombre'=>$equipo->nombre,'descripcion'=>$equipo->descripcion], 'registros'=>$miembros];
    }

    private function integrantes(Request $request): array
    {
        $datos=$request->validate(['profesionales'=>'required|array|min:1|max:100','profesionales.*.profesional_id'=>'required|integer|exists:profesionales,id',
            'profesionales.*.posicion'=>'required|string|max:100']);
        return $datos['profesionales'];
    }

    private function guardarIntegrantes(int $equipo,array $integrantes): void
    {
        foreach($integrantes as $integrante){
            $p=Profesional::findOrFail($integrante['profesional_id']);
            $resultado=static::registroEquipoProfesional($equipo,$p->id_tipo_especialidad,$p->id_sub_tipo_especialidad,$integrante['posicion'],$p->id);
            if($resultado->estado!==1)throw new \RuntimeException('No se pudo guardar un integrante del equipo.');
        }
    }

    public function registroMiEquipoProfesionales(Request $request)
    {
        $profesional=$this->propio();
        abort_if($request->filled('id_profesional') && (int)$request->id_profesional !== (int)$profesional->id,403);
        $datos=$request->validate(['nombre'=>'required|string|max:255','descripcion'=>'required|string|max:2000']);
        $integrantes=$this->integrantes($request);
        return \Illuminate\Support\Facades\DB::transaction(function()use($datos,$profesional,$integrantes){
            $resultado=static::registrarEquipo($datos['nombre'],$datos['descripcion'],$profesional->id);
            if($resultado->estado!==1)throw new \RuntimeException('No se pudo guardar el equipo.');
            $this->guardarIntegrantes($resultado->last_id,$integrantes);
            return ['estado'=>1,'msj-equipo'=>'Equipo creado','result-equipo'=>$resultado,'cant-prof'=>count($integrantes),'cant-prof-reg'=>count($integrantes),'cant-prof-error'=>0];
        });
    }

    public function modificarMiEquipoProfesionales(Request $request)
    {
        $request->validate(['id_equipo_modificar'=>'required|integer']);
        $equipo=$this->equipoPropio($request->id_equipo_modificar);
        // Validar toda la lista antes de reemplazar los integrantes existentes.
        $integrantes=$this->integrantes($request);
        return \Illuminate\Support\Facades\DB::transaction(function()use($equipo,$integrantes){
            ProfesionalMiEquipo::whereKey($equipo->id)->lockForUpdate()->firstOrFail();
            ProfesionalMiEquipoProfesional::where('id_profesional_mi_equipo',$equipo->id)->delete();
            $this->guardarIntegrantes($equipo->id,$integrantes);
            return ['estado'=>1,'msj-equipo'=>'Equipo actualizado','cant-prof'=>count($integrantes),'cant-prof-reg'=>count($integrantes),'cant-prof-error'=>0];
        });
    }

    public function eliminarMiEquipoProfesionales(Request $request)
    {
        $equipo=$this->equipoPropio($request->id);
        return \Illuminate\Support\Facades\DB::transaction(function()use($equipo){
            ProfesionalMiEquipoProfesional::where('id_profesional_mi_equipo',$equipo->id)->delete();$equipo->delete();return ['estado'=>1];
        });
    }

    public function modificarEstadoMiEquipoProfesionales(Request $request)
    {
        $request->validate(['habilitado'=>'required|boolean']);$equipo=$this->equipoPropio($request->id);
        $equipo->estado=$request->habilitado;$equipo->saveOrFail();return ['estado'=>1];
    }

    static public function registrarEquipo( $nombre, $descripcion, $id_profesional )
    {
        $datos = array();
        $error = array();
        $valido = 1;

        if(empty($nombre))
        {
            $error['nombre'] = 'campo requerido';
            $valido = 0;
        }
        if(empty($descripcion))
        {
            $error['descripcion'] = 'campo requerido';
            $valido = 0;
        }
        if(empty($id_profesional))
        {
            $error['id_profesional'] = 'campo requerido';
            $valido = 0;
        }

        if($valido)
        {
            $registro = new ProfesionalMiEquipo();
            $registro->nombre = $nombre;
            $registro->descripcion = $descripcion;
            $registro->id_profesional = $id_profesional;
            $registro->estado = 1;

            if($registro->save())
            {
                $datos['estado'] = 1;
                $datos['msj'] = 'registro';
                $datos['last_id'] = $registro->id;
            }
            else
            {
                $datos['estado'] = 0;
                $datos['msj'] = 'problema en registro';
            }
        }
        else
        {
            $datos['estado'] = 0;
            $datos['msj'] ='campo requerido';
            $datos['error'] = $error;
        }

        return (object)$datos;
    }

    static public function registroEquipoProfesional( $id_profesional_mi_equipo, $id_tipo_especialidad, $id_sub_tipo_especialidad, $posicion, $id_profesional )
    {
        $datos = array();
        $error = array();
        $valido = 1;

        if(empty($id_profesional_mi_equipo))
        {
            $error['id_profesional_mi_equipo'] = 'campor requerido';
            $valido = 0;
        }


        if(empty($posicion))
        {
            $error['posicion'] = 'campor requerido';
            $valido = 0;
        }
        if(empty($id_profesional))
        {
            $error['id_profesional'] = 'campor requerido';
            $valido = 0;
        }
        // if(empty($estado))
        // {
        //     $error['estado'] = 'campor requerido';
        //     $valido = 0;
        // }

        if($valido)
        {
            $registro = new ProfesionalMiEquipoProfesional();
            $registro->id_profesional_mi_equipo = $id_profesional_mi_equipo;
            $registro->id_tipo_especialidad = $id_tipo_especialidad;
            $registro->id_sub_tipo_especialidad = $id_sub_tipo_especialidad;
            $registro->posicion = $posicion;
            $registro->id_profesional = $id_profesional;
            $registro->estado = 1;

            if($registro->save())
            {
                $datos['estado'] = 1;
                $datos['msj'] = 'registro';
                $datos['last_id'] = $registro->id;
            }
            else
            {
                $datos['estado'] = 0;
                $datos['msj'] = 'problema en registro';
            }
        }
        else
        {
            $datos['estado'] = 0;
            $datos['msj'] ='campo requerido';
            $datos['error'] = $error;
        }

        return (object)$datos;
    }

}
