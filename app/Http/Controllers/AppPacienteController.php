<?php

namespace App\Http\Controllers;

use App\Models\Antecedente;
use App\Models\AntecedentesPaciente;
// carbon
use Carbon\Carbon;
// modelos
use App\Models\Ciudad;
use App\Models\ControlObesidad;
use App\Models\Diabete;
use App\Models\Direccion;
use App\Models\DocumentoFcPaciente;
use App\Models\Especialidad;
use App\Models\ExamenEspecialidad;
use App\Models\ExamenMedico;
use App\Models\FichaAtencion;
use App\Models\GrupoSanguineo;
use App\Models\Hipertension;
use App\Models\HoraMedica;
use App\Models\LogUserDevice;
use App\Models\LogUsersDevices;
use App\Models\Mensajes;
use App\Models\UsersDevices;
use App\Models\LugarAtencion;
use App\Models\Mascota;
use App\Models\NotificacionConfirmacion;
use App\Models\OdontogramaPaciente;
use App\Models\Paciente;
use App\Models\PacienteContactoEmergencia;
use App\Models\PacienteOdontograma;
use App\Models\PacientesDependientes;
use App\Models\Profesional;
use App\Models\ProfesionalHorario;
use App\Models\Region;
use App\Models\Recomendacion;
use App\Models\RecomendacionDetalle;
use App\Models\ResultadoExamen;
use App\Models\ConConsentimientosPcte;
use App\Models\SubTipoEspecialidad;
use App\Models\TipoEspecialidad;
use App\Models\ContactoEmergencia;
use App\Models\User;
use Illuminate\Http\Request;

use ArrayObject;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Laravel\Sanctum\PersonalAccessToken;

class AppPacienteController extends Controller
{
    public function getMensajesApp(Request $request)
    {
        $authToken = $request->header('X-Auth-Token');
        $token = $authToken ? PersonalAccessToken::findToken($authToken) : null;
        $user = $token?->tokenable;
        if (!$user) {
            return response()->json(['estado' => 0, 'mensaje' => 'Token de autenticación inválido'], 401);
        }

        $mensajes = Mensajes::query()
            ->where('id_receptor', $user->id)
            ->orderByDesc('fecha_envio')
            ->orderByDesc('id')
            ->limit(100)
            ->get();

        $emisores = User::whereIn('id', $mensajes->pluck('id_usuario')->filter()->unique())
            ->pluck('name', 'id');

        $registros = $mensajes->map(function ($mensaje) use ($emisores) {
            $datos = json_decode((string) $mensaje->datos_mensaje, true);
            $datos = is_array($datos) ? $datos : [];
            $archivos = $datos['archivos'] ?? [];
            if (!is_array($archivos)) {
                $archivos = [];
            }
            if (isset($archivos['url']) || isset($archivos['ruta']) || isset($archivos['path'])) {
                $archivos = [$archivos];
            }

            foreach (['imagen', 'image', 'archivo', 'adjunto'] as $claveAdjunto) {
                if (!empty($datos[$claveAdjunto])) {
                    $archivos[] = $datos[$claveAdjunto];
                }
            }

            $archivos = collect($archivos)->map(function ($archivo) {
                if (is_string($archivo)) {
                    $archivo = ['url' => $archivo];
                }
                if (!is_array($archivo)) {
                    return null;
                }

                $urlArchivo = $archivo['url'] ?? $archivo['ruta'] ?? $archivo['path'] ?? null;
                if (!$urlArchivo) {
                    return null;
                }

                $urlArchivo = trim((string) $urlArchivo);
                if (!preg_match('#^https?://#i', $urlArchivo)) {
                    $urlArchivo = url('/'.ltrim($urlArchivo, '/'));
                }

                $nombre = $archivo['nombre'] ?? $archivo['name'] ?? basename(parse_url($urlArchivo, PHP_URL_PATH));
                $mime = strtolower((string) ($archivo['mime'] ?? $archivo['tipo'] ?? ''));
                $extension = strtolower(pathinfo(parse_url($urlArchivo, PHP_URL_PATH), PATHINFO_EXTENSION));
                $esImagen = str_starts_with($mime, 'image/') || in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg'], true);

                return [
                    'nombre' => $nombre ?: 'Adjunto',
                    'url' => $urlArchivo,
                    'mime' => $mime,
                    'es_imagen' => $esImagen,
                ];
            })->filter()->values()->all();

            return [
                'id' => $mensaje->id,
                'asunto' => $datos['asunto'] ?? $datos['titulo'] ?? 'Mensaje VeterChile',
                'mensaje' => $datos['mensaje'] ?? '',
                'emisor' => $emisores[$mensaje->id_usuario] ?? 'VeterChile',
                'fecha_envio' => Carbon::parse($mensaje->created_at ?: $mensaje->fecha_envio)->format('d-m-Y H:i'),
                'leido' => (int) $mensaje->estado !== 1,
                'archivos' => $archivos,
            ];
        })->values();

        return response()->json([
            'estado' => 1,
            'registros' => $registros,
            'no_leidos' => $mensajes->where('estado', 1)->count(),
        ]);
    }

    public function marcarMensajeAppLeido(Request $request, int $id)
    {
        $authToken = $request->header('X-Auth-Token');
        $token = $authToken ? PersonalAccessToken::findToken($authToken) : null;
        $user = $token?->tokenable;
        if (!$user) {
            return response()->json(['estado' => 0, 'mensaje' => 'Token de autenticación inválido'], 401);
        }

        $mensaje = Mensajes::where('id', $id)->where('id_receptor', $user->id)->first();
        if (!$mensaje) {
            return response()->json(['estado' => 0, 'mensaje' => 'Mensaje no encontrado'], 404);
        }

        $mensaje->estado = 0;
        $mensaje->save();

        return response()->json(['estado' => 1, 'mensaje' => 'Mensaje marcado como leído']);
    }

    public function buscarPersonaAppPorRut(Request $request)
    {
        $authToken = $request->header('X-Auth-Token');
        $token = $authToken ? PersonalAccessToken::findToken($authToken) : null;
        if (!$token?->tokenable) {
            return response()->json(['estado' => 0, 'mensaje' => 'Token de autenticación inválido'], 401);
        }

        $rut = strtoupper(preg_replace('/[^0-9k]/i', '', (string) $request->query('rut')));
        if (strlen($rut) < 2) {
            return response()->json(['estado' => 0, 'mensaje' => 'RUT inválido'], 422);
        }

        $apiUrl = rtrim((string) config('services.personas.url'), '/');
        if ($apiUrl === '') {
            return response()->json(['estado' => 0, 'mensaje' => 'La base Personas no está configurada'], 503);
        }

        try {
            $response = Http::acceptJson()
                ->withToken((string) config('services.personas.token'))
                ->connectTimeout((int) config('services.personas.connect_timeout', 2))
                ->timeout((int) config('services.personas.timeout', 5))
                ->get($apiUrl.'/api/personas/'.$rut);

            if ($response->successful() && $response->json('found')) {
                $persona = $response->json('persona', []);
                return response()->json([
                    'estado' => 1,
                    'persona' => [
                        'rut' => $persona['rut_original'] ?? $rut,
                        'nombre' => trim(implode(' ', array_filter([
                            $persona['nombre1'] ?? '',
                            $persona['appaterno'] ?? '',
                            $persona['apmaterno'] ?? '',
                        ]))),
                        'correo' => $persona['email'] ?? '',
                        'telefono' => $persona['telefono'] ?? '',
                    ],
                ]);
            }

            return response()->json(['estado' => 0, 'mensaje' => 'El RUT no está registrado en la base Personas']);
        } catch (\Throwable $e) {
            Log::warning('No fue posible consultar Personas API desde VeterChile.', [
                'rut' => $rut,
                'message' => $e->getMessage(),
            ]);
            return response()->json(['estado' => 0, 'mensaje' => 'No fue posible consultar la base Personas'], 503);
        }
    }

    public function buscarProfesionalesEmergencia(Request $request)
    {
        $authToken = $request->header('X-Auth-Token');
        $token = $authToken ? PersonalAccessToken::findToken($authToken) : null;
        if (!$token?->tokenable) {
            return response()->json(['message' => 'Token de autenticación inválido'], 401);
        }

        $texto = mb_strtolower(trim((string) $request->query('q')));
        if (mb_strlen($texto) < 3) {
            return response()->json([]);
        }
        $rut = preg_replace('/[^0-9k]/i', '', $texto);

        $profesionales = Profesional::with(['Especialidad:id,nombre', 'TipoEspecialidad:id,nombre'])
            ->where('estado', 1)
            ->where(function ($query) use ($texto, $rut) {
                $query->whereRaw('LOWER(nombre) LIKE ?', ["%{$texto}%"])
                    ->orWhereRaw('LOWER(apellido_uno) LIKE ?', ["%{$texto}%"])
                    ->orWhereRaw('LOWER(apellido_dos) LIKE ?', ["%{$texto}%"])
                    ->orWhereRaw('LOWER(email) LIKE ?', ["%{$texto}%"]);
                if ($rut !== '') {
                    $query->orWhereRaw(
                        'REPLACE(REPLACE(LOWER(rut), ".", ""), "-", "") LIKE ?',
                        ["%{$rut}%"]
                    );
                }
            })
            ->limit(15)
            ->get()
            ->map(fn ($profesional) => [
                'id' => $profesional->id,
                'name' => $profesional->nombreCompleto(),
                'specialty' => $profesional->TipoEspecialidad?->nombre
                    ?: $profesional->Especialidad?->nombre
                    ?: 'Medicina veterinaria',
                'phone' => $profesional->telefono_uno ?: $profesional->telefono_dos,
                'email' => $profesional->email,
            ])
            ->values();

        return response()->json($profesionales);
    }

    public function getMisMascotasApp(Request $request)
    {
        $authToken = $request->header('X-Auth-Token');
        $token = $authToken ? PersonalAccessToken::findToken($authToken) : null;
        $user = $token?->tokenable;

        if (!$user) {
            return response()->json(['estado' => 0, 'mensaje' => 'Token de autenticación inválido'], 401);
        }

        $paciente = Paciente::where('id_usuario', $user->id)->first();
        if (!$paciente) {
            return response()->json(['estado' => 1, 'mascotas' => []]);
        }

        $mascotas = Mascota::with(['especieMascota:id,nombre', 'razaMascota:id,nombre'])
            ->where('id_responsable', $paciente->id)
            ->where('estado', 1)
            ->vivas()
            ->orderBy('nombre')
            ->get()
            ->map(fn ($mascota) => [
                'id' => $mascota->id,
                'nombre' => $mascota->nombre,
                'especie' => $mascota->especieMascota?->nombre,
                'raza' => $mascota->razaMascota?->nombre,
            ])
            ->unique(fn ($mascota) => mb_strtolower(trim($mascota['nombre'])) . '|' .
                mb_strtolower(trim((string) $mascota['especie'])) . '|' .
                mb_strtolower(trim((string) $mascota['raza'])))
            ->values();

        return response()->json(['estado' => 1, 'mascotas' => $mascotas]);
    }

