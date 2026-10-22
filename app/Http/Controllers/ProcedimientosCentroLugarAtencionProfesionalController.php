<?php

namespace App\Http\Controllers;

use App\Models\ProcedimientosCentroLugarAtencionProfesional;
use Illuminate\Http\Request;

class ProcedimientosCentroLugarAtencionProfesionalController extends Controller
{
    public function registrar_r(Request $request)
    {
        if ($request->filled('arancel_procedimiento_id')) {
            $prof = \App\Models\Profesional::where('id_usuario', auth()->id())->firstOrFail();
            abort_unless($prof->LugaresAtencion()->where('lugares_atencion.id', $request->id_lugar_atencion)->exists(), 403);
            $request->validate(['minutos_bloque' => 'required|integer|min:1|max:1440', 'cantidad_bloques' => 'required|integer|min:1|max:1440']);
            $fila = \Illuminate\Support\Facades\DB::table('aranceles_profesionales')->where('id_profesional', $prof->id)->first();
            $opciones = app(\App\Services\ProcedimientosArancelados::class)->opciones($fila ? json_decode($fila->datos, true) : []);
            $opcion = collect($opciones)->firstWhere('id', $request->arancel_procedimiento_id);
            abort_unless($opcion, 422, 'Seleccione un procedimiento activo con valor configurado.');
            return \Illuminate\Support\Facades\DB::transaction(function () use ($request, $prof, $opcion) {
                $origen = 'arancel:'.$prof->id.':'.$opcion['id'];
                $centro = \App\Models\ProcedimientosCentro::where('id_lugar_atencion', $request->id_lugar_atencion)->where('otros', $origen)->first() ?? new \App\Models\ProcedimientosCentro();
                $centro->id_lugar_atencion = $request->id_lugar_atencion;
                $centro->nombre = $opcion['nombre'];
                $centro->valor = $opcion['valor'];
                $centro->minutos_bloque = $request->minutos_bloque;
                $centro->cantidad_bloques = $request->cantidad_bloques;
                $centro->tipo_ficha_atencion = 1;
                $centro->estado = 1;
                $centro->otros = $origen;
                $centro->save();
                return static::registrar($centro->id, $request->id_lugar_atencion, $prof->id, $opcion['nombre'], null, $request->minutos_bloque, $request->cantidad_bloques, $origen);
            });
        }
        return static::registrar( $request->id_procedimiento_centro, $request->id_lugar_atencion, $request->id_profesional,  $request->nombre, $request->descripcion, $request->minutos_bloque, $request->cantidad_bloques, $request->otros );
    }
    static public function registrar( $id_procedimiento_centro, $id_lugar_atencion, $id_profesional,  $nombre, $descripcion, $minutos_bloque, $cantidad_bloques, $otros )
    {
        $datos = array();
        $error = array();
        $valido = 1;

        if(empty($id_procedimiento_centro))
        {
            $error['id_procedimiento_centro'] = 'campo requerido';
            $valido = 0;
        }
        if(empty($id_lugar_atencion))
        {
            $error['id_lugar_atencion'] = 'campo requrido';
            $valido = 0;
        }
        if(empty($id_profesional))
        {
            $error['id_profesional'] = 'campo requerido';
            $valido = 0;
        }
        if(empty($nombre))
        {
            $error['nombre'] = 'campo requrido';
            $valido = 0;
        }
        // if(empty($descripcion))
        // {
        //     $error['descripcion'] = 'campo requrido';
        //     $valido = 0;
        // }
        if(empty($minutos_bloque))
        {
            $error['minutos_bloque'] = 'campo requrido';
            $valido = 0;
        }
        if(empty($cantidad_bloques))
        {
            $error['cantidad_bloques'] = 'campo requrido';
            $valido = 0;
        }
        // if(empty($otros))
        // {
        //     $error['otros'] = 'campo requrido';
        //     $valido = 0;
        // }
        // if(empty($estado))
        // {
        //     $error['estado'] = 'campo requrido';
        //     $valido = 0;
        // }

        if($valido)
        {
            $registro = new ProcedimientosCentroLugarAtencionProfesional();
            $registro->id_procedimiento_centro = $id_procedimiento_centro;
            $registro->id_lugar_atencion = $id_lugar_atencion;
            $registro->id_profesional = $id_profesional;
            $registro->nombre = $nombre;
            $registro->descripcion = $descripcion;
            $registro->minutos_bloque = $minutos_bloque;
            $registro->cantidad_bloques = $cantidad_bloques;
            $registro->otros = $otros;
            $registro->estado = 1;

            if($registro->save())
            {
                $datos['estado'] = 1;
                $datos['msj'] = 'resgistro exitoso';
            }
            else
            {
                $datos['estado'] = 0;
                $datos['msj'] = 'resgistro con falla';
            }
        }
        else
        {
            $datos['estado'] = 0;
            $datos['msj'] = 'campo requerido';
            $datos['error'] = $error;
        }

        return (object)$datos;
    }


