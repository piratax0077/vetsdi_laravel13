<?php

namespace App\Services;

use App\Models\{Asistente, Bancos, Ciudad, Direccion, LiquidacionRecibo, Paciente, Profesional, User};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, DB, Hash, Schema};
use Illuminate\Validation\{Rule, ValidationException};

class PerfilUsuario
{
    public function liquidaciones(int $usuarioId)
    {
        $registros=LiquidacionRecibo::where('id_seccion',$usuarioId)->get();
        foreach($registros as $registro) {
            foreach(['serie','autor','casa','numero_control','email','otro'] as $campo) {
                if (!$registro->{$campo}) continue;
                try { $registro->{$campo}=decrypt($registro->{$campo}); }
                catch (\Illuminate\Contracts\Encryption\DecryptException $error) { $registro->{$campo}=''; }
            }
            $registro->banco=$registro->casa ? Bancos::select('id','nombre')->find($registro->casa) : null;
        }
        return $registros;
    }

    public function propio(string $tipo, Request $request): Asistente|Profesional
    {
        $modelo = $tipo === 'asistente' ? Asistente::class : Profesional::class;
        $perfil = $modelo::where('id_usuario', Auth::id())->firstOrFail();
        $id = $request->input('id_'.$tipo);
        abort_if($id !== null && (int) $id !== (int) $perfil->id, 403);
        return $perfil;
    }

    public function personales(string $tipo, Request $request): array
    {
        $perfil = $this->propio($tipo, $request);
        $nombre = $tipo === 'asistente' ? 'nombres' : 'nombre';
        $reglas = [
            $nombre => 'required|string|max:255',
            'apellido_uno' => 'required|string|max:255',
            'apellido_dos' => 'nullable|string|max:255',
            'sexo' => 'nullable|in:M,F,O',
            'rut' => ['sometimes','required','string','max:20',Rule::unique($perfil->getTable(), 'rut')->ignore($perfil->id),Rule::unique('users', 'rut')->ignore(Auth::id())],
        ];
        if ($tipo === 'asistente') {
            $reglas['nacimiento'] = 'sometimes|nullable|date_format:Y-m-d|before_or_equal:today';
        } else {
            $reglas += [
                'id_especialidad' => 'sometimes|nullable|integer|exists:especialidades,id',
                'id_tipo_especialidad' => 'sometimes|nullable|integer|exists:tipos_especialidad,id',
                'id_sub_tipo_especialidad' => 'sometimes|nullable|integer|exists:sub_tipo_especialidad,id',
                'id_tipo_atencion' => 'sometimes|nullable|integer|in:1,2,3,4',
                'num_colegio' => 'sometimes|nullable|string|max:255',
            ];
        }
        if ($tipo === 'profesional') {
            foreach (['id_especialidad','id_tipo_especialidad','id_sub_tipo_especialidad'] as $campo) {
                if ($request->exists($campo) && (string)$request->input($campo) === '0') $request->merge([$campo=>null]);
            }
        }
        $datos = $request->validate($reglas);
        if ($tipo === 'profesional') {
            $especialidad = array_key_exists('id_especialidad', $datos) ? $datos['id_especialidad'] : $perfil->id_especialidad;
            $especialidadTipo = array_key_exists('id_tipo_especialidad', $datos) ? $datos['id_tipo_especialidad'] : $perfil->id_tipo_especialidad;
            $subtipo = array_key_exists('id_sub_tipo_especialidad', $datos) ? $datos['id_sub_tipo_especialidad'] : $perfil->id_sub_tipo_especialidad;
            if ($especialidadTipo && !DB::table('tipos_especialidad')->where('id', $especialidadTipo)->where('id_especialidad', $especialidad)->exists()) {
                throw ValidationException::withMessages(['id_tipo_especialidad'=>'La especialidad no pertenece a la profesión seleccionada.']);
            }
            if ($subtipo && !DB::table('sub_tipo_especialidad')->where('id', $subtipo)->where('id_tipo_especialidad', $especialidadTipo)->exists()) {
                throw ValidationException::withMessages(['id_sub_tipo_especialidad'=>'La subespecialidad no pertenece a la especialidad seleccionada.']);
            }
        }
        DB::transaction(function () use ($perfil, $datos, $nombre) {
            foreach ($datos as $campo => $valor) $perfil->{$campo === 'nacimiento' ? 'fecha_nac' : $campo} = $valor;
            $perfil->saveOrFail();
            $usuario = User::findOrFail(Auth::id());
            $usuario->nombres = $perfil->{$nombre};
            $usuario->apellido_uno = $perfil->apellido_uno;
            $usuario->apellido_dos = $perfil->apellido_dos;
            if (array_key_exists('rut', $datos)) $usuario->rut = $datos['rut'];
            $usuario->name = trim(implode(' ', array_filter([$perfil->{$nombre},$perfil->apellido_uno,$perfil->apellido_dos])));
            $usuario->saveOrFail();
        });
        return ['success'=>true,'message'=>'Datos personales guardados.',$tipo=>$perfil];
    }