    //
    public function getMiFichaMedica(Request $request){

        // Validación de token X-Auth-Token
        $authToken = $request->header('X-Auth-Token');
        if (!$authToken) {
            return response()->json([
                'estado' => 0,
                'mensaje' => 'Token de autenticación requerido'
            ], 401);
        }

        // Buscar el token en la base de datos
        $token = PersonalAccessToken::findToken($authToken);
        if (!$token) {
            return response()->json([
                'estado' => 0,
                'mensaje' => 'Token de autenticación inválido'
            ], 401);
        }

        // Verificar que el token no haya expirado
        if ($token->expires_at && $token->expires_at->isPast()) {
            return response()->json([
                'estado' => 0,
                'mensaje' => 'Token de autenticación expirado'
            ], 401);
        }

        // Obtener el usuario autenticado del token
        $user = $token->tokenable;
        if (!$user) {
            return response()->json([
                'estado' => 0,
                'mensaje' => 'Usuario no encontrado'
            ], 401);
        }

        //capturamos el id_usuario receptor
        $id_usuario = $request->id_paciente;
        // return response()->json([
        //     'estado' => 1,
        //     'id_usuario' => $id_usuario,
        //     'registros' => $request->all()
        // ]);
        /* PACIENTE */
        $paciente = Paciente::where('id_usuario', $id_usuario)->first();
        // return response()->json([
        //     'estado' => 1,
        //     'paciente' => $paciente
        // ]);
         if (!$paciente) {
            return response()->json([
                'estado' => 0,
                'mensaje' => 'Paciente no encontrado',
                'registros' => []
            ]);
        }

        // La Ficha Veterinaria Única pertenece a una mascota concreta. Evita
        // reutilizar antecedentes humanos del tutor en la vista veterinaria.
        if ($request->filled('id_mascota')) {
            $mascota = Mascota::with(['especieMascota:id,nombre', 'razaMascota:id,nombre'])
                ->whereKey($request->integer('id_mascota'))
                ->where('id_responsable', $paciente->id)
                ->first();

            if (!$mascota) {
                return response()->json(['estado' => 0, 'mensaje' => 'Mascota no encontrada'], 404);
            }

            $fichasApp = \App\Models\FichaAtencionApp::where('id_mascota', $mascota->id)
                ->where('estado', 1)
                ->latest()
                ->get();
            $antecedentesMascota = [];
            if (trim((string) $mascota->enfermedad_cronica) !== '') {
                $antecedentesMascota[] = [
                    'id_tipo_antecedente' => 1,
                    'antecedente_data' => ['nombre' => $mascota->enfermedad_cronica],
                    'created_at' => $mascota->updated_at,
                ];
            }

            return response()->json([
                'estado' => 1,
                'mascota' => [
                    'id' => $mascota->id,
                    'nombre' => $mascota->nombre,
                    'especie' => $mascota->especieMascota?->nombre,
                    'raza' => $mascota->razaMascota?->nombre,
                ],
                'grupo_sanguineo' => null,
                'antecedentes' => $antecedentesMascota,
                'medicamentos_cronicos' => [],
                'ultimos_medicamentos' => $fichasApp->whereNotNull('medicamentos')->take(5)->map(fn ($ficha) => [
                    'nombre' => $ficha->medicamentos,
                    'fecha' => $ficha->created_at,
                ])->values(),
                'fichas' => $fichasApp->map(fn ($ficha) => [
                    'hipotesis_diagnostico' => $ficha->diagnosticos,
                    'created_at' => $ficha->created_at,
                ])->values(),
                'protesis_ortesis' => [],
                'odontograma' => [],
                'contacto_emergencia' => null,
            ]);
        }

        list($ano,$mes,$dia) = explode("-",$paciente->fecha_nac);
        $ano_diferencia  = date("Y") - $ano;
        $mes_diferencia = date("m") - $mes;
        $dia_diferencia   = date("d") - $dia;
        if ($dia_diferencia < 0 || $mes_diferencia < 0)
          $ano_diferencia--;

        $edad = $ano_diferencia;
        $paciente->fecha_nac = $dia.'-'.$mes.'-'.$ano;
        $paciente->edad = $edad;

        /* DIRECCION */
        $direccion = Direccion::find($paciente->id_direccion);

        if($direccion)
        {
            $direccion_nombre = $direccion->direccion;
            $numero_dir = $direccion->numero_dir;
            $id_ciudad = $direccion->id_ciudad;

            $ciudad = Ciudad::find($id_ciudad);
            $ciudad_nombre = $ciudad->nombre;
            $region = Region::find($ciudad->id_region);
            $region_nombre = $region->nombre;
        }else{
            $direccion_nombre = "";
            $numero_dir = "";
            $ciudad_nombre = "";
            $region_nombre = "";
        }

        // $direccion = (object)$direccion = array(
        //     'direccion' => $direccion_nombre,
        //     'numero' => $numero_dir,
        //     'ciudad' => $ciudad_nombre,
        //     'region' => $region_nombre,
        // );

        /* CONTACTO EMERGENCIA */
        $pacientes_contacto_emergencia = PacienteContactoEmergencia::where('id_paciente',$paciente->id)->first();
        if(is_object($pacientes_contacto_emergencia))
        {
            $contacto_emergencia = ContactoEmergencia::find($pacientes_contacto_emergencia->id_contacto);

            list($ano,$mes,$dia) = explode("-",$paciente->fecha_nac);
            $ano_diferencia  = date("Y") - $ano;
            $mes_diferencia = date("m") - $mes;
            $dia_diferencia   = date("d") - $dia;
            if ($dia_diferencia < 0 || $mes_diferencia < 0)
            $ano_diferencia--;

            $edad = $ano_diferencia;
            $contacto_emergencia->fecha_nac = $dia.'-'.$mes.'-'.$ano;
            $contacto_emergencia->edad = $edad;

        }
        else
        {
            $contacto_emergencia = (object) array(
                'nombre'=>'N/A',
                'apellido_uno'=>'N/A',
                'apellido_dos'=>'N/A',
                'rut'=>'N/A',
                'edad'=>'N/A',
                'email'=>'N/A',
                'fecha_nac'=>'N/A',
                'telefono'=>'N/A',
                'parentezco'=>'N/A'
            );
        }

        /* ANTECEDENTES */
        $id_antecedente = $paciente->id_antecedente;
        if($id_antecedente!=null)
        {
            $antecedentes_paciente = AntecedentesPaciente::find($id_antecedente);
        }
        else
        {
            $antecedentes_paciente = (object) array(
                'id'=>'',
                'transfusion'=>'N/A',
                'dona_organos'=>'N/A',
                'dona_organos_parcial'=>'N/A',
                'dona_sangre'=>'N/A',
                'impedimento_donar'=>'N/A',
                'comentario_gs'=>'N/A',
                'comentarios'=>'N/A',
                'hepatitis'=>'N/A',
                'comentario_hepa'=>'N/A',
                'id_grupo_sanguineo'=>0,
            );
        }

        /* SANGUINEO */
        $id_grupo_sanguineo = $antecedentes_paciente->id_grupo_sanguineo;
        if($id_grupo_sanguineo!=0)
        {
            $grupo_sanguineo = GrupoSanguineo::find($id_grupo_sanguineo);
        }
        else
        {
            $grupo_sanguineo = (object) array(
                'id'=> 0,
                'nombre_gs'=> 'N/A',
                'descripcion_gs'=> 'N/A'
            );
        }

        /* ANTECEDENTES */
        $antecedentes = Antecedente::where('id_paciente',$paciente->id)->with('users','paciente','tipo_antecendente','profesional')->get();
        foreach ($antecedentes as $valor)
        {
            $valor['antecedente_data'] = json_decode($valor['data']);
        }

        /** RESPONSABLES */
        $responsables = '';
        /** validar si es dependiente */
        $array_id_responsable = PacientesDependientes::where('id_paciente', $paciente->id)->pluck('id_responsable')->toArray();
        if(count($array_id_responsable) > 0)
        {
            $responsables = Paciente::whereIn('id', $array_id_responsable)->get();
        }

        /** RECETAS */
        $fecha_actual = date("d-m-Y");
        $regisrto_result = array();
        $lista_recetas = Recomendacion::whereDate('created_at', '>=', date("Y-m-d",strtotime($fecha_actual."- 1 week")) )->where('activo',$paciente->id)->pluck('id')->toArray();
        if($lista_recetas)
        {
            $registros = Recomendacion::whereIn('id', $lista_recetas)->get();
            if($registros)
            {
                $regisrto_result = array();
                foreach ($registros as $key => $value)
                {
                    $detalle = RecomendacionDetalle::where('id_recomendacion',$value->id)->get();
                    $detalle_temp = array();
                    if($detalle)
                    {
                        $detalle_temp = array();
                        foreach ($detalle as $key_det => $value_det)
                        {
                            $detalle_temp[] = array(
                                'id' => $value_det->id,
                                'id_receta' => $value_det->id_recomendacion,
                                'id_tipo_control' => decrypt($value_det->control),
                                'id_producto' => decrypt($value_det->id_articulo),
                                'producto' => decrypt($value_det->articulo),
                                'farmaco' => decrypt($value_det->componente),
                                'id_presentacion' => decrypt($value_det->id_apariencia),
                                'presentacion' => decrypt($value_det->apariencia),
                                'id_receta_dosis' => decrypt($value_det->id_cuota),
                                'posologia' => decrypt($value_det->cuota),
                                'id_via_administracion' => decrypt($value_det->id_regimen),
                                'via_administracion' => decrypt($value_det->regimen),
                                'id_periodo' => decrypt($value_det->id_lapso),
                                'periodo' => decrypt($value_det->lapso),
                                'uso_cronico' => decrypt($value_det->uso_frecuente),
                                'cantidad_compra' => decrypt($value_det->volumen_compra),
                                'cantidad' => decrypt($value_det->volumen),
                                'cantidad_vendida' => decrypt($value_det->volumen_entregado),
                                'comentario' => decrypt($value_det->comentario),
                                'token_doc' => $value_det->cod_doc,
                                'estado' => $value_det->estado,
                                'created_at' => $value_det->created_at,
                                'updated_at' => $value_det->updated_at,
                            );
                        }
                    }

                    $regisrto_result[] = array(
                        'id' => $value->id,
                        'id_ficha_atencion' => $value->atencion,
                        'id_ingreso_paciente' => $value->salida,
                        'id_recuperacion' => $value->herir,
                        'id_sala' => $value->cuadro,
                        'id_paciente' => $value->activo,
                        'id_profesional' => $value->aficionado,
                        'id_tipo_control' => $value->control,
                        'token_doc' => $value->cod_doc,
                        'token_auto' => $value->cod_auto,
                        'pdf' => $value->info,
                        'estado' => $value->estado,
                        'detalle' => $detalle_temp,
                        'created_at' => $value->created_at,
                        'updated_at' => $value->updated_at,
                    );
                }
            }
        }

        /** Control enfermedades Cronicas */
        $control_enfer_cronicas = array();
        /** obsidad */
        $obesidad = ControlObesidad::where('id_paciente', $paciente->id)->get();
        if($obesidad)
        {
            foreach ($obesidad as $key => $value)
            {
                $temp = array(
                    'fecha' => date('d-m-Y', strtotime($value->created_at)),
                    'tipo' => 'Obesidad',
                    'detalle' => array(
                        'Peso' => $value->peso,
                        'Variación' => $value->variacion,
                        'Ideal' => $value->ideal,
                    )
                );
                $control_enfer_cronicas[] = $temp;
            }
        }

        /** diabetes */
        $diabetes = Diabete::where('id_paciente', $paciente->id)->get();
        if($diabetes)
        {
            foreach ($diabetes as $key => $value)
            {
                $temp = array(
                    'fecha' => date('d-m-Y', strtotime($value->created_at)),
                    'tipo' => 'Diabetes',
                    'detalle' => array(
                        'Peso' => $value->peso,
                        'Piés' => $value->pies,
                        'HG A1c' => $value->hgac1,
                        'Colesterol' => $value->colesterol,
                        'Creatina' => $value->creatina,
                        'Glicosilada postprandial' => $value->glicosilada_postprandial,
                        'Glicosilada ayuno' => $value->glicosinada_ayuno,
                    )
                );
                $control_enfer_cronicas[] = $temp;
            }
        }

        /** hipertensiones */
        $hipertension = Hipertension::where('id_paciente', $paciente->id)->get();
        if($hipertension)
        {
            foreach ($hipertension as $key => $value)
            {
                $temp = array(
                    'fecha' => date('d-m-Y', strtotime($value->created_at)),
                    'tipo' => 'Hipertensión',
                    'detalle' => array(
                        'Presión Sistólica' => $value->sistolica,
                        'Presión Diastólica' => $value->diastolica,
                        'Presión Ideal' => $value->ideal,
                    )
                );
                $control_enfer_cronicas[] = $temp;
            }
        }


        /* ATENCIONES MEDICAS */
		$profesional = Profesional::where('id_usuario',$id_usuario)->first();
        $fichas = FichaAtencion::where('id_paciente', $paciente->id)->where('finalizada', 1)->get();
        if($fichas->count() == 0){
             $fichas = FichaAtencion::where('id_paciente', $paciente->id)->where('finalizada', 1)->get();
        }
        // $fichas = FichaAtencion::where('id_paciente', $paciente->id)->where('finalizada', 1)->get();
        $especialidad = Especialidad::where('estado',1)->get();
        $sub_tipo_especialidad = SubTipoEspecialidad::where('estado',1)->get();

        /** EXAMENES DE ESPECIALIDAD REALIZADOS */
        $examenes_especialidad_realizados = ExamenEspecialidad::select('id', 'id_tipo', 'id_template', 'id_examen_tipo', 'id_sub_tipo_especialidad', 'id_ficha_atencion', 'id_ficha_especialidad', 'id_paciente', 'id_profesional', 'id_asistente', 'nombre', 'revisado', 'estado')
                                                            ->with(['HoraMedica' => function($query){
                                                                $query->select('id', 'id_ficha_atencion', 'fecha_realizacion_consulta', 'id_estado');
                                                            }])
                                                            ->with(['ExamenEspecialidadTemplate' => function($query){
                                                                $query->select('id', 'nombre', 'alias');
                                                            }])
                                                            ->with(['ExamenEspecialidadTipo' => function($query){
                                                                $query->select('id', 'nombre', 'descripcion');
                                                            }])
                                                            ->with(['SubTipoEspecialidad' => function($query){
                                                                $query->select('id', 'nombre');
                                                            }])
                                                            ->where('id_paciente', $paciente->id)
                                                            ->get();

        /** resultado de examenes */
        // $resultado_examen = ResultadoExamen::where('id_paciente', $paciente->id)->get();
        $resultado_examen = ResultadoExamen::with('ResultadoExamenArchivo')->where('id_paciente', $paciente->id)->get();
        if($resultado_examen)
        {
            foreach ($resultado_examen as $key => $value)
            {
                $result_tipo_ex = ExamenMedico::where('id', $value->tipo_examen)->get()->first();
                $resultado_examen[$key]['obj_tipo_examen'] = $result_tipo_ex;
            }
        }

        /* --------------------------- HTML MODAL -------------------------- */
        //MODALS - Externos
        $contacto_emergencia_html = "
            <table class='table table-bordered table-xs'>
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Apellido Materno</th>
                        <th>Apellido Paterno</th>
                        <th>Rut</th>
                        <th>Edad</th>
                        <th>Email</th>
                        <th>Fecha Nacimiento</th>
                        <th>Teléfono</th>
                        <th>Parentezco</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>$contacto_emergencia->nombre</td>
                        <td>$contacto_emergencia->apellido_uno</td>
                        <td>$contacto_emergencia->apellido_dos</td>

                        <td>$contacto_emergencia->rut</td>
                        <td>$contacto_emergencia->edad</td>
                        <td>$contacto_emergencia->email</td>

                        <td>$contacto_emergencia->fecha_nac</td>
                        <td>$contacto_emergencia->telefono</td>
                        <td>$contacto_emergencia->parentezco</td>
                    </tr>
                </tbody>
            </table>
        ";

        $responsables_html = "
            <table class='table table-bordered table-xs'>
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Apellido Materno</th>
                        <th>Apellido Paterno</th>
                        <th>Rut</th>
                        <th>Email</th>
                        <th>Teléfono </th>
                    </tr>
                </thead>
                <tbody>";
                if($responsables)
                {
                    foreach ($responsables as $key => $value)
                    {
                        $responsables_html .= "<tr>
                            <td>$value->nombres</td>
                            <td>$value->apellido_uno</td>
                            <td>$value->apellido_dos</td>
                            <td>$value->rut</td>
                            <td>$value->email</td>
                            <td>$value->telefono</td>
                        </tr>";
                    }
                }
        $responsables_html .=     "</tbody>
            </table>";

        // $regisrto_result
        $receta_activa_html = "
            <table class='table table-bordered table-xs'>
            <thead>
                <tr>
                    <th>Fecha Receta</th>
                    <th>Producto</th>
                    <th>Presentación</th>
                    <th>Posologia</th>
                    <th>Via<br/>Administracion</th>
                    <th>Periodo</th>
                    <th>Cantidad Compra</th>
                </tr>
            </thead>
            <tbody>";
            if($regisrto_result)
            {
                foreach ($regisrto_result as $key => $value)
                {
                    foreach ($value['detalle'] as $key_detalle => $value_detalle)
                    {
                        $receta_activa_html .= "
                            <tr>
                                <td>".date('d-m-Y', strtotime($value['created_at']))."</td>
                                <td>".$value_detalle['producto']."<br/><span style=\"font-size:10px;\">".$value_detalle['farmaco']."</span></td>
                                <td>".$value_detalle['presentacion']."</td>
                                <td>".$value_detalle['posologia']."</td>
                                <td>".$value_detalle['via_administracion']."</td>
                                <td>".$value_detalle['periodo']."</td>
                                <td>".$value_detalle['cantidad_compra']."</td>
                            </tr>";
                    }
                }
            }
        $receta_activa_html .= "
            </tbody>
        </table>";

        // $datos = (object)array(
        //     'contacto_emergencia' =>  $contacto_emergencia_html,
        //     'tratamientos_activos' => $receta_activa_html,
        //     'confidencial' => '',
        //     'responsables' => $responsables_html,
        //     'estado' => 'ok'

        // );


        /* FIN --------------------------- HTML MODAL -------------------------- */

        $odontograma = $this->dameOdontogramaPaciente($paciente->id);

        $datos = (object)array(
            'paciente' => $paciente,
            'odontograma' => $odontograma,
            'contacto_emergencia' => $contacto_emergencia,
            'antecedentes_paciente' => $antecedentes_paciente,
            'grupo_sanguineo' => $grupo_sanguineo,
            'antecedentes' => $antecedentes,
            'token' => $request->token,
            'fichas' => $fichas,
            'especialidad' => $especialidad,
            'sub_tipo_especialidad' => $sub_tipo_especialidad,
            'direccion' => $direccion,
            'tratamiento_activo' => $regisrto_result,
            'examenes_especialidad_realizados' => $examenes_especialidad_realizados,
            'resultado_examen' => $resultado_examen,
            'control_enfer_cronicas' => $control_enfer_cronicas,

        );

        return response()->json([
                'estado' => 1,
                'paciente' => $paciente,
                'odontograma' => $odontograma,
                'contacto_emergencia' => $contacto_emergencia,
                'antecedentes_paciente' => $antecedentes_paciente,
                'grupo_sanguineo' => $grupo_sanguineo,
                'antecedentes' => $antecedentes,
                'token' => $request->token,
                'fichas' => $fichas,
                'especialidad' => $especialidad,
                'sub_tipo_especialidad' => $sub_tipo_especialidad,
                'direccion' => $direccion,
                'tratamiento_activo' => $regisrto_result,
                'examenes_especialidad_realizados' => $examenes_especialidad_realizados,
                'resultado_examen' => $resultado_examen,
                'control_enfer_cronicas' => $control_enfer_cronicas,
            ]);

    }

