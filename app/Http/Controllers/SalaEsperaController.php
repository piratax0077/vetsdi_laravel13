<?php

namespace App\Http\Controllers;

use App\Models\SalaEspera;
use Illuminate\Http\Request;

class SalaEsperaController extends Controller
{
    public function verRegistro(Request $request)
    {
        $datos = [];
        $error = [];
        $valido = 1;

        if(empty($request->id))
        {
            $error['id'] = 'campo requerido';
            $valido = 0;
        }

        if($valido)
        {
            $registro = SalaEspera::with(['boxes', 'televisores'])->find($request->id);

            if ($registro) {
                $datos['estado'] = 1;
                $datos['msj'] = 'Registro encontrado';
                $datos['registros'] = $registro;
            } else {
                $datos['estado'] = 0;
                $datos['msj'] = 'Registro no encontrado';
            }
        }
        else
        {
            $datos['estado'] = 0;
            $datos['msj'] = 'Campos requeridos';
            $datos['error'] = $error;
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
        if(!empty($request->alias))
            $filtros[] = array('alias',$request->alias);
        if(!empty($request->token))
            $filtros[] = array('token',$request->token);
        if(!empty($request->piso))
            $filtros[] = array('piso',$request->piso);
        if(!empty($request->estado))
            $filtros[] = array('estado',$request->estado);


        $registros = SalaEspera::with(['boxes', 'televisores'])->were($filtros)->get();

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

        if (empty($request->nombre)) {
            $error['nombre'] = 'Campo requerido';
            $valido = false;
        }

        if (empty($request->id_lugar_atencion)) {
            $error['id_lugar_atencion'] = 'Campo requerido';
            $valido = false;
        }

        if ($valido) {
            try {
                $registro = new SalaEspera();
                $registro->id_institucion = $request->id_institucion;
                $registro->id_lugar_atencion = $request->id_lugar_atencion;
                $registro->alias = $request->alias;
                $registro->token = $request->token ?? uniqid();
                $registro->piso = $request->piso ?? 1;
                $registro->nombre = $request->nombre;
                $registro->descripcion = $request->descripcion;
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
        $error = [];
        $valido = true;

        if (empty($request->nombre)) {
            $error['nombre'] = 'Campo requerido';
            $valido = false;
        }

        if ($valido) {
            try {
                $registro = SalaEspera::find($request->id);

                if ($registro) {
                    $registro->id_institucion = $request->id_institucion ?? $registro->id_institucion;
                    $registro->id_lugar_atencion = $request->id_lugar_atencion ?? $registro->id_lugar_atencion;
                    $registro->alias = $request->alias ?? $registro->alias;
                    $registro->piso = $request->piso ?? $registro->piso;
                    $registro->nombre = $request->nombre ?? $registro->nombre;
                    $registro->descripcion = $request->descripcion ?? $registro->descripcion;

                    if ($registro->save()) {
                        $datos['estado'] = 1;
                        $datos['msj'] = 'Actualización exitosa';
                        $datos['registros'] = $registro;
                    } else {
                        $datos['estado'] = 0;
                        $datos['msj'] = 'Error al actualizar';
                    }
                } else {
                    $datos['estado'] = 0;
                    $datos['msj'] = 'Registro no encontrado';
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

    public function estado(Request $request)
    {
        $datos = [];
        $registro = SalaEspera::find($request->id);

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
        $registro = SalaEspera::find($request->id);

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