    public function contacto(string $tipo, Request $request): array
    {
        $perfil = $this->propio($tipo, $request);
        $request->merge(['email' => correo_normalizar($request->input('email'))]);
        $datos = $request->validate([
            'email'=>['required','email','max:255',Rule::unique('users','email')->ignore(Auth::id()),Rule::unique($perfil->getTable(),'email')->ignore($perfil->id)],
            'telefono_uno'=>'nullable|string|max:30',
        ]);
        DB::transaction(function () use ($perfil, $datos) {
            $usuario=User::findOrFail(Auth::id());
            $usuario->email=$datos['email'];
            if (array_key_exists('telefono_uno', $datos)) $usuario->telefono=$datos['telefono_uno'];
            $usuario->saveOrFail();
            $perfil->email=$datos['email'];
            if (array_key_exists('telefono_uno', $datos)) $perfil->telefono_uno=$datos['telefono_uno'];
            $perfil->saveOrFail();
        });
        return ['success'=>true,'message'=>'Datos de contacto guardados.',$tipo=>$perfil];
    }

    public function direccion(string $tipo, Request $request): array
    {
        $perfil=$this->propio($tipo,$request);
        $campo=$tipo === 'asistente' ? 'id_ciudad' : 'ciudad';
        $datos=$request->validate([$campo=>'required|integer|exists:ciudades,id','region'=>'sometimes|nullable|integer|exists:regiones,id','direccion'=>'required|string|max:255','numero_dir'=>'nullable|string|max:30']);
        $ciudad=Ciudad::findOrFail($datos[$campo]);
        if (!empty($datos['region']) && (int)$ciudad->id_region !== (int)$datos['region']) {
            throw ValidationException::withMessages([$campo=>'La ciudad no pertenece a la región seleccionada.']);
        }
        $direccion=DB::transaction(function () use ($perfil,$datos,$campo) {
            $direccion=DireccionesPerfil::editable($perfil);
            $direccion->id_ciudad=$datos[$campo];
            $direccion->direccion=$datos['direccion'];
            $direccion->numero_dir=$datos['numero_dir'] ?? null;
            $direccion->saveOrFail();
            $perfil->id_direccion=$direccion->id;
            $perfil->saveOrFail();
            return $direccion;
        });
        return ['success'=>true,'message'=>'Dirección guardada.',$tipo=>$perfil,'direccion'=>$direccion,'ciudad'=>$ciudad];
    }

    public function contrasena(Request $request): array
    {
        $datos=$request->validate(['contrasena_actual'=>'required|string','password_registro'=>'required|string|min:8|max:128|different:contrasena_actual','password_confirmacion_registro'=>'required|same:password_registro']);
        $usuario=User::findOrFail(Auth::id());
        if (!Hash::check($datos['contrasena_actual'],$usuario->password)) {
            throw ValidationException::withMessages(['contrasena_actual'=>'La contraseña actual no es correcta.']);
        }
        $usuario->password=Hash::make($datos['password_registro']);
        $usuario->saveOrFail();
        return ['success'=>true,'message'=>'Contraseña actualizada.'];
    }

    public function busqueda(Request $request): array
    {
        $asistente=$this->propio('asistente',$request);
        $datos=$request->validate(['buscador'=>'required|in:1,2','modalidad'=>'nullable|in:1,2,3','archivo'=>'nullable|array']);
        if ((int)$datos['buscador'] === 1 && empty($datos['modalidad'])) {
            throw ValidationException::withMessages(['modalidad'=>'Seleccione una modalidad de trabajo.']);
        }
        $resultado=['estado'=>1,'msj'=>'Configuración guardada.'];
        if (!empty($datos['archivo'])) {
            $ruta=normalizar_imagen_almacenada($datos['archivo'][0] ?? null);
            abort_unless($ruta && str_starts_with($ruta, 'asistente/curriculum/'.$asistente->id.'/') && !str_contains($ruta,'..') && \Illuminate\Support\Facades\Storage::disk('public')->exists($ruta),403);
            $asistente->curriculum=$ruta;
            $resultado['archivo']=['estado'=>1,'proceso'=>['url'=>storage_public_url($ruta)]];
        }
        $asistente->buscador=$datos['buscador'];
        $asistente->id_modalidad=$datos['modalidad'] ?? null;
        $asistente->saveOrFail();
        return $resultado;
    }
}