    public function getMisHorasMedicas(Request $request){

        // Validación de token X-Auth-Token
        $authToken = $request->header('X-Auth-Token');
        if (!$authToken) {
            return response()->json([
                'estado' => 0,
                'mensaje' => 'Token de autenticación requerido'
            ], 401);
        }

        // Buscar el token en la base de datos
        $token = PersonalAccessToken::findToken($authToken);
        if (!$token) {
            return response()->json([
                'estado' => 0,
                'mensaje' => 'Token de autenticación inválido'
            ], 401);
        }

        // Verificar que el token no haya expirado
        if ($token->expires_at && $token->expires_at->isPast()) {
            return response()->json([
                'estado' => 0,
                'mensaje' => 'Token de autenticación expirado'
            ], 401);
        }

        // Obtener el usuario autenticado del token
        $user = $token->tokenable;
        if (!$user) {
            return response()->json([
                'estado' => 0,
                'mensaje' => 'Usuario no encontrado'
            ], 401);
        }

        $id_usuario = $request->id_paciente;
        $paciente = Paciente::where('id', $id_usuario)->first();

        // return response()->json([
        //     'estado' => 1,
        //     'paciente' => $paciente
        // ]);
        if($paciente){
             $hora_medica = HoraMedica::select('horas_medicas.id', 'horas_medicas.fecha_consulta', 'horas_medicas.hora_inicio', 'horas_medicas.hora_termino', 'horas_medicas.alias_examen',
                                                'horas_medicas.comentarios_confirmacion', 'horas_medicas.fecha_confirmacion', 'horas_medicas.comentarios_cancelacion', 'horas_medicas.fecha_cancelacion',
                                                'horas_medicas.fecha_realizacion_consulta', 'horas_medicas.id_ficha_atencion', 'horas_medicas.id_profesional', 'horas_medicas.id_lugar_atencion',
                                                'horas_medicas.id_asistente', 'horas_medicas.id_paciente', 'horas_medicas.acomp_representante', 'horas_medicas.acomp_acompanante',
                                                'horas_medicas.acomp_lista', 'horas_medicas.autorizacion_atencion', 'horas_medicas.id_log_users_devices', 'horas_medicas.id_estado',
                                                'profesionales.nombre as nombre_profesional', 'profesionales.apellido_uno as apellido_uno_profesional','profesionales.foto_perfil',
                                                'lugares_atencion.nombre as nombre_lugar_atencion', 'direcciones.direccion as direccion_lugar_atencion', 'direcciones.numero_dir as numero_dir_lugar_atencion',
                                                'especialidades.nombre as nombre_especialidad', 'tipos_especialidad.nombre as nombre_tipo_especialidad', 'sub_tipo_especialidad.nombre as nombre_sub_tipo_especialidad',
                                                'parametros.valor as texto_estado', 'parametros.color as color_estado')
                                        ->join('profesionales', 'profesionales.id', '=', 'horas_medicas.id_profesional')
                                        ->join('especialidades', 'especialidades.id', '=', 'profesionales.id_especialidad')
                                        ->join('tipos_especialidad', 'tipos_especialidad.id', '=', 'profesionales.id_tipo_especialidad')
                                        ->leftJoin('sub_tipo_especialidad', 'sub_tipo_especialidad.id', '=', 'profesionales.id_sub_tipo_especialidad')
                                        ->join('lugares_atencion', 'lugares_atencion.id', '=', 'horas_medicas.id_lugar_atencion')
                                        ->join('direcciones', 'direcciones.id', '=', 'lugares_atencion.id_direccion')
                                        ->join('parametros', function ($join) {
                                            $join->on('parametros.id', '=', 'horas_medicas.id_estado')
                                                    ->where('parametros.referencia', '=', 'Agenda_Estado');
                                        })
                                        ->where('id_paciente', $paciente->id)
                                        // ->whereRaw("fecha_consulta >= NOW() AND hora_inicio >= NOW()")
                                        ->whereRaw("date(fecha_consulta) >= '".date('Y-m-d')."' ")
                                        // ->whereIn('id_estado',[1,2,4,5,6,8])
                                        ->orderBy('fecha_consulta', 'ASC')
                                        ->orderBy('hora_inicio', 'DESC')
                                        ->get();

            return response()->json([
                'estado' => 1,
                'horas' => $hora_medica
            ]);
        }else{
            return response()->json([
                'estado' => 0,
                'mensaje' => 'Paciente no encontrado',
                'horas' => []
            ]);
        }
    }

    public function getMisMascotas(Request $request)
    {
        // Validación de token X-Auth-Token
        $authToken = $request->header('X-Auth-Token');
        if (!$authToken) {
            return response()->json([
                'estado' => 0,
                'mensaje' => 'Token de autenticación requerido'
            ], 401);
        }

        // Buscar el token en la base de datos
        $token = PersonalAccessToken::findToken($authToken);
        if (!$token) {
            return response()->json([
                'estado' => 0,
                'mensaje' => 'Token de autenticación inválido'
            ], 401);
        }

        // Verificar que el token no haya expirado
        if ($token->expires_at && $token->expires_at->isPast()) {
            return response()->json([
                'estado' => 0,
                'mensaje' => 'Token de autenticación expirado'
            ], 401);
        }

        // Obtener el usuario autenticado del token
        $user = $token->tokenable;
        if (!$user) {
            return response()->json([
                'estado' => 0,
                'mensaje' => 'Usuario no encontrado'
            ], 401);
        }

        $idUsuario = $request->id_paciente ?: $user->id;
        $paciente = Paciente::where('id_usuario', $idUsuario)->first();
        if (!$paciente) {
            return response()->json([
                'estado' => 0,
                'mensaje' => 'Paciente no encontrado',
                'registros' => []
            ]);
        }

        $mascotas = Mascota::with(['especieMascota', 'tamanoMascota'])
            ->where('id_responsable', $paciente->id)
            ->get();

        return response()->json([
            'estado' => 1,
            'msj' => 'registros',
            'registros' => $mascotas,
        ]);
    }

    public function getDocumentosMascota(Request $request)
    {
        [$user, $authError] = $this->resolveAuthUser($request);
        if ($authError) {
            return $authError;
        }

        $idMascota = $request->id_mascota;
        if (empty($idMascota)) {
            return response()->json([
                'estado' => 0,
                'mensaje' => 'Mascota requerida',
                'documentos' => []
            ]);
        }

        $idUsuario = $request->id_paciente ?: $user->id;
        $paciente = Paciente::where('id_usuario', $idUsuario)->first();
        if (!$paciente) {
            return response()->json([
                'estado' => 0,
                'mensaje' => 'Paciente no encontrado',
                'documentos' => []
            ]);
        }

        $mascota = Mascota::where('id', $idMascota)
            ->where('id_responsable', $paciente->id)
            ->first();
        if (!$mascota) {
            return response()->json([
                'estado' => 0,
                'mensaje' => 'Mascota no encontrada',
                'documentos' => []
            ]);
        }

        $fichaIds = FichaAtencion::where('id_mascota', $mascota->id)
            ->where('id_paciente', $paciente->id)
            ->pluck('id');

        if ($fichaIds->isEmpty()) {
            return response()->json([
                'estado' => 1,
                'mensaje' => 'Sin documentos',
                'documentos' => []
            ]);
        }

        $consentimientos = ConConsentimientosPcte::with(['Consentimiento' => function ($query) {
                $query->select('id', 'nombre');
            }])
            ->whereIn('id_fc', $fichaIds)
            ->orderBy('id', 'DESC')
            ->get();

        $baseUrl = rtrim(config('app.url') ?: env('APP_URL', ''), '/');
        $documentos = [];

        foreach ($consentimientos as $consentimiento) {
            $nombre = $consentimiento->Consentimiento->nombre ?? 'Consentimiento informado';
            $fecha = $consentimiento->fecha_cons ?: ($consentimiento->created_at ? $consentimiento->created_at->format('Y-m-d') : null);
            $pdfUrl = $baseUrl ? $baseUrl . '/consentimiento/pdf?id_consentimiento=' . $consentimiento->id . '&id_ficha_atencion=' . $consentimiento->id_fc : null;

            $documentos[] = [
                'id' => $consentimiento->id,
                'tipo' => 'Consentimiento informado',
                'nombre' => $nombre,
                'fecha_emision' => $fecha,
                'id_ficha_atencion' => $consentimiento->id_fc,
                'estado' => $consentimiento->confirmacion,
                'pdf_url' => $pdfUrl,
            ];
        }

        $presupuestosOdontologicos = DocumentoFcPaciente::whereIn('id_ficha_atencion', $fichaIds)
            ->where('id_paciente', $paciente->id)
            ->where('otro', 'presupuesto_odontologico_veterinario')
            ->where('estado', 1)
            ->orderByDesc('updated_at')
            ->get();

        foreach ($presupuestosOdontologicos as $presupuesto) {
            $rutaDocumento = ltrim((string) $presupuesto->url, '/');
            $documentos[] = [
                'id' => $presupuesto->id,
                'tipo' => 'Presupuesto odontológico veterinario',
                'nombre' => $presupuesto->observacion ?: 'Presupuesto odontológico veterinario',
                'fecha_emision' => optional($presupuesto->updated_at)->format('Y-m-d'),
                'id_ficha_atencion' => $presupuesto->id_ficha_atencion,
                'estado' => $presupuesto->estado,
                'pdf_url' => $baseUrl && $rutaDocumento ? $baseUrl.'/'.$rutaDocumento : null,
            ];
        }

        usort($documentos, function ($a, $b) {
            return strcmp((string) ($b['fecha_emision'] ?? ''), (string) ($a['fecha_emision'] ?? ''));
        });

        return response()->json([
            'estado' => 1,
            'mensaje' => 'documentos',
            'documentos' => $documentos
        ]);
    }