    public function modificar_r(Request $request)
    {
        return static::modificar( $request->id,
                                    $request->id_procedimiento_centro,
                                    $request->id_lugar_atencion,
                                    $request->id_profesional,
                                    $request->nombre,
                                    $request->descripcion,
                                    $request->minutos_bloque,
                                    $request->cantidad_bloques,
                                    $request->otros,
                                    $request->estado );
    }
    static public function modificar($id, $id_procedimiento_centro, $id_lugar_atencion, $id_profesional, $nombre, $descripcion, $minutos_bloque, $cantidad_bloques, $otros, $estado )
    {
        $datos = array();
        $error = array();
        $valido = 1;

        if(empty($id))
        {
            $error['ID'] = 'campo requerido';
            $valido = 0;
        }

        if($valido)
        {
            $registro = ProcedimientosCentroLugarAtencionProfesional::find($id);
            if(!empty($id_procedimiento_centro))
                $registro->id_procedimiento_centro = $id_procedimiento_centro;
            if(!empty($id_lugar_atencion))
                $registro->id_lugar_atencion = $id_lugar_atencion;
            if(!empty($id_profesional))
                $registro->id_profesional = $id_profesional;
            if(!empty($nombre))
                $registro->nombre = $nombre;
            if(!empty($descripcion))
                $registro->descripcion = $descripcion;
            if(!empty($minutos_bloque))
                $registro->minutos_bloque = $minutos_bloque;
            if(!empty($cantidad_bloques))
                $registro->cantidad_bloques = $cantidad_bloques;
            if(!empty($otros))
                $registro->otros = $otros;
            if( intval($estado) == 1 || intval($estado) == 0 )
                $registro->estado = $estado;

            if($registro->save())
            {
                $datos['estado'] = 1;
                $datos['msj'] = 'resgistro exitoso';
            }
            else
            {
                $datos['estado'] = 0;
                $datos['msj'] = 'resgistro con falla';
            }
        }
        else
        {
            $datos['estado'] = 0;
            $datos['msj'] = 'campo requerido';
            $datos['error'] = $error;
        }

        return (object)$datos;
    }

    public function verRegistro($id)
    {
        $datos = array();
        $error = array();
        $valido = 1;

        if(!empty($id))
        {
            $error['ID'] = 'campo requerido';
            $valido = 0;
        }

        if($valido)
        {
            $registro = ProcedimientosCentroLugarAtencionProfesional::find($id);
            if($registro)
            {
                $datos['estado'] = 1;
                $datos['msj'] = 'registro';
                $datos['registro'] = $registro;
            }
            else
            {
                $datos['estado'] = 0;
                $datos['msj'] = 'sin registro';
            }
        }
        else
        {
            $datos['estado'] = 0;
            $datos['msj'] = 'campo requerido';
            $datos['error'] = $error;
        }

        return (object)$datos;
    }

    public function verRegistros_r( Request $request )
    {
        return static::verRegistros( $request->id,
                                $request->id_procedimiento_centro,
                                $request->id_lugar_atencion,
                                $request->id_profesional,
                                $request->nombre,
                                $request->descripcion,
                                $request->minutos_bloque,
                                $request->cantidad_bloques,
                                $request->otros,
                                $request->estado );
    }
    static public function verRegistros( $id, $id_procedimiento_centro, $id_lugar_atencion, $id_profesional, $nombre, $descripcion, $minutos_bloque, $cantidad_bloques, $otros, $estado )
    {
        $datos = array();
        $error = array();
        $valido = 1;

        if($valido)
        {
            $filtro = array();
            if(!empty($id))
                $filtro[] = array('id', $id);
            if(!empty($id_procedimiento_centro))
                $filtro[] = array('id_procedimiento_centro', $id_procedimiento_centro);
            if(!empty($id_lugar_atencion))
                $filtro[] = array('id_lugar_atencion', $id_lugar_atencion);
            if(!empty($id_profesional))
                $filtro[] = array('id_profesional', $id_profesional);
            if(!empty($nombre))
                $filtro[] = array('nombre', $nombre);
            if(!empty($descripcion))
                $filtro[] = array('descripcion', $descripcion);
            if(!empty($minutos_bloque))
                $filtro[] = array('minutos_bloque', $minutos_bloque);
            if(!empty($cantidad_bloques))
                $filtro[] = array('cantidad_bloques', $cantidad_bloques);
            if(!empty($otros))
                $filtro[] = array('otros', $otros);
            if(!empty($estado))
                $filtro[] = array('estado', $estado);

            $registro = ProcedimientosCentroLugarAtencionProfesional::where($filtro)->get();
            if($registro)
            {
                $datos['estado'] = 1;
                $datos['msj'] = 'registro';
                $datos['registro'] = $registro;
            }
            else
            {
                $datos['estado'] = 0;
                $datos['msj'] = 'sin registro';
            }
        }
        else
        {
            $datos['estado'] = 0;
            $datos['msj'] = 'campo requerido';
            $datos['error'] = $error;
        }

        return (object)$datos;
    }

}
