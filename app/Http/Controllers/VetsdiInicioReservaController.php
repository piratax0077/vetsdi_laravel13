<?php

namespace App\Http\Controllers;

use App\Models\Ciudad;
use App\Models\EspecieMascota;
use App\Models\HoraMedica;
use App\Models\LugarAtencion;
use App\Models\Mascota;
use App\Models\Paciente;
use App\Models\Prevision;
use App\Models\Profesional;
use App\Models\ProfesionalHorario;
use App\Models\Region;
use App\Services\AccesoAgendaVeterinaria;
use App\Services\AgendaDisponibilidad;
use App\Support\LugarAtencionInstitucionResolver;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class VetsdiInicioReservaController extends Controller
{
    public function catalogo()
    {
        $catalogoVeterinario = $this->getVeterinarySearchCatalog();

        return response()->json([
            'estado' => 1,
            'regiones' => Region::orderBy('nombre')->get(['id', 'nombre']),
            'areas_veterinarias' => collect($catalogoVeterinario)->map(function (array $area) {
                return [
                    'id' => $area['slug'],
                    'nombre' => $area['nombre'],
                ];
            })->values(),
            'especies' => EspecieMascota::orderBy('nombre')->get(['id', 'nombre']),
        ]);
    }

    public function ciudades(Request $request)
    {
        $request->validate([
            'id_region' => 'required|integer',
        ]);

        return response()->json([
            'estado' => 1,
            'ciudades' => Ciudad::where('id_region', (int) $request->input('id_region'))
                ->orderBy('nombre')
                ->get(['id', 'nombre']),
        ]);
    }

    public function tiposEspecialidad(Request $request)
    {
        $request->validate([
            'area' => 'required|string',
        ]);

        $area = $this->findVeterinaryAreaBySlug((string) $request->input('area'));

        return response()->json([
            'estado' => 1,
            'label_item' => $area['label_item'] ?? 'Servicio',
            'tipos_especialidad' => collect($area['items'] ?? [])->map(function (string $item) {
                return [
                    'id' => $item,
                    'nombre' => $item,
                ];
            })->values(),
        ]);
    }

    public function subtiposEspecialidad(Request $request)
    {
        return response()->json([
            'estado' => 1,
            'subtipos_especialidad' => [],
        ]);
    }

    public function responsable(Request $request)
    {
        $request->validate([
            'rut' => 'required|string|max:20',
        ]);

        $paciente = $this->findPacienteByRut((string) $request->input('rut'));

        if (!$paciente) {
            return response()->json([
                'estado' => 1,
                'encontrado' => false,
                'responsable' => null,
                'mascotas' => [],
            ]);
        }

        // Ruta pública: cualquiera puede escribir un RUT, así que el correo y el teléfono van
        // enmascarados. Al reservar se usan los datos guardados, nunca los que lleguen de la web.
        $mascotas = Mascota::with(['especieMascota', 'razaMascota'])
            ->where('id_responsable', $paciente->id)
            ->seleccionables()
            ->orderBy('nombre')
            ->get()
            ->map(function (Mascota $mascota) {
                return [
                    'id' => $mascota->id,
                    'nombre' => $mascota->nombre,
                    'especie_id' => $mascota->especie_id ?: $mascota->especie,
                    'especie_nombre' => optional($mascota->especieMascota)->nombre ?: $mascota->otra_especie,
                    'sexo' => $mascota->sexo,
                    'raza_id' => $mascota->raza_id,
                    'raza_nombre' => optional($mascota->razaMascota)->nombre,
                ];
            })
            ->values();

        return response()->json([
            'estado' => 1,
            'encontrado' => true,
            'responsable' => [
                'id' => $paciente->id,
                'rut' => $this->formatRut($paciente->rut),
                'nombres' => $paciente->nombres,
                'apellido_uno' => $paciente->apellido_uno,
                'apellido_dos' => $paciente->apellido_dos === 'N/A' ? '' : $paciente->apellido_dos,
                'email' => $this->enmascararCorreo($paciente->email),
                'telefono' => $this->enmascararTelefono($paciente->telefono_uno ?: $paciente->telefono_dos),
            ],
            'mascotas' => $mascotas,
        ]);
    }

    public function profesionales(Request $request)
    {
        $query = Profesional::query()
            ->with([
                'Especialidad:id,nombre',
                'TipoEspecialidad:id,nombre',
                'SubTipoEspecialidad:id,nombre',
                'Direccion:id,direccion,numero_dir,id_ciudad',
                'Direccion.Ciudad:id,nombre,id_region',
                'Direccion.Ciudad.Region:id,nombre',
                'LugaresAtencion' => function ($relation) {
                    $relation->select('lugares_atencion.id', 'lugares_atencion.nombre', 'lugares_atencion.id_direccion');
                },
                'LugaresAtencion.Direccion:id,direccion,numero_dir,id_ciudad',
                'LugaresAtencion.Direccion.Ciudad:id,nombre,id_region',
                'LugaresAtencion.Direccion.Ciudad.Region:id,nombre',
            ]);

        // La ubicación se busca en los lugares de atención (donde el profesional
        // atiende), no en su dirección personal. Quien no tiene lugar de atención
        // no tiene agenda que reservar, así que no se lista.
        if ($request->filled('id_ciudad')) {
            $query->whereHas('LugaresAtencion.Direccion', function ($relation) use ($request) {
                $relation->where('id_ciudad', (int) $request->input('id_ciudad'));
            });
        } elseif ($request->filled('id_region')) {
            $query->whereHas('LugaresAtencion.Direccion.Ciudad', function ($relation) use ($request) {
                $relation->where('id_region', (int) $request->input('id_region'));
            });
        } else {
            $query->has('LugaresAtencion');
        }

        if ($request->filled('q')) {
            $term = mb_strtolower(trim((string) $request->input('q')));
            $query->where(function ($nameQuery) use ($term) {
                $nameQuery->whereRaw('LOWER(nombre) LIKE ?', ["%{$term}%"])
                    ->orWhereRaw('LOWER(apellido_uno) LIKE ?', ["%{$term}%"])
                    ->orWhereRaw('LOWER(apellido_dos) LIKE ?', ["%{$term}%"])
                    ->orWhereRaw('LOWER(CONCAT(nombre, " ", apellido_uno, " ", apellido_dos)) LIKE ?', ["%{$term}%"]);
            });
        }

        $profesionales = $query->orderBy('apellido_uno')->orderBy('nombre')->limit(80)->get();

        $resultados = $profesionales->map(function (Profesional $profesional) use ($request) {
            $rutBase = explode('-', (string) $profesional->rut)[0] ?? '';
            $imagen = asset('images/iconos/usuario_profesional.svg');

            if ($rutBase !== '' && file_exists(public_path('images/img_perfil/'.$rutBase.'.png'))) {
                $imagen = asset('images/img_perfil/'.$rutBase.'.png');
            }

            $lugares = $profesional->LugaresAtencion->map(function (LugarAtencion $lugar) {
                $direccion = $lugar->Direccion;
                $ciudad = optional($direccion)->Ciudad;
                $region = optional($ciudad)->Region;

                return [
                    'id' => $lugar->id,
                    'nombre' => $lugar->nombre,
                    'direccion' => trim(
                        collect([
                            optional($direccion)->direccion,
                            optional($direccion)->numero_dir,
                            optional($ciudad)->nombre,
                            optional($region)->nombre,
                        ])->filter()->implode(', ')
                    ),
                    'ciudad' => optional($ciudad)->nombre,
                    'region' => optional($region)->nombre,
                    'ciudad_id' => optional($ciudad)->id,
                    'region_id' => optional($region)->id,
                ];
            });

            $lugaresFiltrados = $lugares->filter(function (array $lugar) use ($request) {
                if ($request->filled('id_ciudad')) {
                    return (int) ($lugar['ciudad_id'] ?? 0) === (int) $request->input('id_ciudad');
                }

                if ($request->filled('id_region')) {
                    return (int) ($lugar['region_id'] ?? 0) === (int) $request->input('id_region');
                }

                return true;
            })->values();

            if ($lugaresFiltrados->isEmpty()) {
                $lugaresFiltrados = $lugares->values();
            }

            $lugares = $lugaresFiltrados->map(function (array $lugar) {
                unset($lugar['ciudad_id'], $lugar['region_id']);

                return $lugar;
            });

            $direccionPrincipal = $profesional->Direccion;
            $ciudadPrincipal = optional($direccionPrincipal)->Ciudad;
            $regionPrincipal = optional($ciudadPrincipal)->Region;

            return [
                'id' => $profesional->id,
                'nombre' => trim($profesional->nombre.' '.$profesional->apellido_uno.' '.$profesional->apellido_dos),
                'especialidad' => optional($profesional->TipoEspecialidad)->nombre ?: optional($profesional->Especialidad)->nombre ?: 'Veterinario/a',
                'tipo_especialidad' => optional($profesional->SubTipoEspecialidad)->nombre,
                'sub_tipo_especialidad' => null,
                'email' => $profesional->email,
                'telefono' => $profesional->telefono ?? $profesional->telefono_uno ?? null,
                'imagen' => $imagen,
                'lugares_atencion' => $lugares,
                'agenda_publica_disponible' => $lugares->count() > 0,
                'direccion_principal' => trim(
                    collect([
                        optional($direccionPrincipal)->direccion,
                        optional($direccionPrincipal)->numero_dir,
                        optional($ciudadPrincipal)->nombre,
                        optional($regionPrincipal)->nombre,
                    ])->filter()->implode(', ')
                ),
            ];
        })->values();

        return response()->json([
            'estado' => 1,
            'registros' => $resultados,
        ]);
    }

    public function horas(Request $request)
    {
        $request->validate([
            'id_profesional' => 'required_without:id_prestador|nullable|integer',
            'id_prestador' => 'required_without:id_profesional|nullable|integer',
            'id_lugar_atencion' => 'required|integer',
            'fecha' => 'required|date_format:Y-m-d',
        ]);

        $idProfesional = (int) ($request->input('id_prestador') ?: $request->input('id_profesional'));
        $idLugar = (int) $request->input('id_lugar_atencion');
        $fecha = Carbon::createFromFormat('Y-m-d', $request->input('fecha'))->startOfDay();
        $tipoAgenda = $this->tipoAgenda($request);

        // Todos los turnos del día, sin horas pasadas, bloqueadas ni ocupadas en ningún lugar
        $bloques = app(AgendaDisponibilidad::class)->bloquesLibres($idProfesional, $idLugar, $fecha->toDateString(), $tipoAgenda);

        return response()->json([
            'estado' => 1,
            'msj' => $bloques ? 'registros' : 'sin horas disponibles',
            'text_fecha' => $fecha->translatedFormat('l d \\d\\e F \\d\\e Y'),
            'horas' => array_map(fn ($b) => substr($b['hora'], 0, 5), $bloques),
            'horarios' => array_map(function ($b) use ($fecha) {
                $inicio = Carbon::parse($fecha->toDateString().' '.$b['hora']);

                return [
                    'fecha' => $inicio->format('Y-m-d'),
                    'hora' => $inicio->format('H:i'),
                    'fecha_hora' => $inicio->format('Y-m-d H:i'),
                    'timestamp' => $inicio->timestamp,
                ];
            }, $bloques),
        ]);
    }

    public function reservar(Request $request)
    {
        // id_prestador es el nombre que usa el buscador nuevo; id_profesional se mantiene por compatibilidad
        if (!$request->filled('id_profesional') && $request->filled('id_prestador')) {
            $request->merge(['id_profesional' => $request->input('id_prestador')]);
        }

        $validator = Validator::make($request->all(), [
            'id_profesional' => 'required|integer',
            'id_lugar_atencion' => 'required|integer',
            'fecha' => 'required|date_format:Y-m-d',
            'hora' => 'required|date_format:H:i',
            'responsable_id' => 'nullable|integer',
            'responsable_rut' => 'required|string|max:20',
            'responsable_nombres' => 'required_without:responsable_id|nullable|string|max:100',
            'responsable_apellido_uno' => 'required_without:responsable_id|nullable|string|max:50',
            'responsable_apellido_dos' => 'nullable|string|max:50',
            'responsable_email' => 'required_without:responsable_id|nullable|email|max:200',
            'responsable_telefono' => 'required_without:responsable_id|nullable|string|max:20',
            'mascota_id' => 'nullable|integer',
            'mascota_nombre' => 'required_without:mascota_id|nullable|string|max:100',
            'mascota_especie_id' => 'required_without:mascota_id|nullable|integer|exists:especies_mascotas,id',
            'mascota_raza_id' => 'nullable|integer|exists:razas_mascotas,id',
            'mascota_otra_especie' => 'nullable|string|max:100',
            'mascota_sexo' => 'nullable|in:M,F',
            'comentarios' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'estado' => 0,
                'msj' => 'Revisa los datos ingresados.',
                'errores' => $validator->errors(),
            ], 422);
        }

        $rutIngresado = (string) $request->input('responsable_rut');
        if (!$this->rutValido($rutIngresado)) {
            return $this->errorReserva('El RUT ingresado no es válido.', 422);
        }

        $profesional = Profesional::with(['Especialidad', 'TipoEspecialidad', 'SubTipoEspecialidad'])->find((int) $request->input('id_profesional'));
        $lugar = LugarAtencion::with('Direccion.Ciudad')->find((int) $request->input('id_lugar_atencion'));
        if (!$profesional || !$lugar) {
            return $this->errorReserva('No encontramos al profesional o el lugar de atención.', 422);
        }

        // El profesional tiene que atender en ese lugar
        $atiendeEnLugar = \App\Models\ProfesionalesLugaresAtencion::where('id_profesional', $profesional->id)
            ->where('id_lugar_atencion', $lugar->id)
            ->where('estado', 1)
            ->exists();
        if (!$atiendeEnLugar) {
            return $this->errorReserva('El profesional no atiende en ese lugar.', 422);
        }

        $tipoHora = $this->tipoHoraMedica($request);
        $tipoAgenda = ['C' => 1, 'D' => 2, 'T' => 3, 'E' => 4, 'P' => 5][$tipoHora];
        $fecha = $request->input('fecha');
        $hora = $request->input('hora').':00';

        // La hora tiene que ser uno de los bloques libres (mismos criterios que guardarReserva)
        $bloque = collect(app(AgendaDisponibilidad::class)->bloquesLibres($profesional->id, $lugar->id, $fecha, $tipoAgenda))
            ->firstWhere('hora', $hora);
        if (!$bloque) {
            return $this->errorReserva('La hora seleccionada ya no está disponible. Elige otra.', 409);
        }
        $duracion = (int) $bloque['duracion'];

        // ---- Tutor: se identifica por RUT. Si ya existe NO se modifican sus datos ----
        $paciente = null;
        if ($request->filled('responsable_id')) {
            $paciente = Paciente::find((int) $request->input('responsable_id'));
            if (!$paciente || $this->normalizeRut((string) $paciente->rut) !== $this->normalizeRut($rutIngresado)) {
                return $this->errorReserva('El RUT no coincide con el tutor seleccionado.', 422);
            }
        }
        if (!$paciente) {
            $paciente = $this->findPacienteByRut($rutIngresado);
        }
        $tutorNuevo = !$paciente;

        if ($tutorNuevo) {
            // Un tutor nuevo necesita todos sus datos (los existentes ya están guardados)
            foreach (['responsable_nombres', 'responsable_apellido_uno', 'responsable_email', 'responsable_telefono'] as $campo) {
                if (!$request->filled($campo)) {
                    return $this->errorReserva('Completa tus datos para registrarte y reservar.', 422);
                }
            }
        }

        // ---- Mascota ----
        $mascota = null;
        $especie = null;
        if ($request->filled('mascota_id')) {
            if ($tutorNuevo) {
                return $this->errorReserva('La mascota seleccionada no corresponde al tutor.', 422);
            }
            $mascota = Mascota::with('especieMascota')
                ->where('id', (int) $request->input('mascota_id'))
                ->where('id_responsable', $paciente->id)
                ->seleccionables()
                ->first();
            if (!$mascota) {
                return $this->errorReserva('La mascota seleccionada no corresponde al tutor.', 422);
            }
            $especie = $mascota->especieMascota ?: EspecieMascota::find($mascota->especie_id ?: $mascota->especie);
        } else {
            $especie = EspecieMascota::find((int) $request->input('mascota_especie_id'));
            if (!$especie) {
                return $this->errorReserva('Selecciona la especie de tu mascota.', 422);
            }
            if ($request->filled('mascota_raza_id')) {
                $razaValida = DB::table('razas_mascotas')
                    ->where('id', (int) $request->input('mascota_raza_id'))
                    ->where('especie_id', $especie->id)
                    ->exists();
                if (!$razaValida) {
                    return $this->errorReserva('La raza no corresponde a la especie elegida.', 422);
                }
            }
            if ($this->esEspecieOtra($especie) && !$request->filled('mascota_otra_especie')) {
                return $this->errorReserva('Cuéntanos qué especie es tu mascota.', 422);
            }
        }

        // El test de leucemia felina solo se reserva para gatos
        $item = trim((string) $request->input('item', ''));
        if ($item !== '' && stripos($item, 'leucemia felina') !== false && (int) optional($especie)->id !== 2) {
            return $this->errorReserva('El test de leucemia felina es solo para gatos.', 422);
        }

        // Regla del escritorio: una hora por día con el mismo profesional para la misma mascota
        if ($mascota) {
            $yaTiene = HoraMedica::where('id_mascota', $mascota->id)
                ->where('id_profesional', $profesional->id)
                ->where('tipo_hora_medica', $tipoHora)
                ->where('fecha_consulta', $fecha)
                ->whereNotIn('id_estado', AgendaDisponibilidad::ESTADOS_LIBRES)
                ->exists();
            if ($yaTiene) {
                return $this->errorReserva('Tu mascota ya tiene una hora con este profesional ese día.', 422);
            }
        }

        // ---- Guardar ----
        // "mascotas" es MyISAM y no se deshace con la transacción: la mascota nueva se crea al final,
        // con todo lo demás ya validado, y si la hora no se puede guardar se borra a mano.
        $mascotaCreada = null;

        try {
            $horaMedica = DB::transaction(function () use ($request, $paciente, $tutorNuevo, &$mascota, &$mascotaCreada, $especie, $profesional, $lugar, $fecha, $hora, $duracion, $tipoHora, $item, $rutIngresado) {
                if ($tutorNuevo) {
                    $paciente = new Paciente();
                    $paciente->token = md5(uniqid((string) mt_rand(), true));
                    $paciente->rut = $this->formatRut($rutIngresado);
                    $paciente->nombres = trim((string) $request->input('responsable_nombres'));
                    $paciente->apellido_uno = trim((string) $request->input('responsable_apellido_uno'));
                    $paciente->apellido_dos = trim((string) $request->input('responsable_apellido_dos', '')) ?: 'N/A';
                    $paciente->telefono_uno = trim((string) $request->input('responsable_telefono'));
                    $paciente->sexo = 'O';
                    $paciente->email = mb_strtolower(trim((string) $request->input('responsable_email')));
                    $paciente->fecha_nac = '2000-01-01';
                    $paciente->id_prevision = optional(Prevision::orderBy('id')->first())->id ?? 1;
                    $paciente->save();
                }

                if (!$mascota) {
                    $mascota = new Mascota();
                    $mascota->id_responsable = $paciente->id;
                    $mascota->nombre = trim((string) $request->input('mascota_nombre'));
                    $mascota->especie_id = $especie->id;
                    $mascota->especie = $especie->id;
                    $mascota->raza_id = $request->filled('mascota_raza_id') ? (int) $request->input('mascota_raza_id') : null;
                    $mascota->otra_especie = $this->esEspecieOtra($especie) ? trim((string) $request->input('mascota_otra_especie')) : null;
                    $mascota->sexo = $request->input('mascota_sexo') ?: null;
                    $mascota->tiene_chip = false;
                    $mascota->esterilizado = false;
                    $mascota->vacunas_registro = [];
                    $mascota->desparasitaciones_registro = [];
                    $mascota->suscripciones_servicios_registro = [];
                    $mascota->reservas_servicios_registro = [];
                    $mascota->id_user = $paciente->id_usuario;
                    $mascota->estado = 1;
                    $mascota->save();
                    $mascotaCreada = $mascota;
                }

                $horaMedica = new HoraMedica();
                $horaMedica->id_paciente = $paciente->id;
                $horaMedica->id_mascota = $mascota->id;
                $horaMedica->id_profesional = $profesional->id;
                $horaMedica->id_estado = 1;
                $horaMedica->id_lugar_atencion = $lugar->id;
                $horaMedica->fecha_consulta = $fecha;
                $horaMedica->hora_inicio = $hora;
                $horaMedica->hora_termino = Carbon::parse($fecha.' '.$hora)->addMinutes($duracion)->format('H:i:s');
                $horaMedica->tipo_hora_medica = $tipoHora;
                $horaMedica->alias_examen = ['C' => 'Consulta', 'D' => 'Consulta Dental', 'T' => 'Videoconsulta veterinaria'][$tipoHora] ?? ($item ?: 'Consulta');
                $horaMedica->descripcion = trim($paciente->nombres.' '.$paciente->apellido_uno.' '.($paciente->apellido_dos === 'N/A' ? '' : $paciente->apellido_dos));
                $horaMedica->observaciones = trim(
                    'Reserva web pública. Mascota: '.$mascota->nombre.' ('.$especie->nombre.').'.
                    ($item !== '' ? ' Servicio: '.$item.'.' : '').
                    (!empty($request->input('comentarios')) ? ' Observaciones: '.$request->input('comentarios') : '')
                );
                $horaMedica->medio_pago = 'en_consulta';

                // Misma validación final que la reserva del escritorio (turno, bloqueos y choques)
                app(AccesoAgendaVeterinaria::class)->guardarReserva($horaMedica, $duracion);

                return $horaMedica;
            });
        } catch (ValidationException $e) {
            $this->borrarMascotaCreada($mascotaCreada);

            return $this->errorReserva('La hora seleccionada ya no está disponible. Elige otra.', 409);
        } catch (\Throwable $e) {
            $this->borrarMascotaCreada($mascotaCreada);
            report($e);

            return $this->errorReserva('No pudimos guardar la reserva. Intenta nuevamente en unos minutos.', 500);
        }

        $paciente = Paciente::find($horaMedica->id_paciente);

        // La mascota queda vinculada al lugar donde se atiende, igual que al reservar desde el escritorio
        $mascota->vincularLugarAtencion($lugar->id, LugarAtencionInstitucionResolver::resolve($lugar->id), 'agenda_responsable');

        if ($tipoHora === 'T') {
            try {
                $horaMedica->video_llamada = JitsiController::jitsiRegistroMeet($profesional->id, $horaMedica->id_paciente, $horaMedica->id);
            } catch (\Throwable $e) {
                report($e);
            }
        }

        $direccion = optional($lugar->Direccion);
        $ciudad = optional($direccion->Ciudad);

        try {
            // El correo va siempre al email guardado del tutor
            SendMailController::envioCorreo(
                'hora_agendada',
                [[
                    'email' => $paciente->email,
                    'name' => trim($paciente->nombres.' '.$paciente->apellido_uno),
                ]],
                [],
                [],
                'VET SDI - Reserva confirmada',
                [
                    'nombre_paciente' => trim($paciente->nombres.' '.$paciente->apellido_uno.' '.($paciente->apellido_dos === 'N/A' ? '' : $paciente->apellido_dos)),
                    'fecha' => $horaMedica->fecha_consulta,
                    'hora' => substr($horaMedica->hora_inicio, 0, 5),
                    'profesional_nombre' => trim($profesional->nombre.' '.$profesional->apellido_uno.' '.$profesional->apellido_dos),
                    'profesional_especialidad' => optional($profesional->Especialidad)->nombre,
                    'profesional_tipo_especialidad' => optional($profesional->TipoEspecialidad)->nombre,
                    'profesional_sub_tipo_especialidad' => optional($profesional->SubTipoEspecialidad)->nombre,
                    'lugar_atencion' => $lugar->nombre,
                    'direccion' => trim(
                        collect([
                            $direccion->direccion ?? null,
                            $direccion->numero_dir ?? null,
                            $ciudad->nombre ?? null,
                        ])->filter()->implode(', ')
                    ),
                    'mascota_nombre' => $mascota->nombre,
                    'mascota_especie' => $especie->nombre,
                ],
                '',
                ''
            );
        } catch (\Throwable $e) {
            // La hora ya quedó guardada: un problema con el correo no la deshace
            report($e);
        }

        return response()->json([
            'estado' => 1,
            'msj' => 'Reserva creada correctamente.',
            'registro' => [
                'id' => $horaMedica->id,
                'fecha' => $horaMedica->fecha_consulta,
                'hora' => substr($horaMedica->hora_inicio, 0, 5),
                'profesional' => trim($profesional->nombre.' '.$profesional->apellido_uno.' '.$profesional->apellido_dos),
                'lugar_atencion' => $lugar->nombre,
                'mascota' => $mascota->nombre,
                'especie' => $especie->nombre,
            ],
        ]);
    }

    /** Respuesta de error de la reserva, con el mensaje que ve la persona */
    protected function errorReserva(string $mensaje, int $codigo)
    {
        return response()->json(['estado' => 0, 'msj' => $mensaje], $codigo);
    }

    /** Si la hora no se guardó, la mascota recién creada se borra (la tabla no hace rollback) */
    protected function borrarMascotaCreada(?Mascota $mascota): void
    {
        if ($mascota && $mascota->exists) {
            try {
                $mascota->lugaresAtencion()->delete();
            } catch (\Throwable $e) {
                // sin vínculos que borrar
            }
            $mascota->delete();
        }
    }

    /** Servicio y modalidad del buscador → tipo de hora del escritorio (C, D, T, E o P) */
    protected function tipoHoraMedica(Request $request): string
    {
        if ($request->input('modalidad') === 'videoconsulta') {
            return 'T';
        }

        return [
            'procedimiento' => 'P',
            'examenes' => 'E',
            'odontologia' => 'D',
        ][(string) $request->input('servicio')] ?? 'C';
    }

    /** tipo_agenda de los turnos que corresponde a la búsqueda (1 consulta … 5 procedimiento) */
    protected function tipoAgenda(Request $request): int
    {
        return ['C' => 1, 'D' => 2, 'T' => 3, 'E' => 4, 'P' => 5][$this->tipoHoraMedica($request)];
    }

    protected function esEspecieOtra(EspecieMascota $especie): bool
    {
        return stripos(\Illuminate\Support\Str::ascii((string) $especie->nombre), 'otr') === 0;
    }

    /** RUT chileno con dígito verificador correcto */
    protected function rutValido(string $rut): bool
    {
        $limpio = $this->normalizeRut($rut);
        if (strlen($limpio) < 2 || !ctype_digit(substr($limpio, 0, -1))) {
            return false;
        }
        $cuerpo = substr($limpio, 0, -1);
        $suma = 0;
        $factor = 2;
        for ($i = strlen($cuerpo) - 1; $i >= 0; $i--) {
            $suma += ((int) $cuerpo[$i]) * $factor;
            $factor = $factor === 7 ? 2 : $factor + 1;
        }
        $resto = 11 - ($suma % 11);
        $esperado = $resto === 11 ? '0' : ($resto === 10 ? 'K' : (string) $resto);

        return substr($limpio, -1) === $esperado;
    }

    /** "camila.rojas@correo.cl" → "ca•••@correo.cl" */
    protected function enmascararCorreo(?string $correo): string
    {
        $correo = trim((string) $correo);
        if ($correo === '' || strpos($correo, '@') === false) {
            return '';
        }
        [$usuario, $dominio] = explode('@', $correo, 2);

        return mb_substr($usuario, 0, 2).'•••@'.$dominio;
    }

    /** "+56 9 1234 5678" → "+56 9 •••• 5678" */
    protected function enmascararTelefono(?string $telefono): string
    {
        $digitos = preg_replace('/\D/', '', (string) $telefono);
        if (strlen($digitos) < 4) {
            return '';
        }

        return '•••• '.substr($digitos, -4);
    }

    protected function generateExternalRut(): string
    {
        do {
            $rut = 'EXT'.str_pad((string) random_int(0, 999999999), 9, '0', STR_PAD_LEFT);
        } while (Paciente::where('rut', $rut)->exists());

        return $rut;
    }

    protected function getVeterinarySearchCatalog(): array
    {
        return [
            [
                'slug' => 'procedimientos_examenes',
                'nombre' => 'Procedimiento / Exámenes',
                'label_item' => 'Procedimiento / Exámenes',
                'items' => [
                    'Exámenes Sanguíneos',
                    'Radiografías',
                    'Ecografía abdominal',
                    'Ecografía tiroides',
                    'Ecografía tórax extra cardiaca',
                    'TAC',
                    'Toma Muestra de Sangre',
                    'Ecografía Cuello',
                ],
            ],
            [
                'slug' => 'consulta_veterinaria',
                'nombre' => 'Consulta Veterinaria',
                'label_item' => 'Consulta',
                'items' => [
                    'Consulta General Felino',
                    'Consulta General Canino',
                    'Implantación Microchip',
                    'Control Felino',
                    'Control Canino',
                ],
            ],
            [
                'slug' => 'consulta_especialista',
                'nombre' => 'Consulta Especialista',
                'label_item' => 'Especialidad',
                'items' => [
                    'Broncopulmonar',
                    'Cardiología',
                    'Neurología',
                    'Odontología',
                    'Especialista Felino',
                    'Especialista Canino',
                    'Nefrología',
                    'Oftalmología',
                    'Otorrinolaringología',
                    'Dermatología',
                    'Geriatría',
                    'Consulta Cirujano',
                    'Medicina Interna Canino',
                    'Medicina Interna Felino',
                    'Oncología',
                    'Traumatología',
                    'Fisiatría',
                    'Sesión Fisiatría',
                ],
            ],
            [
                'slug' => 'vacunacion',
                'nombre' => 'Vacunación',
                'label_item' => 'Vacuna',
                'items' => [
                    'Vacunación Canino',
                    'Vacunación Felino',
                ],
            ],
            [
                'slug' => 'visita_hospital',
                'nombre' => 'Visita Hospital',
                'label_item' => 'Servicio',
                'items' => [
                    'Visita Hospital',
                ],
            ],
            [
                'slug' => 'peluqueria',
                'nombre' => 'Peluquería',
                'label_item' => 'Servicio',
                'items' => [
                    'Peluquería / Baño y Mantenimiento',
                    'Corte de uñas',
                ],
            ],
        ];
    }

    protected function findVeterinaryAreaBySlug(string $slug): ?array
    {
        foreach ($this->getVeterinarySearchCatalog() as $area) {
            if (($area['slug'] ?? '') === $slug) {
                return $area;
            }
        }

        return null;
    }

    protected function findPacienteByRut(string $rut): ?Paciente
    {
        $normalizedRut = $this->normalizeRut($rut);

        if ($normalizedRut === '') {
            return null;
        }

        return Paciente::whereRaw(
            "REPLACE(REPLACE(UPPER(rut), '.', ''), '-', '') = ?",
            [$normalizedRut]
        )->first();
    }

    protected function normalizeRut(?string $value): string
    {
        return strtoupper((string) preg_replace('/[^0-9kK]/', '', (string) $value));
    }

    protected function formatRut(?string $value): string
    {
        $normalized = $this->normalizeRut($value);

        if ($normalized === '' || strncmp($normalized, 'EXT', 3) === 0) {
            return $normalized;
        }

        if (strlen($normalized) < 2) {
            return $normalized;
        }

        $body = substr($normalized, 0, -1);
        $dv = substr($normalized, -1);
        $formattedBody = number_format((int) $body, 0, '', '.');

        return $formattedBody.'-'.$dv;
    }
}