    public function getSolicitudesPendientes(Request $request)
    {
        [$user, $authError] = $this->resolveAuthUser($request);
        if ($authError) {
            return $authError;
        }

        $registros = LogUsersDevices::where('id_user_recept', $user->id)
            ->where('estado', 0)
            ->orderBy('id', 'DESC')
            ->get();

        $pendientes = [];
        foreach ($registros as $registro) {
            $data = json_decode($registro->msg, false);
            $titulo = $data->nombre_consentimiento ?? $data->evento ?? $data->nombre ?? 'Solicitud de autorización';
            $profesional = $data->nombre_profesional ?? $data->profesional ?? '';
            $mensajePlano = trim('Tiene pendiente confirmar: '.$titulo.($profesional ? ', solicitado por '.$profesional : '').'.');
            $mensaje = e($mensajePlano);

            $pendientes[] = [
                'id' => $registro->id,
                'tipo' => $registro->tipo,
                'msg_estado' => $mensaje,
                'msg_html' => '<article class="log-card"><p class="msg_estado_log">'.$mensaje.'</p><div class="log-card-acciones"><button type="button" onclick="abrir_autorizacion_log('.(int) $registro->id.',1,this)">Revisar solicitud</button></div></article>',
                'token' => $registro->token,
                'created_at' => $registro->created_at,
            ];
        }

        return response()->json([
            'estado' => 1,
            'mensaje' => 'pendientes',
            'cantidad' => count($pendientes),
            'registros' => $pendientes,
        ]);
    }

    public function getDispositivoAutorizacion(Request $request)
    {
        [$user, $authError] = $this->resolveAuthUser($request);
        if ($authError) return $authError;

        $uuid = trim((string) $request->query('uuid'));
        if ($uuid === '') {
            return response()->json(['estado' => 0, 'mensaje' => 'UUID requerido', 'registros' => []], 422);
        }

        $device = UsersDevices::where('id_user', $user->id)->where('uuid', $uuid)->first();
        if (!$device) {
            return response()->json(['estado' => 0, 'mensaje' => 'Dispositivo no registrado', 'registros' => []]);
        }

        return response()->json([
            'estado' => 1,
            'registros' => [['id' => $device->id, 'estado' => (int) $device->estado]],
            'password' => $device->password,
        ]);
    }

    public function responderSolicitudPendiente(Request $request, int $id)
    {
        [$user, $authError] = $this->resolveAuthUser($request);
        if ($authError) return $authError;

        $estado = (int) $request->input('estado');
        if (!in_array($estado, [1, 2], true)) {
            return response()->json(['estado' => 0, 'msg' => 'Decisión no válida'], 422);
        }

        $uuid = trim((string) $request->input('uuid'));
        $credential = trim((string) $request->input('credential'));
        $device = UsersDevices::where('id_user', $user->id)->where('uuid', $uuid)->first();
        if (!$device || !hash_equals((string) $device->password, $credential)) {
            return response()->json(['estado' => 0, 'msg' => 'La clave del dispositivo no es correcta'], 422);
        }

        $registro = LogUsersDevices::whereKey($id)->where('id_user_recept', $user->id)->first();
        if (!$registro) return response()->json(['estado' => 0, 'msg' => 'Solicitud no encontrada'], 404);
        if ((int) $registro->estado !== 0) {
            return response()->json(['estado' => 2, 'msg' => 'Esta solicitud ya fue procesada'], 409);
        }

        $registro->estado = $estado;
        $registro->save();
        return response()->json(['estado' => 1, 'msg' => $estado === 1 ? 'Solicitud autorizada' : 'Solicitud rechazada']);
    }

    public function getDesafiosAcceso(Request $request)
    {
        [$user, $authError] = $this->resolveAuthUser($request);
        if ($authError) return $authError;

        $items = LogUsersDevices::where('id_user_recept', $user->id)
            ->where('estado', 0)->orderByDesc('id')->get()->map(function ($registro) {
                $data = json_decode($registro->msg, true) ?: [];
                return [
                    'id' => (string) $registro->id,
                    'title' => $data['evento'] ?? $data['nombre_consentimiento'] ?? $data['nombre'] ?? 'Solicitud de autorización',
                    'location' => $data['lugar'] ?? $data['lugar_atencion'] ?? 'Base veterinaria VeterChile',
                    'device' => $data['dispositivo'] ?? 'Solicitud de tutor o profesional',
                ];
            })->values();

        return response()->json($items);
    }

    public function responderDesafioAcceso(Request $request, int $id)
    {
        $request->merge([
            'estado' => $request->input('decision') === 'approved' ? 1 : 2,
            'credential' => $request->input('credential'),
            'uuid' => $request->input('uuid'),
        ]);
        return $this->responderSolicitudPendiente($request, $id);
    }

    public function getAgendaAppProfesional(Request $request)
    {
        [$user, $authError] = $this->resolveAuthUser($request);
        if ($authError) return $authError;

        $profesional = Profesional::where('id_usuario', $user->id)->first();
        if (!$profesional) {
            return response()->json(['estado' => 0, 'mensaje' => 'Perfil profesional no encontrado'], 403);
        }

        $fecha = $request->query('fecha', now()->toDateString());
        $lugarId = $request->query('id_lugar_atencion');
        $lugares = $profesional->LugaresAtencion()->select('lugares_atencion.id', 'lugares_atencion.nombre')->get();

        $query = HoraMedica::with([
                'Paciente:id,nombres,apellido_uno,apellido_dos',
                'Mascota.especieMascota:id,nombre',
                'LugarAtencion:id,nombre',
                'Estado:id,valor',
            ])
            ->where('id_profesional', $profesional->id)
            ->whereDate('fecha_consulta', $fecha);
        if ($lugarId) $query->where('id_lugar_atencion', $lugarId);

        $registros = $query->orderBy('hora_inicio')->get()->map(function ($hora) {
            $paciente = $hora->Paciente;
            return [
                'id' => $hora->id,
                'paciente' => $paciente ? trim($paciente->nombres.' '.$paciente->apellido_uno.' '.$paciente->apellido_dos) : 'Paciente no informado',
                'mascota' => $hora->Mascota?->nombre ?? 'Mascota no asociada',
                'especie' => $hora->Mascota?->especieMascota?->nombre
                    ?? $hora->Mascota?->especie
                    ?? 'Especie no informada',
                'fecha' => $hora->fecha_consulta,
                'hora_inicio' => $hora->hora_inicio,
                'hora_termino' => $hora->hora_termino,
                'lugar' => $hora->LugarAtencion?->nombre ?? 'Lugar no informado',
                'estado' => $hora->Estado?->valor ?? 'Pendiente',
                'es_telemedicina' => false,
                'puede_conectarse' => false,
                'video_url' => null,
            ];
        });

        return response()->json(['estado' => 1, 'lugares' => $lugares, 'registros' => $registros]);
    }

    public function getPacientesAppProfesional(Request $request)
    {
        [$user, $authError] = $this->resolveAuthUser($request);
        if ($authError) return $authError;

        $profesional = Profesional::where('id_usuario', $user->id)->first();
        if (!$profesional) {
            return response()->json(['estado' => 0, 'mensaje' => 'Perfil profesional no encontrado'], 403);
        }

        $buscar = trim((string) $request->query('buscar', ''));
        $porPagina = min(max((int) $request->query('por_pagina', 20), 1), 50);
        $query = Paciente::query()
            ->selectRaw("pacientes.id, pacientes.rut, pacientes.nombres, pacientes.apellido_uno, pacientes.apellido_dos, pacientes.telefono_uno, pacientes.email, MAX(horas_medicas.fecha_consulta) ultima_atencion, COUNT(DISTINCT horas_medicas.id) total_atenciones")
            ->join('horas_medicas', 'horas_medicas.id_paciente', '=', 'pacientes.id')
            ->where('horas_medicas.id_profesional', $profesional->id)
            ->groupBy('pacientes.id', 'pacientes.rut', 'pacientes.nombres', 'pacientes.apellido_uno', 'pacientes.apellido_dos', 'pacientes.telefono_uno', 'pacientes.email');
        if ($buscar !== '') {
            $query->where(function ($q) use ($buscar) {
                $q->where('pacientes.rut', 'like', '%'.$buscar.'%')
                    ->orWhere('pacientes.nombres', 'like', '%'.$buscar.'%')
                    ->orWhere('pacientes.apellido_uno', 'like', '%'.$buscar.'%')
                    ->orWhere('pacientes.email', 'like', '%'.$buscar.'%');
            });
        }

        $pagina = $query->orderByDesc('ultima_atencion')->paginate($porPagina, ['*'], 'page', max((int) $request->query('pagina', 1), 1));
        $registros = collect($pagina->items())->map(fn ($paciente) => [
            'id' => $paciente->id,
            'nombre' => trim($paciente->nombres.' '.$paciente->apellido_uno.' '.$paciente->apellido_dos),
            'rut' => $paciente->rut,
            'telefono' => $paciente->telefono_uno,
            'email' => $paciente->email,
            'ultima_atencion' => $paciente->ultima_atencion,
            'total_atenciones' => (int) $paciente->total_atenciones,
        ])->values();

        return response()->json([
            'estado' => 1,
            'registros' => $registros,
            'paginacion' => [
                'total' => $pagina->total(),
                'pagina_actual' => $pagina->currentPage(),
                'ultima_pagina' => $pagina->lastPage(),
            ],
        ]);
    }

    public function getUltimasVisitasPacienteAppProfesional(Request $request, int $idPaciente)
    {
        [$user, $authError] = $this->resolveAuthUser($request);
        if ($authError) return $authError;

        $profesional = Profesional::where('id_usuario', $user->id)->first();
        if (!$profesional) {
            return response()->json(['estado' => 0, 'mensaje' => 'Perfil profesional no encontrado'], 403);
        }

        $paciente = Paciente::find($idPaciente);
        $pacienteAtendido = $paciente && HoraMedica::where('id_paciente', $paciente->id)
            ->where('id_profesional', $profesional->id)
            ->exists();

        if (!$pacienteAtendido) {
            return response()->json(['estado' => 0, 'mensaje' => 'Paciente no asociado a sus atenciones'], 404);
        }

        $visitas = HoraMedica::with([
                'Mascota:id,nombre',
                'LugarAtencion:id,nombre',
                'Estado:id,valor',
            ])
            ->where('id_paciente', $paciente->id)
            ->where('id_profesional', $profesional->id)
            ->whereNotNull('fecha_realizacion_consulta')
            ->whereDate('fecha_consulta', '<=', now()->toDateString())
            ->orderByDesc('fecha_consulta')
            ->orderByDesc('hora_inicio')
            ->limit(10)
            ->get()
            ->map(fn ($hora) => [
                'id' => $hora->id,
                'fecha' => $hora->fecha_consulta,
                'hora_inicio' => $hora->hora_inicio,
                'hora_termino' => $hora->hora_termino,
                'mascota' => $hora->Mascota?->nombre ?? 'Sin mascota asociada',
                'lugar' => $hora->LugarAtencion?->nombre ?? 'Lugar no informado',
                'motivo' => $hora->alias_examen ?: $hora->descripcion ?: 'Atención veterinaria',
                'estado' => $hora->Estado?->valor ?? 'Realizada',
            ])
            ->values();

        return response()->json([
            'estado' => 1,
            'paciente' => [
                'id' => $paciente->id,
                'nombre' => trim($paciente->nombres.' '.$paciente->apellido_uno.' '.$paciente->apellido_dos),
            ],
            'registros' => $visitas,
        ]);
    }

    protected function resolveAuthUser(Request $request)
    {
        $authToken = $request->header('X-Auth-Token');
        if (!$authToken) {
            return [null, response()->json([
                'estado' => 0,
                'mensaje' => 'Token de autenticación requerido'
            ], 401)];
        }

        $token = PersonalAccessToken::findToken($authToken);
        if (!$token) {
            return [null, response()->json([
                'estado' => 0,
                'mensaje' => 'Token de autenticación inválido'
            ], 401)];
        }

        if ($token->expires_at && $token->expires_at->isPast()) {
            return [null, response()->json([
                'estado' => 0,
                'mensaje' => 'Token de autenticación expirado'
            ], 401)];
        }

        $user = $token->tokenable;
        if (!$user) {
            return [null, response()->json([
                'estado' => 0,
                'mensaje' => 'Usuario no encontrado'
            ], 401)];
        }

        return [$user, null];
    }

