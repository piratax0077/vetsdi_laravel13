<?php

namespace App\Http\Controllers;

use App\Models\Televisor;
use Illuminate\Http\Request;

class TelevisorController extends Controller
{
    public function verRegistro(Request $request)
    {
        $datos = [];
        $registro = Televisor::with('salaEspera')->find($request->id);

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
        $filtros = [];

        if(!empty($request->id_institucion))
            $filtros[] = array('id_institucion',$request->id_institucion);
        if(!empty($request->id_lugar_atencion))
            $filtros[] = array('id_lugar_atencion',$request->id_lugar_atencion);
        if(!empty($request->id_sala_espera))
            $filtros[] = array('id_sala_espera',$request->id_sala_espera);
        if(!empty($request->alias))
            $filtros[] = array('alias',$request->alias);
        if(!empty($request->token))
            $filtros[] = array('token',$request->token);
        if(!empty($request->estado))
            $filtros[] = array('estado',$request->estado);
        if(!empty($request->cantidad))
            $filtros[] = array('cantidad',$request->cantidad);

        $registros = Televisor::with('salaEspera')->where($filtros)->get();

        if ($registros->count() > 0)
        {
            $datos['estado'] = 1;
            $datos['msj'] = 'Registros encontrados';
            $datos['registros'] = $registros;
        }
        else
        {
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

        if (empty($request->nombre)) {
            $error['nombre'] = 'Campo requerido';
            $valido = false;
        }

        if (empty($request->id_lugar_atencion)) {
            $error['id_lugar_atencion'] = 'Campo requerido';
            $valido = false;
        }

        if (empty($request->id_sala_espera)) {
            $error['id_sala_espera'] = 'Campo requerido';
            $valido = false;
        }

        if ($valido) {
            try {
                $registro = new Televisor();
                $registro->id_institucion = $request->id_institucion;
                $registro->id_lugar_atencion = $request->id_lugar_atencion;
                $registro->id_sala_espera = $request->id_sala_espera;
                $registro->alias = $request->alias;
                $registro->token = $request->token ?? uniqid();
                $registro->nombre = $request->nombre;
                $registro->descripcion = $request->descripcion;
                $registro->cantidad = $request->cantidad ?? 6;
                $registro->estado = $request->estado ?? 1;

                if ($registro->save()) {
                    $datos['estado'] = 1;
                    $datos['msj'] = 'Registro exitoso';
                    $datos['registros'] = $registro;
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
            $datos['msj'] = 'Validación fallida';
            $datos['error'] = $error;
        }

        return response()->json($datos);
    }

    public function modificar(Request $request)
    {
        $datos = [];
        $registro = Televisor::find($request->id);

        if ($registro) {
            try {
                $registro->id_institucion = $request->id_institucion ?? $registro->id_institucion;
                $registro->id_lugar_atencion = $request->id_lugar_atencion ?? $registro->id_lugar_atencion;
                $registro->id_sala_espera = $request->id_sala_espera ?? $registro->id_sala_espera;
                $registro->alias = $request->alias ?? $registro->alias;
                $registro->nombre = $request->nombre ?? $registro->nombre;
                $registro->descripcion = $request->descripcion ?? $registro->descripcion;
                $registro->cantidad = $request->cantidad ?? $registro->cantidad;

                if ($registro->save()) {
                    $datos['estado'] = 1;
                    $datos['msj'] = 'Actualización exitosa';
                    $datos['registros'] = $registro;
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

    public function estado(Request $request)
    {
        $datos = [];
        $registro = Televisor::find($request->id);

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
        $registro = Televisor::find($request->id);

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
