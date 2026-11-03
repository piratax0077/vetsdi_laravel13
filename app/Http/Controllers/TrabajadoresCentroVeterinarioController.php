<?php

namespace App\Http\Controllers;

use App\Services\{OperacionVeterinaria,TrabajadoresCentroVeterinario};
use Illuminate\Http\Request;

class TrabajadoresCentroVeterinarioController extends Controller
{
    public function detalle(Request $r,OperacionVeterinaria $acceso,TrabajadoresCentroVeterinario $s,int $id)
    {
        return response()->json(['estado'=>1]+$s->detalle($acceso->institucion($r),$id));
    }

    public function guardar(Request $r,OperacionVeterinaria $acceso,TrabajadoresCentroVeterinario $s)
    {
        return response()->json(['estado'=>1,'id'=>$s->guardar($r,$acceso->institucion($r)),'msj'=>'Trabajador guardado.']);
    }

    public function categoria(Request $r,OperacionVeterinaria $acceso,TrabajadoresCentroVeterinario $s)
    {
        $i=$acceso->institucion($r);$id=$s->categoria($r,$i);
        return response()->json(['estado'=>1,'categoria'=>$s->registro('vet_categorias_trabajadores',$i,$id),'msj'=>'Categoría guardada.']);
    }

    public function estado(Request $r,OperacionVeterinaria $acceso,TrabajadoresCentroVeterinario $s,int $id)
    {
        $s->estado($r,$acceso->institucion($r),$id);return response()->json(['estado'=>1,'msj'=>'Estado actualizado.']);
    }

    public function contrato(Request $r,OperacionVeterinaria $acceso,TrabajadoresCentroVeterinario $s)
    {
        return response()->json(['estado'=>1,'id'=>$s->contrato($r,$acceso->institucion($r)),'msj'=>'Contrato guardado.']);
    }

    public function terminar(Request $r,OperacionVeterinaria $acceso,TrabajadoresCentroVeterinario $s,int $id)
    {
        $s->terminar($r,$acceso->institucion($r),$id);return response()->json(['estado'=>1,'msj'=>'Contrato cerrado.']);
    }

    public function mensaje(Request $r,OperacionVeterinaria $acceso,TrabajadoresCentroVeterinario $s,int $id)
    {
        $m=$s->mensaje($r,$acceso->institucion($r),$id);
        $msj=match($m->estado){'enviado'=>'Mensaje enviado al correo del trabajador.','registrado'=>'Mensaje guardado. Este entorno registra los correos sin entregarlos externamente.','fallido'=>'El correo no pudo enviarse. Puede intentarlo nuevamente desde un mensaje nuevo.',default=>'El mensaje está siendo procesado.'};
        return response()->json(['estado'=>$m->estado==='fallido'?0:1,'msj'=>$msj,'mensaje'=>$m]);
    }
}