    public function guardarAtencionMedica(Request $request){
    // Validación de token X-Auth-Token
        $authToken = $request->header('X-Auth-Token');
        if (!$authToken) {
            return response()->json([
                'estado' => 0,
                'mensaje' => 'Token de autenticación requerido'
            ], 401);
        }

        // Buscar el token en la base de datos
        $token = PersonalAccessToken::findToken($authToken);
        if (!$token) {
            return response()->json([
                'estado' => 0,
                'mensaje' => 'Token de autenticación inválido'
            ], 401);
        }

        // Verificar que el token no haya expirado
        if ($token->expires_at && $token->expires_at->isPast()) {
            return response()->json([
                'estado' => 0,
                'mensaje' => 'Token de autenticación expirado'
            ], 401);
        }

        // Obtener el usuario autenticado del token
        $user = $token->tokenable;
        if (!$user) {
            return response()->json([
                'estado' => 0,
                'mensaje' => 'Usuario no encontrado'
            ], 401);
        }
        $id_ficha_atencion = $request->id_ficha_atencion;
        $id_paciente = $request->id_paciente;
        $id_profesional = $request->id_profesional;
        $observaciones = $request->observaciones;

        $ficha = FichaAtencion::where('id', $id_ficha_atencion)
                                ->where('id_paciente', $id_paciente)
                                ->where('id_profesional', $id_profesional)
                                ->first();
        if($ficha){
            $ficha->observaciones_paciente = $observaciones;
            $ficha->save();

            return response()->json([
                'estado' => 1,
                'mensaje' => 'Atención médica guardada correctamente',
                'ficha' => $ficha
            ]);
        }else{
            return response()->json([
                'estado' => 0,
                'mensaje' => 'Ficha de atención no encontrada',
                'ficha' => null
            ]);
        }
    }

    public function getEspecialidadesProfesionales(Request $request){
        $especialidades = Especialidad::where('estado',1)->get();
        return response()->json([
            'estado' => 1,
            'especialidades' => $especialidades
        ]);
    }

    public function getTipoEspecialidadesProfesionales(Request $request){
        $id = $request->id_especialidad;
        $tipo_especialidades = TipoEspecialidad::where('estado',1)
                                ->where('id_especialidad',$id)
                                ->get();
        return response()->json([
            'estado' => 1,
            'tipo_especialidades' => $tipo_especialidades
        ]);
    }

    public function getSubTipoEspecialidadesProfesionales(Request $request){
        $id = $request->id_tipo_especialidad;
        $sub_tipo_especialidades = SubTipoEspecialidad::where('estado',1)
                                ->where('id_tipo_especialidad',$id)
                                ->get();
        return response()->json([
            'estado' => 1,
            'sub_tipo_especialidades' => $sub_tipo_especialidades
        ]);
    }

      public function dameOdontogramaPaciente($id_paciente, $id_ficha_atencion = null, $id_lugar_atencion = null,$id_presupuesto = null){
        $query = OdontogramaPaciente::select(
            'odontogramas_pacientes.*',
            'diagnosticos_dental.descripcion',
            'diagnosticos_dental.cantidad_bloques',
            'diagnosticos_dental.valor',
            'tratamientos_dental.descripcion as diagnostico')
            ->join('diagnosticos_dental', 'odontogramas_pacientes.tratamiento', '=', 'diagnosticos_dental.descripcion')
            ->join('tratamientos_dental', 'odontogramas_pacientes.diagnostico', '=', 'tratamientos_dental.id')
            ->where('odontogramas_pacientes.id_paciente', $id_paciente);
            // ->where('odontogramas_pacientes.id_ficha_atencion', $id_ficha_atencion)
            // ->where('odontogramas_pacientes.id_lugar_atencion', $id_lugar_atencion);

            // Verificar si el parámetro $id_presupuesto no es nulo
            if (!is_null($id_presupuesto)) {
                $query->where('odontogramas_pacientes.id_presupuesto', $id_presupuesto);
            }

            // Obtener los resultados
            $odontogramas = $query->get();

            return $odontogramas;
    }

    public function getMisProfesionales(Request $request){

        // Validación de token X-Auth-Token
        $authToken = $request->header('X-Auth-Token');
        if (!$authToken) {
            return response()->json([
                'estado' => 0,
                'mensaje' => 'Token de autenticación requerido'
            ], 401);
        }

        // Buscar el token en la base de datos
        $token = PersonalAccessToken::findToken($authToken);
        if (!$token) {
            return response()->json([
                'estado' => 0,
                'mensaje' => 'Token de autenticación inválido'
            ], 401);
        }

        // Verificar que el token no haya expirado
        if ($token->expires_at && $token->expires_at->isPast()) {
            return response()->json([
                'estado' => 0,
                'mensaje' => 'Token de autenticación expirado'
            ], 401);
        }

        // Obtener el usuario autenticado del token
        $user = $token->tokenable;
        if (!$user) {
            return response()->json([
                'estado' => 0,
                'mensaje' => 'Usuario no encontrado'
            ], 401);
        }

        // VER lista de profesionales
        //capturamos el id_usuario receptor
        $id_usuario = $request->id_paciente;
        $paciente = Paciente::where('id_usuario', $id_usuario)->first();
        $fichas = FichaAtencion::where('id_paciente', $paciente->id)->get()->unique('id_profesional');

        $fichas_desvinculados = FichaAtencion::select('id_profesional')
                                        ->where('id_paciente', $paciente->id)
                                        ->where('desvincular', 1)
                                        ->get()
                                        ->unique('id_profesional');

        $profesional = [];
        $desvinculados = [];
        $profesion = [];

        foreach ($fichas as $f) {

            $profesional_ = Profesional::with('Especialidad')->find($f->id_profesional);
            if($profesional_){
                $profesion[$profesional_->Especialidad->id] = $profesional_->Especialidad->nombre;

                // busqueda de imagen
                $array_rut = explode('-',$profesional_->rut);
                $nombre_imagen = asset('images/iconos/usuario_profesional.svg');

                if(file_exists(public_path('images/img_perfil/'.$array_rut[0].'.png')))
                {
                    $nombre_imagen = asset('images/img_perfil/'.$array_rut[0].'.png');
                    $profesional_temp['img_profesional2'] = $nombre_imagen;
                }
                $profesional_temp = $f->profesional()->first();
                $profesional_temp['img_profesional'] = $nombre_imagen;
                $profesional_temp['especialidad'] = $profesional_->Especialidad->nombre;
                $profesional_temp['tipo_especialidad'] = $profesional_->TipoEspecialidad ? $profesional_->TipoEspecialidad->nombre : '';
                if($profesional_->SubTipoEspecialidad){
                    $profesional_temp['sub_tipo_especialidad'] = $profesional_->SubTipoEspecialidad->nombre;
                }

                array_push($profesional, $profesional_temp);
            }

        }

        foreach ($fichas_desvinculados as $d) {
            array_push($desvinculados, $d->id_profesional);
        }


        //$lista_especialidad = array_unique($profesion);
        $lista_especialidad = $profesion;

        // var_dump(Auth::user()->id);
        // echo json_encode($profesional);
        // die();
        return response()->json([
            'estado' => 1,
            'medicos' => $profesional,
            'desvinculados' => $desvinculados,
            'lista_especialidad' => $lista_especialidad,
            'id_usuario' => $id_usuario,
        ]);
    }

    public function getLugaresAtencionProfesional(Request $request){
        $id_profesional = $request->id_profesional;
        $datos = array();

        $profesional = Profesional::where('id', $id_profesional)->first();
        if (!$profesional) {
            return response()->json(['estado' => 0, 'msj' => 'Profesional no encontrado'], 404);
        }

        $lugares_atencion = $profesional->LugaresAtencion()->where('estado',1)->get();
        foreach ($lugares_atencion as $key => $value) {
            $direccion = Direccion::where('id', $value->id_direccion)->first();
            $lugares_atencion[$key]['direccion'] = $direccion
                ? trim(($direccion->direccion ?? '').' '.($direccion->numero_dir ?? ''))
                : '';
            $lugares_atencion[$key]['ciudad'] = $direccion?->Ciudad?->nombre ?? '';
        }
        if($lugares_atencion)
        {
            $datos['estado'] = 1;
            $datos['msj'] = 'registros';
            $datos['registros'] = $lugares_atencion;

        }
        else
        {
            $datos['estado'] = 0;
            $datos['msj'] = 'sin registros';
        }

        return response()->json($datos);
    }

    public function getTiposAgendaLugarAtencion(Request $request)
    {
        $tipos = ProfesionalHorario::query()
            ->where('id_profesional', $request->id_profesional)
            ->where('id_lugar_atencion', $request->id_lugar)
            ->whereIn('tipo_agenda', [1, 2, 3, 4])
            ->distinct()
            ->orderBy('tipo_agenda')
            ->pluck('tipo_agenda');

        $modalidades = [
            1 => ['nombre' => 'Consulta presencial', 'descripcion' => 'Atención veterinaria en el centro', 'tipo_hora_medica' => 'C'],
            2 => ['nombre' => 'Consulta odontológica', 'descripcion' => 'Atención dental veterinaria', 'tipo_hora_medica' => 'D'],
            3 => ['nombre' => 'Telemedicina', 'descripcion' => 'Atención veterinaria por videollamada', 'tipo_hora_medica' => 'T'],
            4 => ['nombre' => 'Exámenes y procedimientos', 'descripcion' => 'Prestaciones configuradas por el centro', 'tipo_hora_medica' => 'E'],
        ];

        $registros = $tipos->map(function ($tipo) use ($modalidades) {
            $tipo = (int) $tipo;
            return array_merge(['id' => $tipo], $modalidades[$tipo]);
        })->values();

        return response()->json([
            'estado' => 1,
            'msj' => $registros->isEmpty() ? 'sin registros' : 'registros',
            'registros' => $registros,
        ]);
    }

    public function getDiasLaboralesLugarAtencionProfesional(Request $request){
        $id_profesional = $request->id_profesional;
        $id_lugar_atencion = $request->id_lugar;
        $id_tipo_agenda = $request->tipo_agenda; // 1=Agenda normal, 2=Agenda horas bloqueadas, 3=Agenda horas especiales
        $datos = array();
        $horario = ProfesionalHorario::where('id_profesional', $id_profesional)
                                        ->where('id_lugar_atencion', $id_lugar_atencion)
                                        ->tipoAgenda($id_tipo_agenda)
                                        ->orderBy('dia', 'ASC')
                                        ->get();

        $dias_no_laborales = ['1','2','3','4','5','6','7'];
        $dias_laborales = [];

        foreach ($horario as $hor) {
            $ho = explode(',', $hor->dia);
            foreach ($ho as $h) {
                $h = trim($h);
                // Agregar a días laborales si no está ya
                if (!in_array($h, $dias_laborales)) {
                    $dias_laborales[] = $h;
                }

                // Quitar de los días no laborales si está presente
                if (($key = array_search($h, $dias_no_laborales)) !== false) {
                    unset($dias_no_laborales[$key]);
                }
            }
        }

        // Ordenar los arrays si quieres mantener un orden consistente
        sort($dias_laborales);
        sort($dias_no_laborales);

        $horario_agenda_laboral = implode(',', $dias_laborales);
        $horario_agenda_no_laboral = implode(',', $dias_no_laborales);

        $datos['estado'] = 1;
        $datos['msj'] = 'registros';
        $datos['registros'] = array(
            'horario_agenda_laboral' => $horario_agenda_laboral,
            'horario_agenda_no_laboral' => $horario_agenda_no_laboral
        );

        return response()->json($datos);
    }

    public function getHorasDisponiblesProfesionalLugarAtencionBuscador(Request $request){
        $id_profesional = $request->id_profesional;
        $id_lugar_atencion = $request->id_lugar;
        $tipo_agenda = $request->tipo_agenda; // 1=Agenda normal
        $texto_dia = ['Domingo', 'Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sabado'];
        $texto_mes = ['', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];

        $datos = array();

        $dia_semana = date('N',strtotime($request->fecha));

        $profesional_horarios = ProfesionalHorario::where('id_profesional',$id_profesional)
                                                ->where('id_lugar_atencion',$id_lugar_atencion)
                                                ->where('dia','like','%'.$dia_semana.'%')
                                                ->tipoAgenda($tipo_agenda)
                                                ->orderBy('dia', 'ASC')
                                                ->first();
        if($profesional_horarios)
        {
            $array_bloques = array();

            $hora_inicio_turno  = $request->fecha.' '.$profesional_horarios->hora_inicio;
            $hora_termino_turno  = $request->fecha.' '.$profesional_horarios->hora_termino;

            /** duracion de consulta en minutos */
            $duracion =  $profesional_horarios->duracion_consulta;
            $array_duracion = explode(':', $duracion);
            $duracion_total = 0;

            if((int)$array_duracion[0]>0)
                $duracion_total += (int)$array_duracion[0]*60;
            if((int)$array_duracion[1]>0)
                $duracion_total += (int)$array_duracion[1];

            for ($hora = strtotime($hora_inicio_turno);
                 strtotime('+'.$duracion_total.' minute', $hora) <= strtotime($hora_termino_turno);
                 $hora = strtotime('+'.$duracion_total.' minute', $hora))
            {
                $inicioBloque = date('H:i:s', $hora);
                $terminoBloque = date('H:i:s', strtotime('+'.$duracion_total.' minute', $hora));

                $hora_medica = HoraMedica::where('fecha_consulta', date('Y-m-d',$hora))
                                            ->where('id_profesional', $id_profesional)
                                            ->where('id_lugar_atencion', $id_lugar_atencion)
                                            ->whereIn('id_estado', [1, 2])
                                            ->where('hora_inicio', '<', $terminoBloque)
                                            ->where('hora_termino', '>', $inicioBloque)
                                            ->first();

                $datos['hora'][strtotime( '+1 second', $hora)] = date('H:i',strtotime( '+1 second', $hora));
                $datos['hora_medica'][strtotime( '+1 second', $hora)] = $hora_medica;

                if($hora_medica)
                {
                    // con reserva
                }
                else
                {
                    // sin reserva
                    $array_bloques[] = array(
                                            'dia' => date('Y-m-d',$hora),
                                            'hora' => date('H:i',$hora),
                                            'fecha_hora' => date('Y-m-d H:i',$hora)
                                        );
                }
            }

            $datos['estado'] = 1;
            $datos['msj'] = 'registros';
            $datos['horarios'] = $array_bloques;
            $datos['text_fecha'] = $texto_dia[(int)$dia_semana].' '. date('d',strtotime($request->fecha)).' '.$texto_mes[(int)date('m',strtotime($request->fecha))].' '.date('Y',strtotime($request->fecha));

        }
        else
        {
            $datos['estado'] = 0;
            $datos['msj'] = 'sin registros';
            $datos['profesional_horario'] = $profesional_horarios;
        }

        return response()->json($datos);
    }

