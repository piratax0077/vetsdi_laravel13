<?php

namespace App\Http\Controllers;

use App\Models\Asistente;
use App\Models\Bono;
use App\Models\Especialidad;
use App\Models\HoraMedica;
use App\Models\Instituciones;
use App\Models\Invitacion;
use App\Models\LugarAtencion;
use App\Models\Mensajes;
use App\Models\Paciente;
use App\Models\Prevision;
use App\Models\Profesional;
use App\Models\Servicios;
use App\Models\Parametro;
use App\Models\Personas;
use App\Models\ProfesionalConvenio;
use App\Models\ProfesionalesLugaresAtencion;
use App\Models\ProfesionalHorario;
use App\Models\ProfesionalInstitucionConvenio;
use App\Models\SubTipoEspecialidad;
use App\Models\TipoEspecialidad;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class EscritorioGeneral extends Controller
{
    /**
     * BUSQUEDA DE PROFESIONAL
     *
     * @param Request $request
     * @return array (Profesional)
     */
    public function buscarProfesional(Request $request)
    {
        $datos = array();
        $filtro = array();
        if(!empty($request->nombre))
            $filtro[] = array('nombre','like',$request->nombre.'%');
        if(!empty($request->apellido_uno))
            $filtro[] = array('apellido_uno','like',$request->apellido_uno.'%');
        if(!empty($request->apellido_dos))
            $filtro[] = array('apellido_dos','like',$request->apellido_dos.'%');

        if(!empty($request->rut))
            $filtro[] = array('rut',$request->rut);

        // if(!empty($request->id_direccion))
        //     $filtro[] = array('id_direccion',request->id_direccion);
        if(!empty($request->id_especialidad))
            $filtro[] = array('id_especialidad', $request->id_especialidad);
        if(!empty($request->id_tipo_especialidad))
            $filtro[] = array('id_tipo_especialidad', $request->id_tipo_especialidad);
        if(!empty($request->id_sub_tipo_especialidad))
            $filtro[] = array('id_sub_tipo_especialidad', $request->id_sub_tipo_especialidad);

        if(!empty($request->id_sub_tipo_especialidad))
            $filtro[] = array('id_sub_tipo_especialidad', $request->id_sub_tipo_especialidad);
        if(!empty($request->id_sub_tipo_especialidad))
            $filtro[] = array('id_sub_tipo_especialidad', $request->id_sub_tipo_especialidad);


        $profesional = Profesional::where('id_usuario', Auth::user()->id)->first();
        if($profesional)
            $registros = Profesional::where($filtro)->whereNotIn('id',[$profesional->id])->get();
        else
            $registros = Profesional::where($filtro)->get();


        if($registros)
        {
            $datos['estado'] = 1;
            $datos['msj'] = 'registros';
            $datos['registros'] = $registros;
        }
        else
        {
            $datos['estado'] = 0;
            $datos['msj'] = 'sin registros';
        }
        return $datos;
    }

    /**
     * BUSQUEDA DE PROFESIONAL buscador
     *
     * @param Request $request
     * @return array (Profesional)
     */
    /**
     * Busca en los aranceles del veterinario (consultas, procedimientos y exámenes activos) el que
     * corresponde al servicio elegido. Se compara por palabras: "Consulta general" sirve para
     * "Consulta veterinaria general". Devuelve el valor con IVA si corresponde, o null si no lo tiene.
     */
    /** Medicina general veterinaria (y sus nombres antiguos): la ofrece todo médico veterinario */
    private function esMedicinaGeneralVeterinaria(string $item): bool
    {
        $texto = \Illuminate\Support\Str::lower(\Illuminate\Support\Str::ascii($item));

        return (bool) preg_match('/^(medicina (veterinaria )?general|consulta (veterinaria )?general|control (canino|felino))/', trim($texto));
    }

    /** ¿El profesional tiene activo ese servicio en sus aranceles? (mismo cruce de palabras que el valor) */
    private function ofreceServicioArancel(array $datos, string $servicio): bool
    {
        $palabras = function (string $texto): array {
            $texto = \Illuminate\Support\Str::lower(\Illuminate\Support\Str::ascii($texto));
            $lista = preg_split('/[^a-z0-9]+/', $texto, -1, PREG_SPLIT_NO_EMPTY);
            $comunes = ['de', 'del', 'la', 'las', 'los', 'el', 'y', 'en', 'para', 'veterinaria', 'veterinario', 'veterinarias', 'consulta'];

            return array_values(array_unique(array_diff($lista, $comunes)));
        };
        $buscado = $palabras($servicio);
        if (!$buscado) {
            return false;
        }

        foreach (['aranceles', 'procedimientos', 'examenes'] as $catalogo) {
            foreach ($datos[$catalogo] ?? [] as $item) {
                if (empty($item['activo']) || empty($item['nombre'])) {
                    continue;
                }
                $nombre = $palabras($item['nombre']);
                if ($nombre && (!array_diff($nombre, $buscado) || !array_diff($buscado, $nombre))) {
                    return true;
                }
            }
        }

        return false;
    }

    private function valorServicioArancel(array $datos, string $servicio): ?array
    {
        $palabras = function (string $texto): array {
            $texto = \Illuminate\Support\Str::lower(\Illuminate\Support\Str::ascii($texto));
            $lista = preg_split('/[^a-z0-9]+/', $texto, -1, PREG_SPLIT_NO_EMPTY);
            $comunes = ['de', 'del', 'la', 'las', 'los', 'el', 'y', 'en', 'para', 'veterinaria', 'veterinario', 'veterinarias'];

            return array_values(array_unique(array_diff($lista, $comunes)));
        };
        $buscado = $palabras($servicio);
        if (!$buscado) {
            return null;
        }

        $iva = (float) ($datos['config']['parametros']['iva'] ?? 19);
        $mejor = null;
        foreach (['aranceles', 'procedimientos', 'examenes'] as $catalogo) {
            foreach ($datos[$catalogo] ?? [] as $item) {
                if (empty($item['activo']) || empty($item['nombre'])) {
                    continue;
                }
                $nombre = $palabras($item['nombre']);
                $coincide = $nombre && (!array_diff($nombre, $buscado) || !array_diff($buscado, $nombre));
                if (!$coincide) {
                    continue;
                }

                $valores = [];
                $desde = false;
                if (!empty($item['variaPorTamano'])) {
                    foreach ($item['preciosTamano'] ?? [] as $tamano) {
                        if (!empty($tamano['activo'])) {
                            $valores[] = ['neto' => (int) $tamano['valorNeto'], 'iva' => !empty($tamano['afectoIva'])];
                        }
                    }
                    $desde = count($valores) > 1;
                }
                if (!$valores) {
                    $valores[] = ['neto' => (int) ($item['valorNeto'] ?? 0), 'iva' => !empty($item['afectoIva'])];
                }
                $totales = array_map(fn ($v) => (int) round($v['iva'] ? $v['neto'] * (1 + $iva / 100) : $v['neto']), $valores);
                $valor = min($totales);
                if ($valor <= 0) {
                    continue;
                }
                $desde = $desde && count(array_unique($totales)) > 1;
                if ($mejor === null || $valor < $mejor['valor']) {
                    $mejor = ['valor' => $valor, 'desde' => $desde];
                }
            }
        }

        return $mejor;
    }

    public function buscarProfesionalBuscador(Request $request)
    {
        $datos = array();
        $filtro = array();
        $esVeterinaria = (int) $request->input('es_veterinaria_busqueda', 0) === 1;
        $veterinariaArea = trim((string) $request->input('veterinaria_area', ''));
        $veterinariaItem = trim((string) $request->input('veterinaria_item', ''));
        $bindings = [];

        if(!empty($request->nombre))
            $filtro[] = array('nombre','like',$request->nombre.'%');
        if(!empty($request->apellido_uno))
            $filtro[] = array('apellido_uno','like',$request->apellido_uno.'%');
        if(!empty($request->apellido_dos))
            $filtro[] = array('apellido_dos','like',$request->apellido_dos.'%');
        // if(!empty($request->id_direccion))
        //     $filtro[] = array('id_direccion',request->id_direccion);
        if(!$esVeterinaria && !empty($request->id_especialidad))
            $filtro[] = array('id_especialidad', $request->id_especialidad);
        if(!$esVeterinaria && !empty($request->id_tipo_especialidad))
            $filtro[] = array('id_tipo_especialidad', $request->id_tipo_especialidad);
        if(!$esVeterinaria && !empty($request->id_sub_tipo_especialidad))
            $filtro[] = array('id_sub_tipo_especialidad', $request->id_sub_tipo_especialidad);

        // if(Auth::user()->hasRole('Profesional'))
        // {
        //     $profesional = Profesional::where('id_usuario', Auth::user()->id)->first();
        //     $registros = Profesional::where($filtro)->whereNotIn('id',[$profesional->id])->get();
        // }
        // else
        {
            // $registros = Profesional::where($filtro)
            //                 ->with(['Direccion'=>function($query) use ($request){
            //                         $query->where('id_ciudad',$request->id_ciudad);
            //                 }])
            //                 ->Dire
            //                 ->get();
            $sql  = "SELECT ";
            $sql .= "    profesionales.id profesionales_id, ";
            $sql .= "    profesionales.nombre profesionales_nombre, ";
            $sql .= "    profesionales.apellido_uno profesionales_apellido_uno, ";
            $sql .= "    profesionales.apellido_dos profesionales_apellido_dos, ";
            $sql .= "    profesionales.rut profesionales_rut, ";
            $sql .= "    profesionales.telefono_uno profesional_telefono_uno, ";
            $sql .= "    profesionales.email profesional_email, ";
            $sql .= "    profesionales.certificado profesional_certificado, ";
            $sql .= "    profesionales.numero_certificado profesional_numero_certificado, ";
            $sql .= "    profesionales.dv_certiicado profesional_dv_certiicado , ";
            $sql .= "    profesionales.id_direccion profesional_id_direccion, ";
            $sql .= "    profesionales.foto_perfil profesionales_foto_perfil, ";

            $sql .= "    profesionales.id_especialidad profesional_id_especialidad, ";
            $sql .= "    especialidades.nombre especialidades_nombre, ";

            $sql .= "    profesionales.id_tipo_especialidad profesional_id_tipo_especialidad, ";
            $sql .= "    tipos_especialidad.nombre tipos_especialidad_nombre, ";

            $sql .= "    profesionales.id_sub_tipo_especialidad profesional_id_sub_tipo_especialidad, ";
            $sql .= "    sub_tipo_especialidad.nombre sub_tipo_especialidad_nombre, ";

            $sql .= "    direcciones.id direcciones_id, ";
            $sql .= "    direcciones.direccion direcciones_direccion, ";
            $sql .= "    direcciones.id_ciudad direcciones_id_ciudad, ";

            $sql .= "    ciudades.nombre ciudades_nombre, ";
            $sql .= "    ciudades.id_region ciudades_id_region, ";

            $sql .= "    regiones.nombre  regiones_nombre ";
            $sql .= " FROM profesionales ";

            $sql .= " LEFT JOIN direcciones ON (direcciones.id = profesionales.id_direccion ) ";

            $sql .= " LEFT JOIN ciudades ON (ciudades.id = direcciones.id_ciudad) ";

            $sql .= " LEFT JOIN regiones ON (regiones.id = ciudades.id_region ) ";

            $sql .= " INNER JOIN especialidades ON (profesionales.id_especialidad = especialidades.id) ";

            $sql .= " LEFT JOIN tipos_especialidad ON (profesionales.id_tipo_especialidad = tipos_especialidad.id) ";

            $sql .= " LEFT JOIN sub_tipo_especialidad ON (profesionales.id_sub_tipo_especialidad = sub_tipo_especialidad.id) ";

            $sql .= " WHERE 1=1";

            if ($esVeterinaria) {
                // Médico veterinario, odontólogo veterinario y peluquería/aseo.
                // Evita mezclar profesionales del sistema clínico humano.
                $sql .= " AND profesionales.id_especialidad IN (1,2,15)";

                // Solo aparece quien ofrece horas: debe tener horario registrado en al menos
                // uno de sus lugares de atención activos
                $sql .= " AND profesionales.id IN (
                    SELECT ph.id_profesional
                    FROM profesional_horarios ph
                    INNER JOIN profesionales_lugares_atencion pla
                        ON pla.id_profesional = ph.id_profesional
                        AND pla.id_lugar_atencion = ph.id_lugar_atencion
                        AND pla.estado = 1
                )";
            }

            if(!$esVeterinaria && !empty($request->id_especialidad))
                $sql .= " AND profesionales.id_especialidad = ".$request->id_especialidad."";
            if(!$esVeterinaria && !empty($request->id_tipo_especialidad))
                $sql .= " AND profesionales.id_tipo_especialidad = ".$request->id_tipo_especialidad."";
            if(!$esVeterinaria && !empty($request->id_sub_tipo_especialidad))
                $sql .= " AND profesionales.id_sub_tipo_especialidad = ".$request->id_sub_tipo_especialidad."";
            if(!empty($request->nombre))
                $sql .= " AND profesionales.nombre LIKE '".$request->nombre."%'";
            if(!empty($request->apellido_uno))
                $sql .= " AND profesionales.apellido_uno LIKE '".$request->apellido_uno."%'";
            if(!empty($request->apellido_dos))
                $sql .= " AND profesionales.apellido_dos LIKE '".$request->apellido_dos."%'";
            if(!empty($request->rut))
                $sql .= " AND profesionales.rut = '".$request->rut."'";

            if(!empty($request->nombre_rut))
            {
                $nombreRut = trim((string) $request->nombre_rut);
                $rutNormalizado = strtoupper((string) preg_replace('/[^0-9K]/i', '', $nombreRut));
                $sql .= " AND (CONCAT_WS(' ', profesionales.nombre, profesionales.apellido_uno, profesionales.apellido_dos) LIKE ?";
                $sql .= " OR REPLACE(REPLACE(UPPER(profesionales.rut), '.', ''), '-', '') LIKE ?)";
                $bindings[] = '%'.$nombreRut.'%';
                $bindings[] = '%'.$rutNormalizado.'%';
            }

            if(!empty($request->id_region)) {
                if ($esVeterinaria) {
                    $sql .= " AND profesionales.id IN (
                        SELECT pla.id_profesional
                        FROM profesionales_lugares_atencion pla
                        INNER JOIN lugares_atencion la ON la.id = pla.id_lugar_atencion
                        INNER JOIN direcciones dla ON dla.id = la.id_direccion
                        INNER JOIN ciudades cla ON cla.id = dla.id_ciudad
                        WHERE pla.estado = 1
                        AND cla.id_region = ".(int) $request->id_region."
                    )";
                } else {
                    $sql .= " AND regiones.id = ".(int) $request->id_region."";
                }
            }

            if(!empty($request->id_ciudad)) {
                if ($esVeterinaria) {
                    $sql .= " AND profesionales.id IN (
                        SELECT pla.id_profesional
                        FROM profesionales_lugares_atencion pla
                        INNER JOIN lugares_atencion la ON la.id = pla.id_lugar_atencion
                        INNER JOIN direcciones dla ON dla.id = la.id_direccion
                        WHERE pla.estado = 1
                        AND dla.id_ciudad = ".(int) $request->id_ciudad."
                    )";
                } else {
                    $sql .= " AND direcciones.id_ciudad = ".(int) $request->id_ciudad."";
                }
            }

            if(!empty($request->id_institucion))
                $sql .= " AND profesionales.id in (SELECT id_profesional FROM profesionales_lugares_atencion WHERE id_lugar_atencion = (SELECT id_lugar_atencion FROM instituciones WHERE id = ".$request->id_institucion.") and estado=1 )";

            // 1 -> Atención General
            // 2 -> Atención Dental
            // 3 -> Atención Telemedicina
            // 4 -> Examene
            if(!$esVeterinaria && !empty($request->tipo_agenda))
                $sql .= " AND profesionales.id IN (SELECT id_profesional FROM `profesional_horarios` WHERE tipo_agenda in (".$request->tipo_agenda.") GROUP BY id_profesional)";

            // $sql .= " AND profesionales.id IN (
            //                 SELECT profesionales_lugares_atencion.id_profesional
            //                 FROM `profesionales_lugares_atencion`
            //                 WHERE profesionales_lugares_atencion.`estado` = 1
            //                     and profesionales_lugares_atencion.id_lugar_atencion in (
            //                                                                     SELECT
            //                                                                         lugares_atencion.id
            //                                                                     FROM
            //                                                                         `lugares_atencion`
            //                                                                     INNER JOIN direcciones ON direcciones.id = lugares_atencion.id_direccion
            //                                                                     INNER JOIN ciudades ON ciudades.id = direcciones.id_ciudad
            //                                                                     INNER JOIN regiones ON ciudades.id_region = regiones.id
            //                                                                     WHERE  1=1 ";
            //                                                                         if(!empty($request->id_ciudad))
            //                                                                             $sql .= " AND direcciones.id_ciudad = ".$request->id_ciudad."";
            //                                                                         if(!empty($request->id_region))
            //                                                                             $sql .= " AND regiones.id = ".$request->id_region." ";
            //                                                         $sql .= " )
            //                 )
            //         ";
            // return var_dump($sql);
            // return  '**';
            // die();
            $registros = DB::select($sql, $bindings);
            $registros_validos = array();
            foreach ($registros as $key_r => $value_r)
            {
                // $profesional_horarios = ProfesionalHorario::where('id_profesional',$value_r->profesionales_id)->get();
                // $registros[$key_r]->profesional_horarios = $profesional_horarios;
                $registros[$key_r]->profesional_hora_mas_proxima = $this->cargarFechaMasProxima(
                    $value_r->profesionales_id,
                    $request->tipo_agenda ?: 1
                );
                // $registros[$key_r]->profesional_hora_mas_proxima = array();

                // var_dump($registros[$key_r]->profesional_hora_mas_proxima);

                // busqueda de imagen
                $array_rut = explode('-',$value_r->profesionales_rut);
                $nombre_imagen = asset('images/iconos/usuario_profesional.svg');
                if(file_exists(public_path('images/img_perfil/'.$array_rut[0].'.png')))
                {
                    $nombre_imagen = asset('images/img_perfil/'.$array_rut[0].'.png');
                }
                $registros[$key_r]->img_profesional = $nombre_imagen;

                if ($esVeterinaria) {
                    $registros[$key_r]->veterinaria_area = $veterinariaArea !== '' ? $veterinariaArea : 'Veterinario/a';
                    $registros[$key_r]->veterinaria_item = $veterinariaItem !== '' ? $veterinariaItem : ($value_r->ciudades_nombre ?? null);
                }


                if($request->buscar_especialidad_hora24 == '1')
                {
                    if(count($registros[$key_r]->profesional_hora_mas_proxima)>0)
                    {
                        $dia = $registros[$key_r]->profesional_hora_mas_proxima['dia'];
                        $hora = $registros[$key_r]->profesional_hora_mas_proxima['hora'];
                        $dia_hora = date('Y-m-d H:i:s',strtotime($dia.' '.$hora));

                        // var_dump('****'.$value_r->profesionales_id.'****');
                        // var_dump('dia_hora:'.$dia_hora);
                        $dia_hoy = date('Y-m-d H:i:s');
                        // var_dump('dia_hoy:'.$dia_hoy);
                        $dia_hoy_24 = date('Y-m-d H:i:s', strtotime('+24 hours',strtotime($dia_hoy)) );
                        // var_dump('dia_hoy_24:'.$dia_hoy_24);


                        if(strtotime($dia_hora) > strtotime( date('Y-m-d H:i:s', strtotime($dia_hoy_24) )))
                        {
                            unset($registros[$key_r]);
                        }
                        else
                        {
                            $registros_validos[] = $registros[$key_r];
                        }

                    }
                    else
                    {
                        unset($registros[$key_r]);
                    }
                }
                else
                {
                    $registros_validos[] = $registros[$key_r];
                }
            }
        }

        $registros =  $registros_validos;

        // Especialidad, procedimiento o examen: solo aparece quien lo ofrece en sus aranceles.
        // La medicina general la atiende todo médico veterinario, así que no filtra.
        if ($esVeterinaria && $registros && $veterinariaItem !== '' && !$this->esMedicinaGeneralVeterinaria($veterinariaItem)) {
            $arancelesFiltro = DB::table('aranceles_profesionales')
                ->whereIn('id_profesional', array_map(fn ($r) => $r->profesionales_id, $registros))
                ->pluck('datos', 'id_profesional');

            $registros = array_values(array_filter($registros, function ($registro) use ($arancelesFiltro, $veterinariaItem) {
                $datosArancel = isset($arancelesFiltro[$registro->profesionales_id])
                    ? json_decode($arancelesFiltro[$registro->profesionales_id], true)
                    : null;

                return is_array($datosArancel) && $this->ofreceServicioArancel($datosArancel, $veterinariaItem);
            }));
        }

        // El modal "Reservar hora" del tutor muestra el valor del servicio elegido según los aranceles de cada veterinario
        if ($esVeterinaria && $request->boolean('con_valores') && $registros) {
            // "Medicina general veterinaria" es solo el nombre que ve el tutor de la consulta veterinaria general
            $servicioValor = ($veterinariaItem === '' || $veterinariaItem === 'Medicina general veterinaria')
                ? 'Consulta veterinaria general'
                : $veterinariaItem;
            $aranceles = DB::table('aranceles_profesionales')
                ->whereIn('id_profesional', array_map(fn ($r) => $r->profesionales_id, $registros))
                ->pluck('datos', 'id_profesional');

            foreach ($registros as $registro) {
                $datosArancel = isset($aranceles[$registro->profesionales_id]) ? json_decode($aranceles[$registro->profesionales_id], true) : null;
                $registro->valor_servicio = is_array($datosArancel) ? $this->valorServicioArancel($datosArancel, $servicioValor) : null;
            }
            if ($request->filled('veterinaria_area_nombre')) {
                foreach ($registros as $registro) {
                    $registro->veterinaria_area = trim((string) $request->input('veterinaria_area_nombre'));
                }
            }
        }

        if($registros)
        {
            $datos['estado'] = 1;
            $datos['msj'] = 'registros';
            $datos['registros'] = $registros;
            $datos['cant'] = count($registros);
        }
        else
        {
            $datos['estado'] = 0;
            $datos['msj'] = 'sin registros';
        }
        return $datos;
    }

    public function cargarFechaMasProxima($id_profesional, $tipo_agenda)
    {
        // Próxima hora libre de cada lugar activo del profesional, con los mismos criterios de la reserva
        // (domingo = 0, todos los turnos del día, bloqueos y choques en cualquier lugar). Ver AgendaDisponibilidad.
        $lugar_atencion_activo = ProfesionalesLugaresAtencion::where('id_profesional', $id_profesional)
            ->where('estado', '1')
            ->pluck('id_lugar_atencion')
            ->toArray();

        $proximas = app(\App\Services\AgendaDisponibilidad::class)
            ->proximaPorLugar((int) $id_profesional, $lugar_atencion_activo, $tipo_agenda);

        // La más próxima de cada lugar queda guardada para quien la necesite (ver proximaHoraPorLugar)
        $this->proximasPorLugar = [];
        $fecha_mas_proxima = array();
        foreach ($proximas as $id_lugar => $proxima) {
            $this->proximasPorLugar[$id_lugar] = array($proxima);
            $clave = $proxima['dia'].' '.$proxima['hora'];
            if (!$fecha_mas_proxima || $clave < $fecha_mas_proxima['dia'].' '.$fecha_mas_proxima['hora']) {
                $fecha_mas_proxima = array(
                    'id_lugar_atencion' => $id_lugar,
                    'dia' => $proxima['dia'],
                    'hora' => $proxima['hora'],
                );
            }
        }

        return $fecha_mas_proxima;
    }

    private array $proximasPorLugar = [];

    /**
     * Próxima hora libre del profesional en cada uno de sus lugares de atención.
     * La usa el modal "Reservar hora" del tutor al desplegar los lugares de un veterinario.
     */
    public function proximaHoraPorLugar(Request $request)
    {
        $request->validate(['id_profesional' => 'required|integer|min:1', 'tipo_agenda' => 'nullable|string|max:10']);
        $this->cargarFechaMasProxima((int) $request->id_profesional, $request->input('tipo_agenda') ?: 1);

        $proximas = [];
        foreach ($this->proximasPorLugar as $idLugar => $lista) {
            if (!empty($lista[0])) {
                $proximas[$idLugar] = $lista[0];
            }
        }

        return response()->json(['estado' => 1, 'registros' => $proximas]);
    }

    public function informacionProfesional(Request $request)
    {
        $datos = array();

        $profesional = Profesional::select('id', 'nombre', 'apellido_uno', 'apellido_dos', 'sexo', 'rut', 'email', 'telefono_uno', 'telefono_dos', 'estado', 'certificado',
                                            'numero_certificado', 'dv_certiicado', 'id_direccion', 'id_especialidad', 'id_usuario', 'id_tipo_especialidad', 'id_sub_tipo_especialidad')

                                    ->with(['Especialidad' => function($query){
                                        $query->select('id', 'nombre')->get();
                                    }])

                                    ->with(['TipoEspecialidad' => function($query){
                                        $query->select('id', 'nombre')->get();
                                    }])

                                    ->with(['SubTipoEspecialidad' => function($query){
                                        $query->select('id', 'nombre')->get();
                                    }])

                                    ->with(['Direccion' => function($query){
                                        $query->select('id', 'direccion', 'numero_dir', 'id_ciudad')
                                            ->with(['Ciudad' => function($query){
                                                $query->select('id', 'nombre')->get();
                                            }])
                                            ->get();
                                    }])

                                    ->with(['AntecedenteAcademico' => function($query){
                                        $query->select('id', 'id_profesional', 'id_tipo_antecedente_academico', 'nombre', 'universidad', 'anio', 'ciudad_pais', 'numero_inscripcion', 'supersalud', 'numero_colegio')
                                                    ->with(['TipoAntecedenteAcademico' => function($query){
                                                        $query->select('id', 'nombre')->get();
                                                    }])
                                            ->get();
                                    }])

                                    ->where('id', $request->id_profesional)
                                    ->first();




        if($profesional)
        {

            $lugares_atencion = $profesional->LugaresAtencion()
                                            ->select('lugares_atencion.id', 'lugares_atencion.nombre', 'lugares_atencion.rut', 'lugares_atencion.email', 'lugares_atencion.telefono', 'lugares_atencion.id_direccion')
                                            ->with(['Direccion' => function($query){
                                                $query->select('id', 'direccion', 'numero_dir', 'id_ciudad')
                                                    ->with(['Ciudad' => function($query){
                                                        $query->select('id', 'nombre')->get();
                                                    }])
                                                    ->get();
                                            }])
											->where('estado', 1)
                                            ->get();

            foreach ($lugares_atencion as $key => $lg)
            {
                $profesional_convenios = ProfesionalConvenio::where('id_profesional', $request->id_profesional)->where('id_lugar_atencion', $lg->id)->first();
                if($profesional_convenios)
                {
                    $lugares_atencion[$key]->convenio = $profesional_convenios;
                }
                else
                {
                    $lugares_atencion[$key]->convenio = '';
                }
            }
            $array_rut = explode('-',$profesional->rut);
            $nombre_imagen = asset('images/iconos/usuario_profesional.svg');

            if(file_exists(public_path('images/img_perfil/'.$array_rut[0].'.png')))
            {
                $nombre_imagen = asset('images/img_perfil/'.$array_rut[0].'.png');
            }
            $profesional->img_profesional = $nombre_imagen;

            $datos['estado'] = 1;
            $datos['msj'] = 'Registros';
            $datos['profesional'] = $profesional;
            $datos['lugares_atencion'] = $lugares_atencion;
            $datos['array_rut'] = $array_rut[0];



        }
        else
        {
            $datos['estado'] = 0;
            $datos['msj'] = 'Informacion Profesional no encotnrada';
        }

        return $datos;

    }


    // listar lugares de atenciones
    /**
     * busqueda de lugares de  atencion por profesional
     * @param Request $requst
     *
     * @return array()
     */
    public function lugaresAtencionProfesionalBuscador(Request $request){

        $datos = array();

        $profesional = Profesional::where('id', $request->id_profesional)->first();
        $lugares_atencion = collect();

        if ($profesional) {
            $lugares_atencion = $profesional->LugaresAtencion()
                                ->with(['Direccion' => function ($query) {
                                    $query->with('Ciudad');
                                }])
                                ->where('estado',1)
                                ->get();
        }

        if($lugares_atencion->count() > 0)
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

        return $datos;
    }

    // carga de dias laborales profesional por profesional y lugar atenciones (dias laborables)
    /**
     * busqueda de dias laborables del profesional en un lugar de atencion
     * @param Request $requst
     *
     * @return array()
     */
    public function diasLaboralesProfesionaLugarAtencionBuscador(Request $request){

        $datos = array();
        $horario = ProfesionalHorario::where('id_profesional', $request->id_profesional)
                                        ->where('id_lugar_atencion', $request->lugar_atencion)
                                        ->tipoAgenda($request->tipo_agenda)
                                        ->orderBy('dia', 'ASC')
                                        ->get();

        // Los días se guardan como 0 = domingo … 6 = sábado (el calendario usa la misma numeración)
        $dias_laborales = $horario->flatMap(function ($hor) {
            return \App\Services\AgendaDisponibilidad::diasDelTurno($hor->dia);
        })->unique()->sort()->values()->all();
        $dias_no_laborales = array_values(array_diff([0, 1, 2, 3, 4, 5, 6], $dias_laborales));

        $horario_agenda_laboral = implode(',', $dias_laborales);
        $horario_agenda_no_laboral = implode(',', $dias_no_laborales);

        $datos['estado'] = 1;
        $datos['msj'] = 'registros';
        $datos['registros'] = array(
            'horario_agenda_laboral' => $horario_agenda_laboral,
            'horario_agenda_no_laboral' => $horario_agenda_no_laboral,
            // rango de horas de cada bloque de días, para mostrarlo como información al tutor
            'horarios' => $horario->map(function ($hor) {
                return array(
                    'dia' => $hor->dia,
                    'hora_inicio' => $hor->hora_inicio,
                    'hora_termino' => $hor->hora_termino,
                );
            })->values(),
        );

        return $datos;
    }


    // carga de horas disponibles para profesional por profesional lugar atencion y dia (horas disponibles)
    /**
     * busqueda de horas disponibles del profesional en una fecha especifica en un lugar de atencion
     * @param Request $requst
     *
     * @return array()
     */
    public function horasDisponiblesProfesionalLugarAtencionBuscador(Request $request){
        // id_profesional
        // id_lugar_atencion
        // dia
        $texto_dia = ['Domingo', 'Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sabado'];
        $texto_mes = ['', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];

        $datos = array();

        if (empty($request->dia) || strtotime($request->dia) === false) {
            $datos['estado'] = 0;
            $datos['msj'] = 'sin registros';
            return $datos;
        }

        // Todos los turnos del día, sin horas pasadas, bloqueadas ni ocupadas en ningún lugar.
        // Son los mismos criterios con que se guarda la reserva (ver AgendaDisponibilidad).
        $agenda = app(\App\Services\AgendaDisponibilidad::class);
        $dia = \Carbon\Carbon::parse($request->dia);
        $turnos_del_dia = $agenda->turnos((int) $request->id_profesional, (int) $request->id_lugar_atencion, $request->tipo_agenda)
            ->filter(function ($turno) use ($dia) {
                return in_array($dia->dayOfWeek, \App\Services\AgendaDisponibilidad::diasDelTurno($turno->dia), true);
            });

        if ($turnos_del_dia->isNotEmpty())
        {
            $bloques = $agenda->bloquesLibres((int) $request->id_profesional, (int) $request->id_lugar_atencion, $dia->toDateString(), $request->tipo_agenda);

            $array_bloques = array_map(function ($bloque) use ($dia) {
                return array(
                    'dia' => $dia->toDateString(),
                    'hora' => $bloque['hora'],
                    'fecha_hora' => $dia->toDateString().' '.$bloque['hora'],
                );
            }, $bloques);

            $datos['estado'] = count($array_bloques) > 0 ? 1 : 0;
            $datos['msj'] = count($array_bloques) > 0 ? 'registros' : 'sin horas disponibles';
            $datos['registros'] = $array_bloques;
            $datos['text_fecha'] = $texto_dia[$dia->dayOfWeek].' '. $dia->format('d').' '.$texto_mes[(int) $dia->format('m')];

        }
        else
        {
            $datos['estado'] = 0;
            $datos['msj'] = 'sin registros';
            $datos['profesional_horario'] = null;
        }

        return $datos;
    }

    // carga de horas para profesional por profesional lugar atencion y dia (horas)
    /**
     * busqueda de horas del profesional en una fecha especifica en un lugar de atencion
     * @param Request $requst
     *
     * @return array()
     */
    public function horasProfesionalLugarAtencionBuscador(Request $request){
        // id_profesional
        // id_lugar_atencion
        // dia
        $texto_dia = ['Domingo', 'Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sabado'];
        $texto_mes = ['', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];

        $datos = array();

        // 0 = domingo … 6 = sábado, igual que se guardan los turnos
        $dia_semana = (int) date('w',strtotime($request->dia));

        $profesional_horarios = ProfesionalHorario::where('id_profesional',$request->id_profesional)
                                                ->where('id_lugar_atencion',$request->id_lugar_atencion)
                                                ->whereRaw('FIND_IN_SET(?, REPLACE(dia, " ", ""))', [$dia_semana])
                                                ->orderBy('dia', 'ASC')
                                                ->first();
        if($profesional_horarios)
        {
            $array_bloques = array();

            $hora_inicio_turno  = $request->dia.' '.$profesional_horarios->hora_inicio;
            $hora_termino_turno  = $request->dia.' '.$profesional_horarios->hora_termino;

            /** duracion de consulta en minutos */
            $duracion =  $profesional_horarios->duracion_consulta;
            $array_duracion = explode(':', $duracion);
            $duracion_total = 0;

            if((int)$array_duracion[0]>0)
                $duracion_total += (int)$array_duracion[0]*60;
            if((int)$array_duracion[1]>0)
                $duracion_total += (int)$array_duracion[1];

            for ($hora=strtotime($hora_inicio_turno); $hora <= strtotime( '+'. $duracion_total.' minute', strtotime($hora_termino_turno) ); $hora = strtotime('+'. $duracion_total.' minute',$hora))
            {
                $hora_medica = HoraMedica::where('fecha_consulta', date('Y-m-d',$hora))->where('hora_inicio',date('H:i:s',$hora))->first();
                if($hora_medica)
                {
                    $cantidad_hora_medica = HoraMedica::where('fecha_consulta', date('Y-m-d',$hora))->where('hora_inicio',date('H:i:s',$hora))->count();
                    $reserva = 0;
                    if($cantidad_hora_medica > 1)
                    {
                        $reserva = 1;
                    }

                    // con reserva
                    $array_bloques[] = array(
                                            'dia' => date('Y-m-d',$hora),
                                            'hora' => date('H:i:s',$hora),
                                            'fecha_hora' => date('Y-m-d H:i:s',$hora),
                                            'reserva' => $reserva
                                        );
                }
                else
                {
                    // sin reserva
                    $array_bloques[] = array(
                                            'dia' => date('Y-m-d',$hora),
                                            'hora' => date('H:i:s',$hora),
                                            'fecha_hora' => date('Y-m-d H:i:s',$hora),
                                            'reserva' => '0'
                                        );
                }
            }

            $datos['estado'] = 1;
            $datos['msj'] = 'registros';
            $datos['registros'] = $array_bloques;
            $datos['text_fecha'] = $texto_dia[(int)$dia_semana].' '. date('d',strtotime($request->dia)).' '.$texto_mes[(int)date('m',strtotime($request->dia))];

        }
        else
        {
            $datos['estado'] = 0;
            $datos['msj'] = 'sin registros';
            $datos['profesional_horario'] = $profesional_horarios;
        }

        return $datos;
    }

    public function getPersona(Request $request)
    {
        $datos = array();
        $registro = Personas::select('rut', 'nombre1', 'appaterno', 'apmaterno')->where('rut', $request->rut)->first();
        if($registro)
        {
            $datos['estado'] = 1;
            $datos['msj'] = 'registros';
            $datos['registros'] = $registro;
        }
        else
        {
            $datos['estado'] = 0;
            $datos['msj'] = 'sin registros';
        }
        return $datos;
    }

    public function invitacionProfesionalConfirmacionRechazo(Request $request)
    {
        // var_dump($request->all);
        $mensaje ='';
        $valido = 1;
        if(empty($request->inv)){
            $valido = 0;
            $mensaje .= 'Problema al manejar invitacion';
        }
        if(empty($request->tpi))
        {
            $valido = 0;
            $mensaje .= 'Problema al manejar el tipo de proceso de la invitacion';
        }

        if($valido == 1)
        {
            $id_invitacion = $request->inv;
            $tipo_proceso = $request->tpi;

            $invitacion = Invitacion::where('id',$id_invitacion)->first();

            if($invitacion->procesado == 0)
            {
                switch ($tipo_proceso) {
                    case '1':// acepto
                        if($invitacion->id_user_invitado)
                        {
                            $profesional = Profesional::where('id_usuario',$invitacion->id_user_invitado)->first();
                            if($profesional)
                            {
                                $buscar_prof_lugar = ProfesionalesLugaresAtencion::where('id_profesional',$profesional->id)->where('id_lugar_atencion',$invitacion->id_lugar_atencion)->first();
                                if($buscar_prof_lugar)
                                {
                                    $invitacion->procesado = 1;
                                    $invitacion->fecha_procesado = date('Y-m-d H:i:s');
                                    $invitacion->fecha_aprobacion = date('Y-m-d H:i:s');
                                    $invitacion->estado = 1;
                                    if($invitacion->save())
                                    {
                                        $mensaje .= 'Profesional '.$profesional->nombre.' '.$profesional->apellido_uno.' '.$profesional->apellido_dos.', ya se encuentra como integrante de la Intitucion '.$invitacion->LugarAtencion()->first()->nombre.'. <br/>';
                                    }
                                    else
                                    {
                                        $mensaje .= 'Profesional '.$profesional->nombre.' '.$profesional->apellido_uno.' '.$profesional->apellido_dos.', ya se encuentra como integrante de la Intitucion '.$invitacion->LugarAtencion()->first()->nombre.'. <br/>';
                                        $mensaje .= '<br/>';
                                        $mensaje .= 'Invitacion no actualizada. <br/>';
                                    }

                                }
                                else
                                {
                                    /** profesional existente */
                                    $reg_prof_lugar = new ProfesionalesLugaresAtencion();
                                    $reg_prof_lugar->id_profesional = $profesional->id;
                                    $reg_prof_lugar->id_lugar_atencion = $invitacion->id_lugar_atencion;
                                    $reg_prof_lugar->estado = 1;
                                    if($reg_prof_lugar->save())
                                    {
                                        $invitacion->procesado = 1;
                                        $invitacion->fecha_procesado = date('Y-m-d H:i:s');
                                        $invitacion->fecha_aprobacion = date('Y-m-d H:i:s');
                                        $invitacion->estado = 1;
                                        if($invitacion->save())
                                        {
                                            $mensaje .= 'Profesional '.$profesional->nombre.' '.$profesional->apellido_uno.' '.$profesional->apellido_dos.', ha sido confirmado como integrante de la Intitucion '.$invitacion->LugarAtencion()->first()->nombre.'. <br/>';
                                        }
                                        else
                                        {
                                            $mensaje .= 'Profesional '.$profesional->nombre.' '.$profesional->apellido_uno.' '.$profesional->apellido_dos.', ha sido confirmado como integrante de la Intitucion '.$invitacion->LugarAtencion()->first()->nombre.'. <br/>';
                                            $mensaje .= 'Invitacion no actualizada. <br/>';
                                        }
                                    }
                                    else
                                    {
                                        $mensaje .= 'Profesional '.$profesional->nombre.' '.$profesional->apellido_uno.' '.$profesional->apellido_dos.', se ha presentado un problema al confirmar como integrante de la Intitucion '.$invitacion->LugarAtencion()->first()->nombre.'. <br/>';
                                    }
                                }
                            }
                            else
                            {

                                $user = User::find($invitacion->id_user_invitado);
                                $temp_pass = rand(1111,9999);
                                $user->password = Hash::make($temp_pass);
                                if ($user->save())
                                {
                                    $mensaje .='Problema con Activar su invitación, intente nuevamente.<br/>';
                                    /** envio de correo de confirmacion  */
                                    $blade = 'profesional_usuario_creado';
                                    $to = array(
                                            array('email' => $invitacion->email,'name' => $invitacion->nombre.' '.$invitacion->apellido_uno.' '.$invitacion->apellido_dos),
                                        );
                                    $cc = array();
                                    $bcc = array();
                                    $asunto = 'VET-SDI - Bienvenido!';
                                    $body = array(
                                        'nombre'=>$invitacion->nombre.' '.$invitacion->apellido_uno.' '.$invitacion->apellido_dos,
                                        'user' => $invitacion->email,
                                        'pass' => $temp_pass,
                                    );
                                    $archivo = '';/** pendiente */
                                    $id_institucion = '';

                                    $result_mail =  SendMailController::envioCorreo($blade, $to, $cc, $bcc, $asunto, $body, $archivo, $id_institucion);

                                    if($result_mail['estado'])
                                    {
                                        $mensaje .= 'Usuario creado con exito.<br/>';
                                        $mensaje .= 'Recibirá un correo de Bienvenida con la información de acceso al sistema. <br/>';
                                    }
                                    else
                                    {
                                        $mensaje .= 'Usuario creado con exito.<br/>';
                                        $mensaje .= 'Correo de Bienvenida con la información de acceso al sistema con problema para envío. <br/>';
                                    }
                                }

                            }
                        }
                        else
                        {
                            /** profesional nuevo  */
                            $user = User::where('email', $invitacion->email)->first();
                            if($user == NULL)
                            {
                                $user = new User();
                                $user->name = $invitacion->nombre.' '.$invitacion->apellido_uno.' '.$invitacion->apellido_dos;
                                $user->email = $invitacion->email;
                                $temp_pass = rand(1111,9999);
                                $user->password = Hash::make($temp_pass);
                                if ($user->save()) {
                                    $user->assignRole('Profesional');
                                    $invitacion->procesado = 1;
                                    $invitacion->fecha_procesado = date('Y-m-d H:i:s');
                                    $invitacion->fecha_aprobacion = date('Y-m-d H:i:s');
                                    $invitacion->id_user_invitado = $user->id;
                                    $invitacion->estado = 0;
                                    if($invitacion->save())
                                    {
                                        $mensaje .= 'Profesional '.$invitacion->nombre.' '.$invitacion->apellido_uno.' '.$invitacion->apellido_dos.', ha sido confirmado como integrante de la Intitucion '.$invitacion->LugarAtencion()->first()->nombre.'. <br/>';


                                        /** envio de correo de confirmacion  */
                                        $blade = 'profesional_usuario_creado';
                                        $to = array(
                                                array('email' => $invitacion->email,'name' => $invitacion->nombre.' '.$invitacion->apellido_uno.' '.$invitacion->apellido_dos),
                                            );
                                        $cc = array();
                                        $bcc = array();
                                        $asunto = 'VET-SDI - Bienvenido!';
                                        $body = array(
                                            'nombre'=>$invitacion->nombre.' '.$invitacion->apellido_uno.' '.$invitacion->apellido_dos,
                                            'user' => $invitacion->email,
                                            'pass' => $temp_pass,
                                        );
                                        $archivo = '';/** pendiente */
                                        $id_institucion = '';

                                        $result_mail =  SendMailController::envioCorreo($blade, $to, $cc, $bcc, $asunto, $body, $archivo, $id_institucion);

                                        if($result_mail['estado'])
                                        {
                                            $mensaje .= 'Usuario creado con exito.<br/>';
                                            $mensaje .= 'Recibirá un correo de Bienvenida con la información de acceso al sistema. <br/>';
                                        }
                                        else
                                        {
                                            $mensaje .= 'Usuario creado con exito.<br/>';
                                            $mensaje .= 'Correo de Bienvenida con la información de acceso al sistema con problema para envío. <br/>';
                                        }

                                        $mensaje .= 'Deberá completar su perfil en el escritorio asignado. <br/>';
                                    }
                                    else
                                    {
                                        $mensaje .= 'Profesional '.$invitacion->nombre.' '.$invitacion->apellido_uno.' '.$invitacion->apellido_dos.', ha sido confirmado como integrante de la Intitucion '.$invitacion->LugarAtencion()->first()->nombre.'. <br/>';
                                        $mensaje .= 'Usuario creado con exito.<br/>';
                                        $mensaje .= 'Recibirá un correo con la información de acceso al sistema. <br/>';
                                        $mensaje .= 'Deberá completar su perfil en el escritorio asignado. <br/>';
                                        $mensaje .= 'Invitacion no actualizada. <br/>';
                                    }
                                }
                                else
                                {
                                    $mensaje .= 'Se presento un problema al generar su Usuario, intente nuevamente. <br/>';
                                }
                            }
                            else
                            {
                                $mensaje .= 'El correo "'.$invitacion->email.'" ya esta siendo utilizado o su usuario ya ha sido creado. <br/>';
                            }

                        }

                        break;
                    case '2': // rechazo
                            $invitacion->procesado = 1;
                            $invitacion->fecha_procesado = date('Y-m-d H:i:s');
                            $invitacion->fecha_rechazo = date('Y-m-d H:i:s');
                            $invitacion->estado = 2;
                            if($invitacion->save())
                            {
                                $mensaje .= 'Profesional '.$invitacion->nombre.' '.$invitacion->apellido_uno.' '.$invitacion->apellido_dos.', ha RECHAZADO la invitacion para ser integrante de la Intitucion '.$invitacion->LugarAtencion()->first()->nombre.'. <br/>';
                            }
                            else
                            {
                                $mensaje .= 'Profesional '.$invitacion->nombre.' '.$invitacion->apellido_uno.' '.$invitacion->apellido_dos.', ha RECHAZADO la invitacion para ser integrante de la Intitucion '.$invitacion->LugarAtencion()->first()->nombre.'. <br/>';
                                $mensaje .= 'Invitacion no actualizada. <br/>';
                            }
                            /** CORREO NOTIFICACION AL CENTRO MEDICO DE RECHAZO DE INVITACION */
                            /** CORREO AL PROFESIONAL INDICANDO RECHAZO A LA INVITACION */

                        break;

                    default:
                            $mensaje .= 'Proceso solicitado no aceptado. <br/>';
                        break;
                }
            }
            else
            {
                $mensaje .= 'Invitacion Ya se encuentra procesada. <br/>';
            }
        }
        return view('app.general.validaciones.invitacion')->with([
                'mensaje' => $mensaje,
            ]);

    }

    public function invitacionConvenioProfesionalConfirmacionRechazo(Request $request)
    {
        // var_dump($request->all);
        $mensaje ='';
        $valido = 1;
        if(empty($request->inv)){
            $valido = 0;
            $mensaje .= 'Problema al manejar invitacion';
        }
        if(empty($request->tpi))
        {
            $valido = 0;
            $mensaje .= 'Problema al manejar el tipo de proceso de la invitacion';
        }

        if($valido == 1)
        {
            $token = $request->inv;
            $tipo_proceso = $request->tpi;

            $invitacion = Invitacion::where('token',$token)->first();

            if($invitacion->procesado == 0)
            {
                switch ($tipo_proceso) {
                    case '1':// acepto
                        if($invitacion->id_user_invitado)
                        {
                            $profesional = Profesional::where('id_usuario',$invitacion->id_user_invitado)->first();
                            if($profesional)
                            {
                                $buscar_prof_lugar = ProfesionalesLugaresAtencion::where('id_profesional',$profesional->id)->where('id_lugar_atencion',$invitacion->id_lugar_atencion)->first();
                                if($buscar_prof_lugar)
                                {
                                    $invitacion->procesado = 1;
                                    $invitacion->fecha_procesado = date('Y-m-d H:i:s');
                                    $invitacion->fecha_aprobacion = date('Y-m-d H:i:s');
                                    $invitacion->estado = 1;
                                    if($invitacion->save())
                                    {
                                        $mensaje .= 'Profesional '.$profesional->nombre.' '.$profesional->apellido_uno.' '.$profesional->apellido_dos.', ya se encuentra como integrante de la Intitucion '.$invitacion->LugarAtencion()->first()->nombre.'. <br/>';

                                        /** CONFIRMAR CONVENIO */
                                        $filtro_cov = array();
                                        $filtro_cov[] = array('id_invitacion', $invitacion->id);
                                        $filtro_cov[] = array('id_profesional', $profesional->id);
                                        $result_prof_inst_conv = ProfesionalInstitucionConvenio::where($filtro_cov)->first();
                                        if($result_prof_inst_conv)
                                        {
                                            $result_prof_inst_conv_temp = ProfesionalInstitucionConvenio::find($result_prof_inst_conv->id);
                                            if($result_prof_inst_conv_temp)
                                            {
                                                $result_prof_inst_conv_temp->fecha_confirmacion = date('Y-m-d H:i:s');
                                                $result_prof_inst_conv_temp->estado = 2;

                                                if($result_prof_inst_conv_temp->save())
                                                {
                                                    $mensaje .= 'Convenio Aprobado con exito.<br/>';
                                                }
                                                else
                                                {
                                                    $mensaje .= 'Problema con la Confirmacion del Convenio.<br/>';
                                                }
                                            }
                                            else
                                            {
                                                $mensaje .= 'Convenio No Encontrado.<br/>';
                                            }
                                        }
                                        else
                                        {
                                            $mensaje .= 'Convenio No Encontrado.<br/>';
                                        }
                                    }
                                    else
                                    {
                                        $mensaje .= 'Profesional '.$profesional->nombre.' '.$profesional->apellido_uno.' '.$profesional->apellido_dos.', ya se encuentra como integrante de la Intitucion '.$invitacion->LugarAtencion()->first()->nombre.'. <br/>';
                                        $mensaje .= '<br/>';
                                        $mensaje .= 'Invitacion no actualizada. <br/>';
                                    }

                                }
                                else
                                {
                                    /** profesional existente */
                                    $reg_prof_lugar = new ProfesionalesLugaresAtencion();
                                    $reg_prof_lugar->id_profesional = $profesional->id;
                                    $reg_prof_lugar->id_lugar_atencion = $invitacion->id_lugar_atencion;
                                    $reg_prof_lugar->estado = 1;
                                    if($reg_prof_lugar->save())
                                    {
                                        $invitacion->procesado = 1;
                                        $invitacion->fecha_procesado = date('Y-m-d H:i:s');
                                        $invitacion->fecha_aprobacion = date('Y-m-d H:i:s');
                                        $invitacion->estado = 1;
                                        if($invitacion->save())
                                        {
                                            $mensaje .= 'Profesional '.$profesional->nombre.' '.$profesional->apellido_uno.' '.$profesional->apellido_dos.', ha sido confirmado como integrante de la Intitucion '.$invitacion->LugarAtencion()->first()->nombre.'. <br/>';

                                            /** CONFIRMAR CONVENIO */
                                            $filtro_cov = array();
                                            $filtro_cov[] = array('id_invitacion', $invitacion->id);
                                            $filtro_cov[] = array('id_profesional', $profesional->id);
                                            $result_prof_inst_conv = ProfesionalInstitucionConvenio::where($filtro_cov)->first();
                                            if($result_prof_inst_conv)
                                            {
                                                $result_prof_inst_conv_temp = ProfesionalInstitucionConvenio::find($result_prof_inst_conv->id);
                                                if($result_prof_inst_conv_temp)
                                                {
                                                    $result_prof_inst_conv_temp->fecha_confirmacion = date('Y-m-d H:i:s');
                                                    $result_prof_inst_conv_temp->estado = 2;

                                                    if($result_prof_inst_conv_temp->save())
                                                    {
                                                        $mensaje .= 'Convenio Aprobado con exito.<br/>';
                                                    }
                                                    else
                                                    {
                                                        $mensaje .= 'Problema con la Confirmacion del Convenio.<br/>';
                                                    }
                                                }
                                                else
                                                {
                                                    $mensaje .= 'Convenio No Encontrado.<br/>';
                                                }
                                            }
                                            else
                                            {
                                                $mensaje .= 'Convenio No Encontrado.<br/>';
                                            }
                                        }
                                        else
                                        {
                                            $mensaje .= 'Profesional '.$profesional->nombre.' '.$profesional->apellido_uno.' '.$profesional->apellido_dos.', ha sido confirmado como integrante de la Intitucion '.$invitacion->LugarAtencion()->first()->nombre.'. <br/>';
                                            $mensaje .= 'Invitacion no actualizada. <br/>';
                                        }
                                    }
                                    else
                                    {
                                        $mensaje .= 'Profesional '.$profesional->nombre.' '.$profesional->apellido_uno.' '.$profesional->apellido_dos.', se ha presentado un problema al confirmar como integrante de la Intitucion '.$invitacion->LugarAtencion()->first()->nombre.'. <br/>';
                                    }
                                }
                            }
                            else
                            {

                                $user = User::find($invitacion->id_user_invitado);
                                $temp_pass = rand(1111,9999);
                                $user->password = Hash::make($temp_pass);
                                if ($user->save())
                                {
                                    $mensaje .= 'Usuario creado con exito.<br/>';
                                    /** envio de correo de confirmacion  */
                                    $blade = 'profesional_usuario_creado';
                                    $to = array(
                                            array('email' => $invitacion->email,'name' => $invitacion->nombre.' '.$invitacion->apellido_uno.' '.$invitacion->apellido_dos),
                                        );
                                    $cc = array();
                                    $bcc = array();
                                    $asunto = 'VET-SDI - Bienvenido!';
                                    $body = array(
                                        'nombre'=> $invitacion->nombre.' '.$invitacion->apellido_uno.' '.$invitacion->apellido_dos,
                                        'user' => $invitacion->email,
                                        'pass' => $temp_pass,
                                    );
                                    $archivo = '';/** pendiente */
                                    $id_institucion = '';

                                    $result_mail =  SendMailController::envioCorreo($blade, $to, $cc, $bcc, $asunto, $body, $archivo, $id_institucion);

                                    if($result_mail['estado'])
                                    {
                                        $mensaje .= 'Recibirá un correo de Bienvenida con la información de acceso al sistema. <br/>';
                                    }
                                    else
                                    {
                                        $mensaje .= 'Correo de Bienvenida con la información de acceso al sistema con problema para envío. <br/>';
                                    }

                                    /** CONFIRMAR CONVENIO */
                                    $filtro_cov = array();
                                    $filtro_cov[] = array('id_invitacion', $invitacion->id);
                                    $result_prof_inst_conv = ProfesionalInstitucionConvenio::where($filtro_cov)->first();
                                    if($result_prof_inst_conv)
                                    {
                                        $result_prof_inst_conv_temp = ProfesionalInstitucionConvenio::find($result_prof_inst_conv->id);
                                        if($result_prof_inst_conv_temp)
                                        {
                                            $result_prof_inst_conv_temp->fecha_confirmacion = date('Y-m-d H:i:s');
                                            $result_prof_inst_conv_temp->estado = 2;

                                            if($result_prof_inst_conv_temp->save())
                                            {
                                                $mensaje .= 'Convenio Aprobado con exito.<br/>';
                                            }
                                            else
                                            {
                                                $mensaje .= 'Problema con la Confirmacion del Convenio.<br/>';
                                            }
                                        }
                                        else
                                        {
                                            $mensaje .= 'Convenio No Encontrado.<br/>';
                                        }
                                    }
                                    else
                                    {
                                        $mensaje .= 'Convenio No Encontrado.<br/>';
                                    }
                                }
                                else
                                {
                                    $mensaje .= 'Problema al crear Usuario.<br/>';
                                    $mensaje .= 'Problema con Activar su invitación, intente nuevamente.<br/>';
                                }

                            }
                        }
                        else
                        {
                            /** profesional nuevo  */
                            $user = User::where('email', $invitacion->email)->first();
                            if($user == NULL)
                            {
                                $user = new User();
                                $user->name = $invitacion->nombre.' '.$invitacion->apellido_uno.' '.$invitacion->apellido_dos;
                                $user->email = $invitacion->email;
                                $temp_pass = rand(1111,9999);
                                $user->password = Hash::make($temp_pass);
                                if ($user->save()) {
                                    $user->assignRole('Profesional');
                                    $invitacion->procesado = 1;
                                    $invitacion->fecha_procesado = date('Y-m-d H:i:s');
                                    $invitacion->fecha_aprobacion = date('Y-m-d H:i:s');
                                    $invitacion->id_user_invitado = $user->id;
                                    $invitacion->estado = 0;
                                    if($invitacion->save())
                                    {
                                        $mensaje .= 'Profesional '.$invitacion->nombre.' '.$invitacion->apellido_uno.' '.$invitacion->apellido_dos.', ha sido confirmado como integrante de la Intitucion '.$invitacion->LugarAtencion()->first()->nombre.'. <br/>';

                                        /** envio de correo de confirmacion  */
                                        $blade = 'profesional_usuario_creado';
                                        $to = array(
                                                array('email' => $invitacion->email,'name' => $invitacion->nombre.' '.$invitacion->apellido_uno.' '.$invitacion->apellido_dos),
                                            );
                                        $cc = array();
                                        $bcc = array();
                                        $asunto = 'VET-SDI - Bienvenido!';
                                        $body = array(
                                            'nombre'=>$invitacion->nombre.' '.$invitacion->apellido_uno.' '.$invitacion->apellido_dos,
                                            'user' => $invitacion->email,
                                            'pass' => $temp_pass,
                                        );
                                        $archivo = '';/** pendiente */
                                        $id_institucion = '';

                                        $result_mail =  SendMailController::envioCorreo($blade, $to, $cc, $bcc, $asunto, $body, $archivo, $id_institucion);

                                        if($result_mail['estado'])
                                        {
                                            $mensaje .= 'Usuario creado con exito.<br/>';
                                            $mensaje .= 'Recibirá un correo de Bienvenida con la información de acceso al sistema. <br/>';
                                        }
                                        else
                                        {
                                            $mensaje .= 'Usuario creado con exito.<br/>';
                                            $mensaje .= 'Correo de Bienvenida con la información de acceso al sistema con problema para envío. <br/>';
                                        }

                                        $mensaje .= 'Deberá completar su perfil en el escritorio asignado. <br/>';

                                        /** CONFIRMAR CONVENIO */
                                        $filtro_cov = array();
                                        $filtro_cov[] = array('id_invitacion', $invitacion->id);
                                        $result_prof_inst_conv = ProfesionalInstitucionConvenio::where($filtro_cov)->first();
                                        if($result_prof_inst_conv)
                                        {
                                            $result_prof_inst_conv_temp = ProfesionalInstitucionConvenio::find($result_prof_inst_conv->id);
                                            if($result_prof_inst_conv_temp)
                                            {
                                                $result_prof_inst_conv_temp->fecha_confirmacion = date('Y-m-d H:i:s');
                                                $result_prof_inst_conv_temp->estado = 2;

                                                if($result_prof_inst_conv_temp->save())
                                                {
                                                    $mensaje .= 'Convenio Aprobado con exito.<br/>';
                                                }
                                                else
                                                {
                                                    $mensaje .= 'Problema con la Confirmacion del Convenio.<br/>';
                                                }
                                            }
                                            else
                                            {
                                                $mensaje .= 'Convenio No Encontrado.<br/>';
                                            }
                                        }
                                        else
                                        {
                                            $mensaje .= 'Convenio No Encontrado.<br/>';
                                        }
                                    }
                                    else
                                    {
                                        $mensaje .= 'Profesional '.$invitacion->nombre.' '.$invitacion->apellido_uno.' '.$invitacion->apellido_dos.', ha sido confirmado como integrante de la Intitucion '.$invitacion->LugarAtencion()->first()->nombre.'. <br/>';
                                        $mensaje .= 'Usuario creado con exito.<br/>';
                                        $mensaje .= 'Recibirá un correo con la información de acceso al sistema. <br/>';
                                        $mensaje .= 'Deberá completar su perfil en el escritorio asignado. <br/>';
                                        $mensaje .= 'Invitacion no actualizada. <br/>';
                                    }
                                }
                                else
                                {
                                    $mensaje .= 'Se presento un problema al generar su Usuario, intente nuevamente. <br/>';
                                }
                            }
                            else
                            {
                                $mensaje .= 'El correo "'.$invitacion->email.'" ya esta siendo utilizado o su usuario ya ha sido creado. <br/>';
                            }

                        }

                        break;
                    case '2': // rechazo
                            $invitacion->procesado = 1;
                            $invitacion->fecha_procesado = date('Y-m-d H:i:s');
                            $invitacion->fecha_rechazo = date('Y-m-d H:i:s');
                            $invitacion->estado = 2;
                            if($invitacion->save())
                            {
                                $mensaje .= 'Profesional '.$invitacion->nombre.' '.$invitacion->apellido_uno.' '.$invitacion->apellido_dos.', ha RECHAZADO la invitacion para ser integrante de la Intitucion '.$invitacion->LugarAtencion()->first()->nombre.'. <br/>';

                                /** CONFIRMAR CONVENIO */
                                $filtro_cov = array();
                                $filtro_cov[] = array('id_invitacion', $invitacion->id);
                                $result_prof_inst_conv = ProfesionalInstitucionConvenio::where($filtro_cov)->first();
                                if($result_prof_inst_conv)
                                {
                                    $result_prof_inst_conv_temp = ProfesionalInstitucionConvenio::find($result_prof_inst_conv->id);
                                    if($result_prof_inst_conv_temp)
                                    {
                                        $result_prof_inst_conv_temp->fecha_rechazo = date('Y-m-d H:i:s');
                                        $result_prof_inst_conv_temp->estado = 3;

                                        if($result_prof_inst_conv_temp->save())
                                        {
                                            $mensaje .= 'Convenio Rechazado con exito.<br/>';
                                        }
                                        else
                                        {
                                            $mensaje .= 'Problema con la Confirmacion del Convenio.<br/>';
                                        }
                                    }
                                    else
                                    {
                                        $mensaje .= 'Convenio No Encontrado.<br/>';
                                    }
                                }
                                else
                                {
                                    $mensaje .= 'Convenio No Encontrado.<br/>';
                                }
                            }
                            else
                            {
                                $mensaje .= 'Profesional '.$invitacion->nombre.' '.$invitacion->apellido_uno.' '.$invitacion->apellido_dos.', ha RECHAZADO la invitacion para ser integrante de la Intitucion '.$invitacion->LugarAtencion()->first()->nombre.'. <br/>';
                                $mensaje .= 'Invitacion no actualizada. <br/>';
                            }
                            /** CORREO NOTIFICACION AL CENTRO MEDICO DE RECHAZO DE INVITACION */
                            /** CORREO AL PROFESIONAL INDICANDO RECHAZO A LA INVITACION */

                        break;

                    default:
                            $mensaje .= 'Proceso solicitado no aceptado. <br/>';
                        break;
                }
            }
            else
            {
                $mensaje .= 'Invitacion Ya se encuentra procesada. <br/>';
            }
        }
        return view('app.general.validaciones.invitacion')->with([
                'mensaje' => $mensaje,
            ]);

    }

    /** especialidad */
    public function cargar_especialidad(Request $request)
    {
        $datos = array();
        $filtro = array();
        $filtro[] = array('estado',1);
        $registros = Especialidad::select('id', 'nombre')->where($filtro)->get();

        if($registros)
        {
            $datos['estado'] = 1;
            $datos['msj'] = 'registro';
            $datos['registros'] = $registros;
        }
        else
        {
            $datos['estado'] = 0;
            $datos['msj'] = 'sin registro';
        }
        return $datos;
    }

    /** tipo especialidad */
    public function cargar_tipo_especialidad(Request $request)
    {
        $datos = array();
        $filtro = array();
        $filtro[] = array('estado',1);
        if(!empty($request->id_especialidad))
            $filtro[] = array('id_especialidad',$request->id_especialidad);

        $registros = TipoEspecialidad::select('id', 'nombre','id_especialidad')->where($filtro)->get();

        if($registros)
        {
            $datos['estado'] = 1;
            $datos['msj'] = 'registro';
            $datos['registros'] = $registros;
        }
        else
        {
            $datos['estado'] = 0;
            $datos['msj'] = 'sin registro';
        }
        return $datos;
    }

    /** sub tipo especialidad */
    public function cargar_sub_tipo_especialidad(Request $request)
    {

        $datos = array();
        $filtro = array();
        $filtro[] = array('estado',1);

        if(!empty($request->id_tipo_especialidad))
            $filtro[] = array('id_tipo_especialidad',$request->id_tipo_especialidad);

        $registros = SubTipoEspecialidad::select('id', 'nombre','id_tipo_especialidad')->where($filtro)->get();

        if($registros)
        {
            $datos['estado'] = 1;
            $datos['msj'] = 'registro';
            $datos['registros'] = $registros;
        }
        else
        {
            $datos['estado'] = 0;
            $datos['msj'] = 'sin registro';
        }
        return $datos;
    }

    public function mensaje($id){
        $mensaje = Mensajes::find($id);
        $mensaje->estado = 0;
        $mensaje->update();
        $mensaje->datos_mensaje = json_decode($mensaje->datos_mensaje);
        $pc = new EscritorioProfesional;
        $mensajes = $pc->dame_mensajes($mensaje->id_receptor);
        $mensaje->datos_mensaje->emisor = Profesional::find($mensaje->id_usuario);
        $mensaje->datos_mensaje->receptor = Profesional::find($mensaje->id_receptor);
        foreach($mensajes as $m){
            $m->emisor = User::find($m->id_usuario);
        }
        return view('app.general.mensajes.profesional')->with([
            'mensaje' => $mensaje->datos_mensaje,
            'mensajes' => $mensajes,
        ]);
    }

    public function mensajeJson($id)
    {
        $mensaje = Mensajes::findOrFail($id);
        $mensaje->estado = 0;
        $mensaje->update();

        $datos = json_decode($mensaje->datos_mensaje);
        $datos->emisor = User::find($mensaje->id_usuario);
        $datos->receptor = Profesional::find($mensaje->id_receptor);

        return response()->json($datos);
    }


}
