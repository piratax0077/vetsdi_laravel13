<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ControlObesidad;
use App\Models\FichaAtencion;
use App\Models\Profesional;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ControlObesidadController extends Controller
{
    public function store(Request $request)
    {
        $datos = $request->validate([
            'id_ficha_atencion' => 'required|integer|exists:fichas_atenciones,id',
            'id_paciente' => 'required|integer|exists:pacientes,id',
            'id_mascota' => 'required|integer|exists:mascotas,id',
            'peso' => 'required|numeric|min:0.01|max:999.99',
            'variacion' => 'required|numeric|min:-999.99|max:999.99',
            'ideal' => 'required|numeric|min:0.01|max:999.99',
        ]);

        $ficha = FichaAtencion::whereKey($datos['id_ficha_atencion'])
            ->where('id_paciente', $datos['id_paciente'])
            ->where('id_mascota', $datos['id_mascota'])
            ->firstOrFail();

        $profesional = Profesional::where('id_usuario', Auth::id())->first();
        abort_unless($profesional, 403, 'No existe un profesional asociado al usuario autenticado.');

        $control = new ControlObesidad();
        $control->peso = $datos['peso'];
        $control->variacion = $datos['variacion'];
        $control->ideal = $datos['ideal'];
        $control->id_profesional = $profesional->id;
        $control->id_paciente = $datos['id_paciente'];
        $control->id_ficha_atencion = $ficha->id;
        $control->save();

        return response()->json([
            'estado' => 1,
            'msj' => 'Control de obesidad guardado correctamente.',
            'registro' => $control,
        ]);
    }

    public function getControlObesidad(Request $request)
    {
        $datos= array();
        $filtro = array();


        if(!empty($request->id)) {
            $filtro[] = array('id',$request->id);
        }
       if(!empty($request->peso)) {
            $filtro[] = array('peso',$request->peso);
        }
       if(!empty($request->variacion)) {
            $filtro[] = array('variacion',$request->variacion);
        }
       if(!empty($request->ideal)) {
            $filtro[] = array('ideal',$request->ideal);
        }
       if(!empty($request->id_paciente)) {
            $filtro[] = array('id_paciente',$request->id_paciente);
        }
       if(!empty($request->id_profesional)) {
            $filtro[] = array('id_profesional',$request->id_profesional);
        }
       if(!empty($request->id_ficha_atencion)) {
            $filtro[] = array('id_ficha_atencion',$request->id_ficha_atencion);
        }


        $consulta = ControlObesidad::where($filtro);
        if (!empty($request->id_mascota)) {
            $fichasMascota = FichaAtencion::where('id_mascota', $request->id_mascota)->select('id');
            $consulta->whereIn('id_ficha_atencion', $fichasMascota);
        }

        $registros = $consulta->orderBy('created_at')->orderBy('id')->get();
        if(count($registros))
        {
            $datos['estado'] = 1;
            $datos['msj'] = 'registros encontrados';
            $datos['registros'] = $registros;
        }
        else{
            $datos['estado'] = 1;
            $datos['msj'] = 'sin registros encontrados';
            $datos['registros'] = array();
        }

        return $datos;
    }
}