    public function agendarHoraMedica(Request $request){

        // Validación de token X-Auth-Token
        $authToken = $request->header('X-Auth-Token');
        if (!$authToken) {
            return response()->json([
                'estado' => 0,
                'mensaje' => 'Token de autenticación requerido'
            ], 401);
        }

        // Buscar el token en la base de datos
        $token = PersonalAccessToken::findToken($authToken);
        if (!$token) {
            return response()->json([
                'estado' => 0,
                'mensaje' => 'Token de autenticación inválido'
            ], 401);
        }

        // Verificar que el token no haya expirado
        if ($token->expires_at && $token->expires_at->isPast()) {
            return response()->json([
                'estado' => 0,
                'mensaje' => 'Token de autenticación expirado'
            ], 401);
        }

        // Obtener el usuario autenticado del token
        $user = $token->tokenable;
        if (!$user) {
            return response()->json([
                'estado' => 0,
                'mensaje' => 'Usuario no encontrado'
            ], 401);
        }
        // return response()->json([
        //     'estado' => 0,
        //     'mensaje' => 'Funcionalidad temporalmente deshabilitada',
        //     'registros' => $request->all()
        // ], 200);

        $datos = array();
        $valido = 1;

        $paciente = Paciente::where('id_usuario', $user->id)->first();
        if (!$paciente) {
            return response()->json([
                'estado' => 0,
                'msj' => 'No se encontró el perfil tutor de la cuenta',
            ], 422);
        }

        $mascota = Mascota::whereKey($request->integer('id_mascota'))
            ->where('id_responsable', $paciente->id)
            ->where('estado', 1)
            ->vivas()
            ->first();
        if (!$mascota) {
            return response()->json([
                'estado' => 0,
                'msj' => 'Debe seleccionar una mascota registrada a su nombre',
            ], 422);
        }

        $profesional = Profesional::where('id', $request->id_profesional)->first();
        $lugar_atencion = LugarAtencion::where('id', $request->id_lugar)->first();
        if (!$profesional || !$lugar_atencion) {
            return response()->json([
                'estado' => 0,
                'msj' => 'El profesional o lugar de atención seleccionado no es válido',
            ], 422);
        }

        $texto_alias_examen = '';
        # TIPO HORA MEDICA
        switch ($request->tipo_hora_medica) {
            case 'C': // 1
                $texto_alias_examen = 'Consulta';
                break;
            case 'D': // 2
                $texto_alias_examen = 'Consulta Dental';
                break;
            case 'T': // 3
                $texto_alias_examen = 'Consulta Telemedicina';
                break;
            case 'E': // 4
                // $texto_alias_examen = 'Consulta Examen';
                $texto_alias_examen = $request->examen;
                break;
        }

        $validar = HoraMedica::where('id_paciente', $paciente->id)
                            ->where('id_mascota', $mascota->id)
                            ->where('id_profesional',$profesional->id)
                            ->where('tipo_hora_medica',$request->tipo_hora_medica)
                            ->where('fecha_consulta',\Carbon\Carbon::parse($request->fecha)->format('Y-m-d'))
                            ->whereIn('id_estado', [1, 2])
                            ->first();

        if($validar)
        {
            $datos['estado'] = 0;
            $datos['msj'] = 'Paciente ya tiene Hora para este dia';
            $valido = 0;
            return json_encode(array(
                'estado' => 'error',
                'id_profesional' => $profesional->id,
                'msj' => $mascota->nombre . ' ya tiene una hora con este profesional para ese día'
            ));
        }

        if($valido)
        {
            $tiempo_consulta = 15;
            $procedimiento = '';

            if($profesional->id_especialidad == 4 && $profesional->id_tipo_especialidad == 55)
            {
                $procedimiento = $request->procedimiento;
                $proc_bloque = ( !empty($request->proc_bloque)?intval($request->proc_bloque):1 );
                $tiempo_consulta = intval($proc_bloque) * 15;
            }else if($profesional->id_especialidad == 2){
                // $procedimiento = $request->procedimiento;
                // $proc_bloque = ( !empty($request->proc_bloque)?intval($request->proc_bloque):1 );
                // $tiempo_consulta = intval($proc_bloque) * 15;
                $procedimiento = $request->procedimiento;
                $proc_bloque = (!empty($request->proc_bloque) ? intval($request->proc_bloque) : 1);
                $tiempo_consulta = intval($proc_bloque) * 15;

                // Calcular hora de inicio y término
                $hora_inicio = \Carbon\Carbon::parse($request->fecha)->format('H:i:s');
                $hora_termino = \Carbon\Carbon::parse($request->fecha)->addMinutes($tiempo_consulta)->format('H:i:s');

                // Validar si ya hay una hora médica que se solape
                $choque_horario = HoraMedica::where('id_profesional', $profesional->id)
                    ->where('id_lugar_atencion', $request->id_lugar)
                    ->where('fecha_consulta', \Carbon\Carbon::parse($request->fecha)->format('Y-m-d'))
                    ->where(function ($query) use ($hora_inicio, $hora_termino) {
                        $query->whereBetween('hora_inicio', [$hora_inicio, $hora_termino])
                            ->orWhereBetween('hora_termino', [$hora_inicio, $hora_termino])
                            ->orWhere(function ($query) use ($hora_inicio, $hora_termino) {
                                $query->where('hora_inicio', '<=', $hora_inicio)
                                        ->where('hora_termino', '>=', $hora_termino);
                            });
                    })
                    ->exists();

                if ($choque_horario) {
                    return json_encode([
                        'estado' => 'error',
                        'id_profesional' => $profesional->id,
                        'msj' => 'La hora médica se superpone con otra ya registrada.<br> Eliga un bloque mayor'
                    ]);
                }
            }
            else
            {
                /** buscar tiempo de la consult */
                $dia_de_semana = \Carbon\Carbon::parse($request->fecha)->format('w');
                $profesional_horarios = ProfesionalHorario::select('duracion_consulta')
                                                            ->where('id_profesional', $profesional->id)
                                                            ->where('id_lugar_atencion',$request->id_lugar)
                                                            ->where('dia','like','%'.$dia_de_semana.'%')
                                                            ->first();

                // $profesional_horarios = '00:30:00';
                // $tiempo_consulta = 30;
                $horas = date('H',strtotime($profesional_horarios->duracion_consulta));
                $minutos = date('i',strtotime($profesional_horarios->duracion_consulta));
                $totales = ($horas*60) + $minutos;
                $tiempo_consulta = $totales;
            }

            $fechaReserva = \Carbon\Carbon::parse($request->fecha)->format('Y-m-d');
            $inicioReserva = \Carbon\Carbon::parse($fechaReserva.' '.$request->hora);
            $terminoReserva = $inicioReserva->copy()->addMinutes($tiempo_consulta);

            $horaOcupada = HoraMedica::where('id_profesional', $profesional->id)
                ->where('id_lugar_atencion', $request->id_lugar)
                ->where('fecha_consulta', $fechaReserva)
                ->whereIn('id_estado', [1, 2])
                ->where('hora_inicio', '<', $terminoReserva->format('H:i:s'))
                ->where('hora_termino', '>', $inicioReserva->format('H:i:s'))
                ->exists();

            if ($horaOcupada) {
                return response()->json([
                    'estado' => 0,
                    'msj' => 'La hora seleccionada ya no está disponible. Elija otro horario.',
                ], 409);
            }

            $hora_medica = new HoraMedica();

            $hora_medica->id_paciente = $paciente->id;
            $hora_medica->id_mascota = $mascota->id;
            $hora_medica->id_profesional = $profesional->id;
            $hora_medica->id_asistente = $request->id_asistente;
            $hora_medica->id_estado = '1';
            $hora_medica->fecha_consulta = \Carbon\Carbon::parse($request->fecha)->format('Y-m-d');

            $hora_medica->hora_inicio = \Carbon\Carbon::parse($request->hora)->format('H:i:s');
            $hora_medica->hora_termino = \Carbon\Carbon::parse($request->hora)->addMinutes($tiempo_consulta)->format('H:i:s');

            $hora_medica->tipo_hora_medica = $request->tipo_hora_medica;
            $hora_medica->alias_examen = $texto_alias_examen;
            $hora_medica->id_procedimiento = $request->id_procedimiento;

            $hora_medica->descripcion = 'Mascota: ' . $mascota->nombre . ' · Tutor: ' .
                trim($paciente->nombres . ' ' . $paciente->apellido_uno . ' ' . $paciente->apellido_dos);
            $hora_medica->id_lugar_atencion = $request->id_lugar;

            $hora_medica->acomp_representante = $request->representante;
            $hora_medica->acomp_acompanante = $request->acompanante;
            if(!empty($request->lista_Acompanante))
            $hora_medica->acomp_lista = json_encode($request->lista_Acompanante);

            $hora_medica->autorizacion_atencion = $request->autorizacion_atencion;
            // $hora_medica->id_log_users_devices = '';

            // $hora_medica->origen = $request->origen;

            if ($hora_medica->save())
            {
                if($request->tipo_hora_medica == 'T')
                {
                    $jitsi = JitsiController::jitsiRegistroMeet( $profesional->id, $paciente->id, $hora_medica->id );
					$hora_medica->video_llamada = $jitsi;
                }

                $datos['estado'] = 1;
                $datos['msj'] = 'Hora Reservada';
                $datos['registro'] = array(
                    'fecha' => \Carbon\Carbon::parse($request->fecha)->format('Y-m-d'),
                    'hora' => \Carbon\Carbon::parse($request->fecha)->format('H:i:s'),
                    'mascota' => $mascota->nombre,
                    'id_mascota' => $mascota->id,
                    'profesional' => $profesional->nombre . ' ' . $profesional->apellido_uno . ' ' . $profesional->apellido_dos ,
                    'lugar_atencion' => $lugar_atencion->nombre,
                );

                /**  */
                /** menor edad? */
                $edad = \Carbon\Carbon::parse($paciente->fecha_nac)->diff(\Carbon\Carbon::now())->format('%y');
                if( $edad < 18 )
                {

                    if( $request->autorizacion_atencion == 1 )
                    {
                        $usuario = Auth::user()->id;
                        $id_user_create = $usuario;
                        $id_user_recept = $usuario;
                        $evento = 'Autorizacion Atencion a Menor de Edad';
                        $nombre = $paciente->nombre;
                        $apellido_p = $paciente->apellido_uno;
                        $apellido_m = $paciente->apellido_dos;
                        $lugar = $lugar_atencion->nombre;
                        $profesional_log = $profesional->nombre.' '.$profesional->apellido_uno.' '.$profesional->apellido_dos;
                        $tipo = 'Autorizacion Atencion a Menor de Edad';
                        $tipo_id = '15';

                        // $log_users_devices = new LogUsersDevices();
                        $funcion = new Funciones();
                        $log_users_devices = (object) $funcion->generatePermApp($id_user_create,$id_user_recept,$evento,$nombre,$apellido_p,$apellido_m,$lugar,$profesional_log,$tipo,$tipo_id);

                        $datos['log_users_devices'] = $log_users_devices;

                        if($log_users_devices->app['estado'] == 1)
                        {
                            $hora_medica->autorizacion_atencion = $request->autorizacion_atencion;
                            $hora_medica->id_log_users_devices = $log_users_devices->app['last_id'];
                            if($hora_medica->save())
                            {
                                $datos['hora_medica_update']['estado'] = 1;
                                $datos['hora_medica_update']['msj'] = 'autorizacion';
                            }
                        }
                    }
                }
            }
        }

        // nombre_paciente
        // fecha
        // hora
        // profesional_nombre
        // profesional_especialidad
        // profesional_tipo_especialidad
        // profesional_sub_tipo_especialidad
        // profesional_sub_tipo_especialidad
        // lugar_atencion
        // direccion

        /** envio de correo de confirmacion INSTITUCION */
        $blade = 'hora_agendada';
        $to = array(
                array('email' => $paciente->email,'name' =>  $paciente->nombres . ' ' . $paciente->apellido_uno . ' ' . $paciente->apellido_dos),
            );
        $cc = array();
        $bcc = array();
        $asunto = 'VET-SDI - Nueva Hora Agendada';
        $body = array(
            'nombre_paciente'=> $paciente->nombres . ' ' . $paciente->apellido_uno . ' ' . $paciente->apellido_dos,
            'fecha'=> $hora_medica->fecha_consulta,
            'hora'=> $hora_medica->hora_inicio,
            'profesional_nombre'=> $profesional->nombre . ' ' . $profesional->apellido_uno . ' ' . $profesional->apellido_dos,
            'profesional_especialidad'=> $profesional->Especialidad()->first()->nombre,
            'profesional_tipo_especialidad'=> $profesional->TipoEspecialidad()->first()->nombre,
            'profesional_sub_tipo_especialidad' => optional($profesional->SubTipoEspecialidad()->first())->nombre ?? null, // Si no existe, no se muestra
            // 'institucion'=> $nombre_institucion,
            'lugar_atencion'=> $lugar_atencion->nombre,
            'direccion'=> $lugar_atencion->Direccion()->first()->direccion.' '.$lugar_atencion->Direccion()->first()->numero_dir.', '.$lugar_atencion->Direccion()->first()->Ciudad()->first()->nombre,
        );
        $archivo = '';/** pendiente */
        $id_institucion = '';

        $result_mail =  SendMailController::envioCorreo($blade, $to, $cc, $bcc, $asunto, $body, $archivo, $id_institucion);

        if($result_mail['estado'])
        {
            $datos['mail']['institucion']['estado'] = 1;
            $datos['mail']['institucion']['msj'] = 'Notificacion de bienvenida enviado';
        }
        else
        {
            $datos['mail']['institucion']['estado'] = 0;
            $datos['mail']['institucion']['msj'] = 'Falle en envio de Notificacion de bienvenida';
        }

        // $details = [
        //     'title' => 'Hora medica Reservada',
        //     'body' => 'Estimado/a ' . $paciente->nombres . ' ' . $paciente->apellido_uno . ' ' . $paciente->apellido_dos . ',<br>
        //             Junto con saludar, por medio de este correo le informamos que se ha reservado su hora con exito <br>' .
        //         'Fecha: ' . $hora_medica->fecha_consulta . '<br>' .
        //         'Hora : ' . $hora_medica->hora_inicio . '<br>' .
        //         'Profesional: <b>' . $profesional->nombre . ' ' . $profesional->apellido_uno . ' ' . $profesional->apellido_dos . '<b> <br><br>' .
        //         'Que tenga un excelente día. </br></br>' .
        //         'Saludos.',
        // ];

        //Mail::to($paciente->email)->send(new \App\Mail\RegistroPacienteMail($details));

        $datos['estado'] = 1;
        return response()->json($datos);
    }

