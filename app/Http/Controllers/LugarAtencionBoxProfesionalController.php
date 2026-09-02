<?php

namespace App\Http\Controllers;

use App\Models\Instituciones;
use App\Models\LugarAtencionBoxProfesional;
use Illuminate\Http\Request;

class LugarAtencionBoxProfesionalController extends Controller
{
    public function verRegistro(Request $request)
    {
        $datos = [];
        $registro = LugarAtencionBoxProfesional::with(['institucion', 'lugarAtencion', 'box', 'profesional'])
            ->find($request->id);

        if ($registro) {
            $datos['estado'] = 1;
            $datos['msj'] = 'Registro encontrado';
            $datos['registros'] = $registro;
        } else {
            $datos['estado'] = 0;
            $datos['msj'] = 'Registro no encontrado';
        }

        return response()->json($datos);
    }

    public function verRegistros(Request $request)
    {
        $datos = [];
        $query = LugarAtencionBoxProfesional::with(['institucion', 'lugarAtencion', 'box', 'profesional']);

        if(!empty($request->id_institucion))
            $query->where('id_institucion', $request->id_institucion);
        if(!empty($request->id_lugar_atencion))
            $query->where('id_lugar_atencion', $request->id_lugar_atencion);
        if(!empty($request->id_box))
            $query->where('id_box', $request->id_box);
        if(!empty($request->id_profesional))
            $query->where('id_profesional', $request->id_profesional);
        if(!empty($request->estado))
            $query->where('estado', $request->estado);

        $registros = $query->get();

        if ($registros->count() > 0) {
            $datos['estado'] = 1;
            $datos['msj'] = 'Registros encontrados';
            $datos['registros'] = $registros;
        } else {
            $datos['estado'] = 0;
            $datos['msj'] = 'No hay registros disponibles';
        }

        return response()->json($datos);
    }

    public function agregar(Request $request)
    {
        $datos = [];
        $error = [];
        $valido = true;

        if (empty($request->id_lugar_atencion)) {
            $error['id_lugar_atencion'] = 'Campo requerido';
            $valido = false;
        }

        if (empty($request->id_box)) {
            $error['id_box'] = 'Campo requerido';
            $valido = false;
        }

        if (empty($request->id_profesional)) {
            $error['id_profesional'] = 'Campo requerido';
            $valido = false;
        }

        if ($valido) {
            try {
                $registro = new LugarAtencionBoxProfesional();
                if (empty($request->id_institucion))
                {
                    $institucion = Instituciones::where('id_lugar_atencion', $request->id_lugar_atencion)->where('estado', 1)->first();
                    if($institucion)
                    {
                        $registro->id_institucion = $institucion->id;
                    }
                }
                else
                {
                    $registro->id_institucion = $request->id_institucion;
                }

                $registro->id_lugar_atencion = $request->id_lugar_atencion;
                $registro->id_box = $request->id_box;
                $registro->id_profesional = $request->id_profesional;
                $registro->estado = $request->estado ?? 1;

                if ($registro->save()) {
                    $datos['estado'] = 1;
                    $datos['msj'] = 'Registro exitoso';
                    $datos['registros'] = $registro->load(['institucion', 'lugarAtencion', 'box', 'profesional']);
                } else {
                    $datos['estado'] = 0;
                    $datos['msj'] = 'Error al guardar';
                }
            } catch (\Exception $e) {
                $datos['estado'] = 0;
                $datos['msj'] = 'Error en el servidor';
                $datos['error'] = $e->getMessage();
            }
        } else {
            $datos['estado'] = 0;
            $datos['msj'] = 'Campos requeridos';
            $datos['error'] = $error;
        }

        return response()->json($datos);
    }

    public function modificar(Request $request)
    {
        $datos = [];
        $registro = LugarAtencionBoxProfesional::find($request->id);

        if ($registro) {
            try {
                $registro->id_institucion = $request->id_institucion ?? $registro->id_institucion;
                $registro->id_lugar_atencion = $request->id_lugar_atencion ?? $registro->id_lugar_atencion;
                $registro->id_box = $request->id_box ?? $registro->id_box;
                $registro->id_profesional = $request->id_profesional ?? $registro->id_profesional;

                if ($registro->save()) {
                    $datos['estado'] = 1;
                    $datos['msj'] = 'Actualización exitosa';
                    $datos['registros'] = $registro->load(['institucion', 'lugarAtencion', 'box', 'profesional']);
                } else {
                    $datos['estado'] = 0;
                    $datos['msj'] = 'Error al actualizar';
                }
            } catch (\Exception $e) {
                $datos['estado'] = 0;
                $datos['msj'] = 'Error en el servidor';
                $datos['error'] = $e->getMessage();
            }
        } else {
            $datos['estado'] = 0;
            $datos['msj'] = 'Registro no encontrado';
        }

        return response()->json($datos);
    }

    // Los métodos estado() y eliminar() permanecen igual que en la versión anterior
    public function estado(Request $request)
    {
        $datos = [];
        $registro = LugarAtencionBoxProfesional::find($request->id);

        if ($registro) {
            $registro->estado = $registro->estado == 1 ? 0 : 1;

            if ($registro->save()) {
                $datos['estado'] = 1;
                $datos['msj'] = 'Estado actualizado';
                $datos['nuevo_estado'] = $registro->estado;
            } else {
                $datos['estado'] = 0;
                $datos['msj'] = 'Error al actualizar estado';
            }
        } else {
            $datos['estado'] = 0;
            $datos['msj'] = 'Registro no encontrado';
        }

        return response()->json($datos);
    }

    public function eliminar(Request $request)
    {
        $datos = [];
        $registro = LugarAtencionBoxProfesional::find($request->id);

        if ($registro) {
            if ($registro->delete()) {
                $datos['estado'] = 1;
                $datos['msj'] = 'Registro eliminado correctamente';
            } else {
                $datos['estado'] = 0;
                $datos['msj'] = 'Error al intentar eliminar el registro';
            }
        } else {
            $datos['estado'] = 0;
            $datos['msj'] = 'Registro no encontrado';
        }

        return response()->json($datos);
    }
}
