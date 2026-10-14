<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LiquidacionRecibo;
use App\Models\Bancos;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;

class LiquidacionReciboController extends Controller
{

    public function agregarLiquidacion(Request $request)
    {
        $result = LiquidacionReciboController::store( Auth::user()->id, $request->rut, $request->nombre, $request->banco, $request->cuenta, $request->email, $request->principal, $request->tipo_cuenta,1);
        return $result;
    }

    public function modificarLiquidacion(Request $request)
    {
        abort_unless(LiquidacionRecibo::where('id_seccion', Auth::id())->whereKey($request->id)->exists(), 403);
        $result = LiquidacionReciboController::edit($request->id, Auth::user()->id, $request->rut, $request->nombre, $request->banco, $request->cuenta, $request->email, $request->principal, $request->tipo_cuenta,1);
        return $result;
    }

    public function ver_registro_r(Request $request)
    {
        return static::ver_registro($request->id);
    }
    static public function ver_registro($id)
    {
        if(empty($id))
        {
            $error['id'] = 'campo requerido';
            $valido = 0;
        }

        if($valido==1)
        {
            $registro = LiquidacionRecibo::where('id', $id)->first();
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
        return $datos;
    }

    public function ver_registros_r(Request $request)
    {
        $datos = array();
        $result = static::ver_registros($request->id_seccion);

        if($result['estado'] == 1)
        {
            foreach ($result['registros'] as $key_registro => $value_registro)
            {
                $value_registro->preparar_para_perfil();
            }
            $datos = $result;
        }
        else
        {
            $datos = $result;
        }

        return $datos;
    }
    static public function ver_registros($id_seccion)
    {
        $valido=1;
        if(empty($id_seccion))
        {
            $error['id'] = 'campo requerido';
            $valido = 0;
        }

        if($valido==1)
        {
            $registro = LiquidacionRecibo::where('id_seccion', $id_seccion)->get();
            if($registro)
            {
                if($registro->count()>0)
                {
                    $datos['estado'] = 1;
                    $datos['msj'] = 'registro';
                    $datos['registros'] = $registro;
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
                $datos['msj'] = 'sin registro';
            }
        }
        else
        {
            $datos['estado'] = 0;
            $datos['msj'] = 'campo requerido';
            $datos['error'] = $error;
        }
        return $datos;
    }

    static public function store( $id_seccion, $serie, $autor, $casa, $numero_control, $email, $principal='0', $otro='', $estado=1 )
    {
        $datos = array();
        $error = array();
        $valido = 1;

        if(empty($id_seccion))
        {
            $error['id_seccion'] = 'campo requerido';
            $valido = 0;
        }
        if(empty($serie))
        {
            $error['serie'] = 'campo requerido';
            $valido = 0;
        }
        if(empty($autor))
        {
            $error['autor'] = 'campo requerido';
            $valido = 0;
        }
        if(empty($casa))
        {
            $error['casa'] = 'campo requerido';
            $valido = 0;
        }
        if(empty($numero_control))
        {
            $error['numero_control'] = 'campo requerido';
            $valido = 0;
        }
        if(empty($email))
        {
            $error['email'] = 'campo requerido';
            $valido = 0;
        }

        if($valido == 1)
        {

            $liquidacion  = new LiquidacionRecibo();

            $liquidacion->id_seccion = $id_seccion;
            $liquidacion->serie = encrypt($serie);
            $liquidacion->autor = encrypt($autor);
            $liquidacion->casa = encrypt($casa);
            $liquidacion->numero_control = encrypt($numero_control);
            $liquidacion->email = encrypt($email);
            $liquidacion->principal = $principal;
            $liquidacion->otro = encrypt($otro);
            $liquidacion->estado = $estado;

            if($liquidacion->save())
            {
                $datos['estado'] = 1;
                $datos['msj'] = 'registro';
                $datos['last_id'] = $liquidacion->id;

                $update_temp = LiquidacionRecibo::where('id_seccion', $id_seccion)->where('id','!=',$liquidacion->id)->update(['principal' => 0]);
                $datos['update'] = $update_temp;
            }
            else
            {
                $datos['estado'] = 0;
                $datos['msj'] = 'Problema al registrar';
            }
        }
        else
        {
            $datos['estado'] = 0;
            $datos['msj'] = 'Campos requeridos';
            $datos['error'] = $error;
        }
        return $datos;
    }

    static public function edit($id, $id_seccion='', $serie='', $autor='', $casa='', $numero_control='', $email='', $principal='', $otro='', $estado='' )
    {
        $datos = array();
        $error = array();
        $valido = 1;

        if(empty($id))
        {
            $error['id'] = 'campo requerido';
            $valido = 0;
        }

        if($valido == 1)
        {
            $registro = LiquidacionRecibo::where('id', $id)->first();
            if($registro)
            {
                if(!empty($id_seccion))
                    $registro->id_seccion = $id_seccion;
                if(!empty($serie))
                    $registro->serie = encrypt($serie);
                if(!empty($autor))
                    $registro->autor = encrypt($autor);
                if(!empty($casa))
                    $registro->casa = encrypt($casa);
                if(!empty($numero_control))
                    $registro->numero_control = encrypt($numero_control);
                if(!empty($email))
                    $registro->email = encrypt($email);
                if($principal != '')
                    $registro->principal = $principal;
                if(!empty($otro))
                    $registro->otro = encrypt($otro);
                if($estado != '')
                    $registro->estado = $estado;

                if($registro->save())
                {
                    $datos['estado'] = 1;
                    $datos['msj'] = 'Registro actualizado';
                }
                else
                {
                    $datos['estado'] = 0;
                    $datos['msj'] = 'Problemas al registrar';
                }
            }
            else
            {
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
        return $datos;

    }

    public function eliminarLiquidacion(Request $request)
    {
        abort_unless(LiquidacionRecibo::where('id_seccion', Auth::id())->whereKey($request->id)->exists(), 403);
        $datos = array();
        $error = array();
        $valido = 1;


        if($valido)
        {
            $registro = LiquidacionRecibo::find($request->id);

            if($registro)
            {
                $registro->delete();
                $datos['estado'] = 1;
                $datos['msj'] = 'Registro eliminado';
            }
            else
            {
                $datos['estado'] = 0;
                $datos['msj'] = 'registro no encontrado';
            }
        }
        else
        {
            $datos['estado'] = 0;
            $datos['msj'] = 'campo requerido';
            $datos['error'] = $error;
        }

        return $datos;
    }
}