    public function getPerfilProfesional(Request $request){
        $id_profesional = $request->id_profesional;
        $datos = array();

        // PRUEBA: Si ves este mensaje, el archivo se está ejecutando correctamente
        $datos['debug_test'] = 'ARCHIVO_CORRECTO_EJECUTANDOSE';

        $profesional = Profesional::where('id', $id_profesional)->with('Especialidad', 'TipoEspecialidad', 'SubTipoEspecialidad', 'LugaresAtencion')->first();
        if($profesional)
        {
            $datos['estado'] = 1;
            $datos['msj'] = 'registros';

            // busqueda de imagen
            $array_rut = explode('-',$profesional->rut);
            $nombre_imagen = asset('images/iconos/usuario_profesional.svg');

            if(file_exists(public_path('images/img_perfil/'.$array_rut[0].'.png')))
            {
                $nombre_imagen = asset('images/img_perfil/'.$array_rut[0].'.png');
            }

            // lugar atencion con direcciones
            $lugares_atencion_originales = $profesional->LugaresAtencion()->where('estado',1)->get();
            $lugares_atencion = [];

            $lugares_html = "<ul>";
            foreach ($lugares_atencion_originales as $lugar) {
                // Convertir el modelo a array y agregar información de dirección
                $lugar_array = $lugar->toArray();

                $direccion = Direccion::where('id', $lugar->id_direccion)->first();

                // Debug temporal - VERSIÓN ACTUALIZADA
                $lugar_array['debug_id_direccion'] = $lugar->id_direccion;
                $lugar_array['debug_direccion_encontrada'] = $direccion ? 'SI' : 'NO';
                $lugar_array['debug_version'] = 'V2024_ACTUALIZADA';

                if($direccion) {
                    $direccion_texto = trim($direccion->direccion).' '.trim($direccion->numero_dir);
                    $lugar_array['direccion'] = $direccion_texto;
                    $lugar_array['direccion_completa'] = $direccion_texto;
                    $lugar_array['debug_direccion_campos'] = [
                        'direccion' => $direccion->direccion,
                        'numero_dir' => $direccion->numero_dir
                    ];
                    $lugares_html .= "<li>".$lugar->nombre." - ".$direccion_texto."</li>";
                } else {
                    $lugar_array['direccion'] = 'Dirección no disponible';
                    $lugar_array['direccion_completa'] = 'Dirección no disponible';
                    $lugares_html .= "<li>".$lugar->nombre." - Dirección no disponible</li>";
                }

                $lugares_atencion[] = $lugar_array;
            }
            $lugares_html .= "</ul> <!-- VERSION_ACTUALIZADA -->";

            // Convertir el profesional a array y modificar
            $profesional_array = $profesional->toArray();
            $profesional_array['lugares_atencion'] = $lugares_atencion;
            $profesional_array['lugares_atencion_html'] = $lugares_html;
            $profesional_array['img_profesional'] = $nombre_imagen;

            // Asignar el profesional modificado
            $datos['profesional'] = $profesional_array;

        }
        else
        {
            $datos['estado'] = 0;
            $datos['msj'] = 'sin registros';
        }

        return response()->json($datos);
    }

    //RECETAS ONLINE
    public function getMisExamenes(Request $request) {
        $datos = array();
        $error = array();
        $campos_requeridos = 0;

        if(empty($request->id_paciente)||(int)$request->id_paciente==0) // id_paciente id_usuario
        {
            $error['id_paciente'] = 'campo requerido';
            $campos_requeridos = 1;
        }

        if($campos_requeridos==0)
        {
            $paciente = Paciente::where('id_usuario',$request->id_paciente)->first();

            $registros = FichaAtencion::select('fichas_atenciones.id as id','detalles_receta.posologia as posologia','fichas_atenciones.hipotesis_diagnostico as hipotesis_diagnostico','fichas_atenciones.created_at as created_at','fichas_atenciones.id as id_ficha' )
										->leftjoin('detalles_receta', 'fichas_atenciones.id', '=', 'detalles_receta.id_ficha')
                                        ->where('fichas_atenciones.id_paciente',$paciente->id)
										->orderBy('fichas_atenciones.id','desc')
                                        ->get();

			$id_fichas = array();
			$registro_limpios = array();
            foreach($registros as $key => $value)
			{
				if(in_array($value->id,$id_fichas)==false)
				{
					$registro_limpios[] = $value;
					$id_fichas[] = $value->id;
				}

			}
        }

        return response($datos)->header('Content-Type', 'application/json');
    }


    public function getMisRecetas(Request $request) {
        $datos = array();
        $error = array();
        $campos_requeridos = 0;

        if(empty($request->id_paciente)||(int)$request->id_paciente==0) // id_paciente id_usuario
        {
            $error['id_paciente'] = 'campo requerido';
            $campos_requeridos = 1;
        }

        if($campos_requeridos==0)
        {
            $paciente = Paciente::where('id_usuario',$request->id_paciente)->first();

            $registros = FichaAtencion::select('fichas_atenciones.id as id','detalles_receta.posologia as posologia','fichas_atenciones.hipotesis_diagnostico as hipotesis_diagnostico','fichas_atenciones.created_at as created_at','fichas_atenciones.id as id_ficha' )
										->leftjoin('detalles_receta', 'fichas_atenciones.id', '=', 'detalles_receta.id_ficha')
                                        ->where('fichas_atenciones.id_paciente',$paciente->id)
										->orderBy('fichas_atenciones.id','desc')
                                        ->get();

			$id_fichas = array();
			$registro_limpios = array();
            foreach($registros as $key => $value)
			{
				if(in_array($value->id,$id_fichas)==false)
				{
					$registro_limpios[] = $value;
					$id_fichas[] = $value->id;
				}

			}
            $recomendaciones = [];
            $id_recomendaciones = [];
            foreach($id_fichas as $i){
                $recomendacion = Recomendacion::select('recomendacion.*','fichas_atenciones.id as id_ficha','fichas_atenciones.hipotesis_diagnostico as hipotesis_diagnostico','fichas_atenciones.created_at as created_at')
                                    ->join('fichas_atenciones','fichas_atenciones.id','=','recomendacion.atencion')
                                    ->where('fichas_atenciones.id',$i)
                                    ->first();
                if($recomendacion){
                    $recomendaciones[] = $recomendacion;
                    $id_recomendaciones[] = $recomendacion->id;
                }
            }

            foreach($recomendaciones as $key => $value_det){
                $recomendacionDetalle = RecomendacionDetalle::where('id_recomendacion',$value_det->id)->get();

                 if($recomendacionDetalle)
                    {
                        $temp_val = array();
                        foreach ($recomendacionDetalle as $key_det => $value_det)
                        {
                            $temp_val[] = array(
                                'id' => $value_det->id,
                                'id_receta' => $value_det->id_recomendacion,
                                'id_tipo_control' => decrypt($value_det->control),
                                'id_producto' => decrypt($value_det->id_articulo),
                                'producto' => decrypt($value_det->articulo),
                                'farmaco' => decrypt($value_det->componente),
                                'id_presentacion' => decrypt($value_det->id_apariencia),
                                'presentacion' => decrypt($value_det->apariencia),
                                'id_receta_dosis' => decrypt($value_det->id_cuota),
                                'posologia' => decrypt($value_det->cuota),
                                'id_via_administracion' => decrypt($value_det->id_regimen),
                                'via_administracion' => decrypt($value_det->regimen),
                                'id_periodo' => decrypt($value_det->id_lapso),
                                'periodo' => decrypt($value_det->lapso),
                                'uso_cronico' => decrypt($value_det->uso_frecuente),
                                'cantidad_compra' => decrypt($value_det->volumen_compra),
                                'cantidad' => decrypt($value_det->volumen),
                                'cantidad_vendida' => decrypt($value_det->volumen_entregado),
                                'comentario' => decrypt($value_det->comentario),
                                'token_doc' => $value_det->cod_doc,
                                'estado' => $value_det->estado,
                            );
                        }
                        $recomendaciones[$key]['detalle'] = new ArrayObject($temp_val);
                    }
            }

            if($registros)
            {
                $datos['estado'] = 1;
                $datos['registros'] = $registro_limpios;
                $datos['recomendaciones'] = $recomendaciones;
                $datos['id_recomendaciones'] = $id_recomendaciones;
                $datos['request'] = $request->all();

            }else{
                $datos['estado'] = 0;
                $datos['msg'] = 'Registro no encontrado';
                $datos['request'] = $request->all();
            }


        }else{
            $datos['estado'] = 0;
            $datos['msg'] = 'Campos Requeridos';
            $datos['request'] = $request->all();
            $datos['error'] = $error;
        }

        return response($datos)->header('Content-Type', 'application/json');
    }
    public function getMisLicencias(Request $request) {
        return $request;
    }
    public function getMisCertificados(Request $request) {
        return $request;
    }
    public function getMisDocumentos(Request $request) {
        return $request;
    }
    public function getMisControles(Request $request) {
        return $request;
    }

