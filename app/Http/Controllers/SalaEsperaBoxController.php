<?php

namespace App\Http\Controllers;

use App\Models\SalaEsperaBox;
use Illuminate\Http\Request;

class SalaEsperaBoxController extends Controller
{
    public function verRegistro(Request $request)
    {
        $datos = [];
        $registro = SalaEsperaBox::with('salaEspera')->find($request->id);

        if ($registro) {
            $datos['estado'] = 1;
            $datos['mensaje'] = 'Registro encontrado';
            $datos['registros'] = $registro;
        } else {
            $datos['estado'] = 0;
            $datos['mensaje'] = 'Registro no encontrado';
        }

        return response()->json($datos);
    }

    public function verRegistros(Request $request)
    {
        $datos = [];
        $filtros = [];

        if(!empty($request->id))
            $filtros['id'] = $request->id;
        if(!empty($request->id_sala_espera))
            $filtros['id_sala_espera'] = $request->id_sala_espera;
        if(!empty($request->id_box))
            $filtros['id_box'] = $request->id_box;
        if(!empty($request->estado))
            $filtros['estado'] = $request->estado;

        $registros = SalaEsperaBox::with('salaEspera','boxesCm')->where($filtros)->get();

        if ($registros->count() > 0) {
            $datos['estado'] = 1;
            $datos['mensaje'] = 'Registros encontrados';
            $datos['registros'] = $registros;
        } else {
            $datos['estado'] = 0;
            $datos['mensaje'] = 'No hay registros disponibles';
        }

        return response()->json($datos);
    }

    public function agregar(Request $request)
    {
        $datos = [];
        $error = [];
        $valido = true;

        if (empty($request->id_sala_espera)) {
            $error['id_sala_espera'] = 'Campo requerido';
            $valido = false;
        }

        if (empty($request->id_box)) {
            $error['id_box'] = 'Campo requerido';
            $valido = false;
        }

        if ($valido) {
            try {
                $registro = new SalaEsperaBox();
                $registro->id_sala_espera = $request->id_sala_espera;
                $registro->id_box = $request->id_box;
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
        $registro = SalaEsperaBox::find($request->id);

        if ($registro) {
            try {
                $registro->id_sala_espera = $request->id_sala_espera ?? $registro->id_sala_espera;
                $registro->id_box = $request->id_box ?? $registro->id_box;

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
        $registro = SalaEsperaBox::find($request->id);

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
        $registro = SalaEsperaBox::find($request->id);

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
