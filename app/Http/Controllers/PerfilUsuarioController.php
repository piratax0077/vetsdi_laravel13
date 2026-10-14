<?php

namespace App\Http\Controllers;

use App\Services\PerfilUsuario;
use Illuminate\Http\Request;

class PerfilUsuarioController extends Controller
{
    public function personales(Request $request, PerfilUsuario $perfiles)
    {
        return response()->json($perfiles->personales('asistente',$request));
    }

    public function contacto(Request $request, PerfilUsuario $perfiles)
    {
        return response()->json($perfiles->contacto('asistente',$request));
    }

    public function direccion(Request $request, PerfilUsuario $perfiles)
    {
        return response()->json($perfiles->direccion('asistente',$request));
    }

    public function contrasena(Request $request, PerfilUsuario $perfiles)
    {
        return response()->json($perfiles->contrasena($request));
    }

    public function archivo(Request $request, PerfilUsuario $perfiles)
    {
        $asistente=$perfiles->propio('asistente',$request);
        $request->validate(['file'=>'required|file|mimes:pdf|max:4096']);
        $archivo=$request->file('file');
        $ruta=app(\App\Services\Fotografias::class)->guardar($archivo,'asistente/curriculum/'.$asistente->id);
        return response()->json(['estado'=>1,'archivo'=>['url'=>storage_public_url($ruta),'original_file_name'=>$archivo->getClientOriginalName(),'nombre_archivo'=>basename($ruta),'file_extension'=>$archivo->extension(),'file_mime_type'=>$archivo->getMimeType()]]);
    }
}