    public function anularHoraMedica(Request $request){
        $idHora = $request->id_hora ?? $request->id_hora_medica;
        $hora_medica = HoraMedica::where('id', $idHora)->first();

        if (!$hora_medica) {
            return response()->json([
                'estado' => 0,
                'msj' => 'Hora no encontrada',
            ], 404);
        }

        $hora_medica->comentarios_cancelacion = 'Cancelada por paciente desde app';
        $hora_medica->fecha_cancelacion = Carbon::now();
        $hora_medica->id_estado = 3;
        $paciente = Paciente::where('id', $hora_medica->id_paciente)->first();
        $profesional = Profesional::where('id', $hora_medica->id_profesional)->first();

        if (!$hora_medica->save()) {
            return 'error';
        }


        $notificacion = NotificacionConfirmacion::where('tipo_notificacion',1)
                                                ->where('id_evento',$hora_medica->id)
                                                ->first();
        $datos['notificacion'] = $notificacion;
        if($notificacion)
        {
            /** cambiar estado notificacion */
            $notificacion->medio_confirmacion = $request->medio_confirmacion;
            $notificacion->fecha_confirmacion = date('Y-m-d H:m:s');
            $notificacion->estado_confirmacion = 3; // RECHAZADA
            if($notificacion->save())
            {
                /** notificacion actualizada */
                $datos['notificacion']['update'] = 'notificacion Actualzada';
            }
            else
            {
                $datos['notificacion']['update'] = 'falla al actualizar notificacion';
            }

            /** cambiar estado de log */
            $id_log_users_devices = $notificacion->id_log_users_devices;
            if(!empty($id_log_users_devices))
            {
                $log_users_devices = LogUsersDevices::find($id_log_users_devices);
                $log_users_devices->estado = 3; //RECHAZADA
                if($log_users_devices->save())
                {
                    /** log_users_devices */
                    $datos['log_users_devices']['update'] = 'log_users_devices Actualzada';
                }
                else
                {
                    $datos['log_users_devices']['update'] = 'falla al actualizar log_users_devices';
                }
            }
            else
            {
                $datos['log_users_devices']['update'] = 'no posee log_users_devices';
            }
        }

        /** enviar correo de confirmada */
        $paciente = $hora_medica->Paciente()->first();
        $profesional = $hora_medica->Profesional()->first();
        $lugar_atencion = $hora_medica->LugarAtencion()->first();

        $blade = 'hora_cancelada_paciente';
        $to = array(
                array('email' => $paciente->email,'name' =>  $paciente->nombres . ' ' . $paciente->apellido_uno . ' ' . $paciente->apellido_dos),
            );
        $cc = array();
        $bcc = array();
        $asunto = 'VET-SDI - Reserva de Hora Cancelada';
        if(!empty($profesional))
        {
            $body = array(
                'nombre_paciente'=> mb_strtoupper($paciente->nombres . ' ' . $paciente->apellido_uno . ' ' . $paciente->apellido_dos),
                'fecha'=> $hora_medica->fecha_evento,
                'hora'=> $hora_medica->hora_evento,
                'profesional_nombre'=> mb_strtoupper($profesional->nombre . ' ' . $profesional->apellido_uno . ' ' . $profesional->apellido_dos),
                'profesional_especialidad'=> mb_strtoupper($profesional->Especialidad()->first()->nombre),
                'profesional_tipo_especialidad'=> mb_strtoupper($profesional->TipoEspecialidad()->first()->nombre),
                'profesional_sub_tipo_especialidad' => optional($profesional->SubTipoEspecialidad()->first())->nombre ?? null, // Si no existe, no se muestra
                // 'institucion'=> $nombre_institucion,
                'lugar_atencion'=> mb_strtoupper($lugar_atencion->nombre),
                'direccion'=> mb_strtoupper($lugar_atencion->Direccion()->first()->direccion.' '.$lugar_atencion->Direccion()->first()->numero_dir.', '.$lugar_atencion->Direccion()->first()->Ciudad()->first()->nombre),
            );
        }
        else
        {
            $body = array(
                'nombre_paciente'=> mb_strtoupper($paciente->nombres . ' ' . $paciente->apellido_uno . ' ' . $paciente->apellido_dos),
                'fecha'=> $hora_medica->fecha_evento,
                'hora'=> $hora_medica->hora_evento,
                // 'institucion'=> $nombre_institucion,
                'lugar_atencion'=> mb_strtoupper($lugar_atencion->nombre),
                'direccion'=> mb_strtoupper($lugar_atencion->Direccion()->first()->direccion.' '.$lugar_atencion->Direccion()->first()->numero_dir.', '.$lugar_atencion->Direccion()->first()->Ciudad()->first()->nombre),
            );
        }

        $archivo = '';/** pendiente */
        $id_institucion = '';

        $result_mail =  SendMailController::envioCorreo($blade, $to, $cc, $bcc, $asunto, $body, $archivo, $id_institucion);

        if($result_mail['estado'])
        {
            $datos['mail'] = 'Notificacion de cancelacion enviado';
        }
        else
        {
            $datos['mail'] = 'Falle en envio de Notificacion de cancelada';
        }

        $datos['estado'] = 1;
        $datos['msj'] = 'Hora Cancelada';

        return response()->json($datos);
    }

    public function dameRegiones(){
        $regiones = Region::all();
        return response()->json([
            'estado' => 1,
            'regiones' => $regiones
        ]);
    }

    public function dameCiudades(Request $request){
        $id = $request->id_region;
        $ciudades = Ciudad::where('id_region',$id)->get();
        return response()->json([
            'estado' => 1,
            'ciudades' => $ciudades
        ]);
    }

    public function buscarProfesionales(Request $request){
        // Validación de token X-Auth-Token
        $authToken = $request->header('X-Auth-Token');
        if (!$authToken) {
            return response()->json([
                'estado' => 0,
                'mensaje' => 'Token de autenticación requerido'
            ], 401);
        }

        // Buscar el token en la base de datos
        $token = PersonalAccessToken::findToken($authToken);
        if (!$token) {
            return response()->json([
                'estado' => 0,
                'mensaje' => 'Token de autenticación inválido'
            ], 401);
        }

        // Verificar que el token no haya expirado
        if ($token->expires_at && $token->expires_at->isPast()) {
            return response()->json([
                'estado' => 0,
                'mensaje' => 'Token de autenticación expirado'
            ], 401);
        }

        // Obtener el usuario autenticado del token
        $user = $token->tokenable;
        if (!$user) {
            return response()->json([
                'estado' => 0,
                'mensaje' => 'Usuario no encontrado'
            ], 401);
        }

        // Validar y limpiar parámetros de entrada
        $id_region = $request->filled('id_region') && $request->id_region != '0' && $request->id_region != '' ? $request->id_region : null;
        $id_ciudad = $request->filled('id_ciudad') && $request->id_ciudad != '0' && $request->id_ciudad != '' ? $request->id_ciudad : null;
        $id_especialidad = $request->filled('id_especialidad') && $request->id_especialidad != '0' && $request->id_especialidad != '' ? $request->id_especialidad : null;
        $id_tipo_especialidad = $request->filled('id_tipo_especialidad') && $request->id_tipo_especialidad != '0' && $request->id_tipo_especialidad != '' ? $request->id_tipo_especialidad : null;
        $id_sub_tipo_especialidad = $request->filled('id_sub_tipo_especialidad') && $request->id_sub_tipo_especialidad != '0' && $request->id_sub_tipo_especialidad != '' ? $request->id_sub_tipo_especialidad : null;
        $nombre_profesional = $request->filled('nombre_profesional') && trim($request->nombre_profesional) != '' ? strtolower(trim($request->nombre_profesional)) : null;

        // Validar que al menos se envíe región o ciudad
        if (!$id_region && !$id_ciudad) {
            return response()->json([
                'estado' => 0,
                'mensaje' => 'Debe seleccionar al menos una región o ciudad para realizar la búsqueda',
                'registros' => []
            ], 400);
        }

        // buscamos las ciudades correspondiente a la region o ciudad específica
        $ciudades = collect();
        $ciudad = null;

        if ($id_ciudad) {
            // Si se especifica ciudad, buscar esa ciudad específica
            $ciudad = Ciudad::find($id_ciudad);
            if ($ciudad) {
                $ciudades = collect([$ciudad]);
            }
        } elseif ($id_region) {
            // Si solo se especifica región, buscar todas las ciudades de esa región
            $ciudades = Ciudad::where('id_region', $id_region)->get();
        }

        if ($ciudades->isEmpty()) {
            return response()->json([
                'estado' => 0,
                'mensaje' => 'No se encontraron ciudades para los criterios especificados',
                'registros' => []
            ], 404);
        }

        // direcciones en las ciudades encontradas
        $ids_ciudades = $ciudades->pluck('id')->toArray();
        $direcciones = Direccion::whereIn('id_ciudad', $ids_ciudades)->get();

        // lugares de atención
        $ids_direcciones = $direcciones->pluck('id')->toArray();
        $lugares_atencion = LugarAtencion::whereIn('id_direccion', $ids_direcciones)->get();

        // Obtener profesionales con todos los filtros aplicados
        $ids_lugares_atencion = $lugares_atencion->pluck('id')->toArray();

        // Construir query base para profesionales
        $query = Profesional::whereHas('LugaresAtencion', function($q) use ($ids_lugares_atencion) {
            $q->whereIn('lugares_atencion.id', $ids_lugares_atencion)
              ->where('profesionales_lugares_atencion.estado', 1);
        })->with(['Especialidad', 'TipoEspecialidad', 'SubTipoEspecialidad']);

        // Aplicar filtros si están presentes
        if ($id_especialidad) {
            $query->where('id_especialidad', $id_especialidad);
        }

        if ($id_tipo_especialidad) {
            $query->where('id_tipo_especialidad', $id_tipo_especialidad);
        }

        if ($id_sub_tipo_especialidad) {
            $query->where('id_sub_tipo_especialidad', $id_sub_tipo_especialidad);
        }

        // Filtro por nombre del profesional
        if ($nombre_profesional) {
            $query->where(function($q) use ($nombre_profesional) {
                $q->whereRaw('LOWER(nombre) LIKE ?', ["%{$nombre_profesional}%"])
                  ->orWhereRaw('LOWER(apellido_uno) LIKE ?', ["%{$nombre_profesional}%"])
                  ->orWhereRaw('LOWER(apellido_dos) LIKE ?', ["%{$nombre_profesional}%"])
                  ->orWhereRaw('LOWER(CONCAT(nombre, " ", apellido_uno, " ", apellido_dos)) LIKE ?', ["%{$nombre_profesional}%"]);
            });
        }

        $profesionales_lugares_atencion = $query->get();

        // Enriquecer información de profesionales
        foreach($profesionales_lugares_atencion as $prof){
            // Obtener lugares de atención del profesional
            $lugares_prof = $prof->LugaresAtencion()->where('profesionales_lugares_atencion.estado', 1)->get();
            $nombres_lugares = $lugares_prof->pluck('nombre')->toArray();

            $prof->nombre_lugar_atencion = implode(', ', $nombres_lugares);
            $prof->lugares_atencion_detalle = $lugares_prof;

            // Información de especialidad
            $prof->nombre_especialidad = $prof->Especialidad ? $prof->Especialidad->nombre : '';
            $prof->nombre_tipo_especialidad = $prof->TipoEspecialidad ? $prof->TipoEspecialidad->nombre : '';
            $prof->nombre_sub_tipo_especialidad = $prof->SubTipoEspecialidad ? $prof->SubTipoEspecialidad->nombre : '';

            // Buscar imagen de perfil
            $array_rut = explode('-', $prof->rut);
            $nombre_imagen = asset('images/iconos/usuario_profesional.svg');

            if(file_exists(public_path('images/img_perfil/'.$array_rut[0].'.png'))) {
                $nombre_imagen = asset('images/img_perfil/'.$array_rut[0].'.png');
            }
            $prof->img_profesional = $nombre_imagen;
        }

        return response()->json([
            'estado' => 1,
            'mensaje' => count($profesionales_lugares_atencion) . ' profesionales encontrados',
            'total_resultados' => count($profesionales_lugares_atencion),
            'filtros_aplicados' => [
                'region' => $id_region,
                'ciudad' => $id_ciudad,
                'especialidad' => $id_especialidad,
                'tipo_especialidad' => $id_tipo_especialidad,
                'sub_tipo_especialidad' => $id_sub_tipo_especialidad,
                'nombre_profesional' => $nombre_profesional
            ],
            'ciudades' => $ciudades,
            'direcciones' => $direcciones,
            'ciudad' => $ciudad,
            'lugares_atencion' => $lugares_atencion,
            'profesionales' => $profesionales_lugares_atencion
        ]);


    }

    public function confirmarHoraMedica(Request $request){

        // return response()->json([
        //     'estado' => 0,
        //     'mensaje' => 'Funcionalidad temporalmente deshabilitada',
        //     'registros' => $request->all()
        // ], 200);

        $idHora = $request->id_hora ?? $request->id_hora_medica;
        $hora_medica = HoraMedica::where('id', $idHora)->first();

        if (!$hora_medica) {
            return response()->json([
                'estado' => 0,
                'msj' => 'Hora no encontrada',
            ], 404);
        }

        $hora_medica->id_estado = 2;
        if (!$hora_medica->save()) {
            return response()->json([
                'estado' => 0,
                'msj' => 'No fue posible confirmar la hora',
            ], 500);
        }

        $notificacion = NotificacionConfirmacion::where('tipo_notificacion',1)
                                                ->where('id_evento',$hora_medica->id)
                                                ->first();
        $datos['notificacion'] = $notificacion;
        if($notificacion)
        {
            /** cambiar estado notificacion */
            $notificacion->medio_confirmacion = $request->medio_confirmacion;
            $notificacion->fecha_confirmacion = date('Y-m-d H:m:s');
            $notificacion->estado_confirmacion = 2; // CONFIRMADA
            if($notificacion->save())
            {
                /** notificacion actualizada */
                $datos['notificacion']['update'] = 'notificacion Actualzada';
            }
            else
            {
                $datos['notificacion']['update'] = 'falla al actualizar notificacion';
            }

            /** cambiar estado de log */
            $id_log_users_devices = $notificacion->id_log_users_devices;
            if(!empty($id_log_users_devices))
            {
                $log_users_devices = LogUsersDevices::find($id_log_users_devices);
                $log_users_devices->estado = 2; //CONFIRMADA
                if($log_users_devices->save())
                {
                    /** log_users_devices */
                    $datos['log_users_devices']['update'] = 'log_users_devices Actualzada';
                }
                else
                {
                    $datos['log_users_devices']['update'] = 'falla al actualizar log_users_devices';
                }
            }
            else
            {
                $datos['log_users_devices']['update'] = 'no posee log_users_devices';
            }
        }

        $datos['estado'] = 1;
        $datos['msj'] = 'Hora Confirmada';

        return response()->json($datos);
    }

}
