<?php

namespace App\Http\Controllers;

use App\Models\Paciente;
use App\Models\PacientesDependientes;
use App\Models\Mascota;
use App\Models\EspecieMascota;
use App\Models\EspecieTamanoMascota;
use App\Models\RazaMascota;
use App\Models\TamanoMascota;
use App\Models\FichaAtencion;
use App\Models\DetalleReceta;
use App\Models\HoraMedica;
use App\Models\Profesional;
use App\Models\Producto;
use App\Models\DocumentoLaboratorioMascota;
use App\Models\Direccion;
use App\Models\Personas;
use App\Models\Mensajes;
use App\Support\LugarAtencionInstitucionResolver;
use App\Support\VeterinaryReservaCatalog;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Collection;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;



class MascotasController extends Controller
{
    public function buscarProductosSuscripcion(Request $request)
    {
        $search = trim((string) $request->input('search', ''));
        $servicio = trim((string) $request->input('service', ''));

        $productos = Producto::query()
            ->when($servicio !== '' && $search === '', fn ($query) => $query->whereRaw('1 = 0'))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('nombre', 'like', '%' . $search . '%')
                        ->orWhere('codigo_interno', 'like', '%' . $search . '%')
                        ->orWhere('descripcion', 'like', '%' . $search . '%');
                });
            })
            ->orderBy('nombre', 'asc')
            ->limit(20)
            ->get(['id', 'nombre', 'codigo_interno', 'descripcion']);

        $response = $productos->map(function ($producto) {
            $label = $producto->nombre;

            if (!empty($producto->codigo_interno)) {
                $label .= ' (' . $producto->codigo_interno . ')';
            }

            return [
                'value' => $producto->id,
                'label' => $label,
                'name' => $producto->nombre,
                'codigo_interno' => $producto->codigo_interno,
                'descripcion' => $producto->descripcion,
            ];
        })->values();

        if (in_array($servicio, ['alimentos', 'farmacia', 'pet_shop', 'entretencion'], true)) {
            try {
                $externos = DB::connection('alimentos')->table('productos')
                ->where('activo', 1)
                ->when($servicio === 'alimentos', fn ($q) => $q->where('categoria', 'like', '%alimento%'))
                ->when($servicio === 'farmacia', fn ($q) => $q->where(function ($s) { $s->where('categoria', 'like', '%farm%')->orWhere('categoria', 'like', '%medic%'); }))
                ->when($search !== '', fn ($q) => $q->where(function ($s) use ($search) { $s->where('nombre', 'like', "%{$search}%")->orWhere('marca', 'like', "%{$search}%")->orWhere('descripcion', 'like', "%{$search}%"); }))
                    ->orderBy('nombre')->limit(20)->get(['id', 'nombre', 'marca', 'categoria', 'peso', 'precio']);
            } catch (\Throwable $exception) {
                report($exception);
                $externos = collect();
            }

            $response = $response->concat($externos->map(fn ($p) => [
                'value' => 'alimentos:' . $p->id,
                'label' => trim($p->nombre . ($p->marca ? ' · ' . $p->marca : '') . ' · $' . number_format((float) $p->precio, 0, ',', '.')),
                'name' => $p->nombre,
                'codigo_interno' => 'ALI-' . $p->id,
                'descripcion' => trim(($p->categoria ?? '') . ' ' . ($p->peso ?? '')),
                'source' => 'Central de alimentos y farmacia',
                'price' => (float) $p->precio,
            ]))->values();
        }

        return response()->json($response->take(30)->values());
    }

    public function index()
    {
        $paciente = Paciente::where('id_usuario', Auth::id())->first();
        $idLugarAtencion = request()->input('id_lugar_atencion');
        $mascotas = Mascota::with(['especieMascota', 'tamanoMascota', 'lugaresAtencion'])
            ->where('id_responsable', optional($paciente)->id)
            ->where('estado', 1)
            ->porLugarAtencion($idLugarAtencion)
            ->get();
        $especies = EspecieMascota::orderBy('nombre')->get();
        $tamanos = TamanoMascota::orderBy('nombre')->get();
        $especieTamanos = EspecieTamanoMascota::with(['especie', 'tamano'])->get();

        return view('app.paciente.dependientes')->with([
            'titulo' => 'Mascotas',
            'registros' => collect(),
            'mascotas' => $mascotas,
            'dependencia' => 0,
            'tipo_dependencias' => '',
            'paciente' => $paciente,
            'prevision' => [],
            'region' => [],
            'especiesMascotas' => $especies,
            'tamanosMascotas' => $tamanos,
            'especieTamanosMascotas' => $especieTamanos,
            'fichasMascota' => collect(),
        ]);
    }

    public function obtenerFichasMascota($mascotaId)
    {
        $mascota = $this->resolverMascota($mascotaId);
        if (!$mascota) {
            return response()->json([
                'estado' => 0,
                'msj' => 'Mascota no encontrada',
                'registros' => [],
            ], 404);
        }

        $registros = FichaAtencion::with(['Profesional', 'LugarAtencion'])
            ->where('id_mascota', $mascota->id)
            ->where('id_paciente', $mascota->id_responsable)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get()
            ->map(function ($ficha) {
                $profesional = $ficha->Profesional;
                $lugarAtencion = $ficha->LugarAtencion;

                return [
                    'id' => $ficha->id,
                    'fecha' => optional($ficha->created_at)->format('d/m/Y'),
                    'diagnostico' => $ficha->hipotesis_diagnostico ?: '-',
                    'indicaciones' => $ficha->indicaciones ?: '-',
                    'profesional' => trim(collect([
                        optional($profesional)->nombre,
                        optional($profesional)->apellido_uno,
                        optional($profesional)->apellido_dos,
                    ])->filter()->implode(' ')) ?: 'Sin profesional registrado',
                    'lugar_atencion' => optional($lugarAtencion)->nombre ?: 'Sin lugar registrado',
                ];
            })
            ->values();

        return response()->json([
            'estado' => 1,
            'msj' => 'registros',
            'registros' => $registros,
        ]);
    }

    public function store(Request $request)
    {
        $request->merge([
            'raza_id' => resolver_raza_mascota_id(
                $request->input('raza_id'),
                $request->filled('especie_id') ? (int) $request->input('especie_id') : null
            ),
        ]);

        $validator = Validator::make($request->all(), [
            'tiene_chip' => 'required|boolean',
            'chip' => 'nullable|required_if:tiene_chip,1|string|max:255',
            'nombre' => 'required|string|max:255',
            'especie_id' => 'required|integer|exists:especies_mascotas,id',
            'raza_id' => 'nullable|integer|exists:razas_mascotas,id',
            'otra_especie' => 'nullable|string|max:500',
            'tamano_id' => 'required|integer|exists:tamanos_mascotas,id',
            'fecha_nacimiento' => 'nullable|date|before_or_equal:today',
            'sexo' => 'required|string|in:M,F',
            'foto_perfil' => 'nullable|string|max:255',
            'galeria' => 'nullable',
            'observaciones_fotos' => 'nullable|string',
            'esterilizado' => 'required|boolean',
            'fecha_esterilizacion_desconocida' => 'nullable|boolean',
            'fecha_esterilizacion' => [
                'nullable',
                'date',
                'before_or_equal:today',
                Rule::requiredIf(function () use ($request) {
                    $esterilizado = filter_var($request->input('esterilizado'), FILTER_VALIDATE_BOOLEAN);
                    $fechaDesconocida = filter_var($request->input('fecha_esterilizacion_desconocida'), FILTER_VALIDATE_BOOLEAN);

                    return $esterilizado && !$fechaDesconocida;
                }),
            ],
            'enfermedad_cronica' => 'nullable|string|max:500',
            'dieta' => 'nullable|string',
            'ultima_desparasitacion' => 'nullable|date|before_or_equal:today',
            'producto_desparasitacion' => 'nullable|string|max:255',
            'cirugias' => 'nullable|string',
            'vacunas' => 'nullable|string',
            'viajes' => 'nullable|string',
            'vive_con_animales' => 'nullable|boolean',
            'id_lugar_atencion' => 'nullable|integer|exists:lugares_atencion,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'estado' => 0,
                'msj' => 'campos requeridos',
                'error' => $validator->errors(),
            ], 422);
        }

        $paciente = Paciente::where('id_usuario', Auth::id())->first();
        if (!$paciente) {
            return response()->json([
                'estado' => 0,
                'msj' => 'Paciente no encontrado',
            ], 404);
        }

        $tieneChipSolicitado = filter_var($request->input('tiene_chip'), FILTER_VALIDATE_BOOLEAN);
        if ($tieneChipSolicitado && Mascota::where('chip', trim((string) $request->input('chip')))
            ->where('estado', 1)
            ->exists()) {
            return response()->json([
                'estado' => 0,
                'msj' => 'El número de chip ya está registrado en otra mascota.',
                'error' => ['chip' => ['El número de chip debe ser único.']],
            ], 422);
        }

        $especieSeleccionada = EspecieMascota::find($request->input('especie_id'));
        if ($especieSeleccionada && $especieSeleccionada->requiere_detalle && !$request->filled('otra_especie')) {
            return response()->json([
                'estado' => 0,
                'msj' => 'Debe indicar cuál es la especie de la mascota.',
                'error' => ['otra_especie' => ['Campo requerido para la especie seleccionada.']],
            ], 422);
        }

        $comboValido = EspecieTamanoMascota::where('especie_id', $request->input('especie_id'))
            ->where('tamano_id', $request->input('tamano_id'))
            ->exists();

        if (!$comboValido) {
            return response()->json([
                'estado' => 0,
                'msj' => 'La combinación de especie y tamaño no es válida',
            ], 422);
        }

        if ($request->filled('raza_id')) {
            $razaValida = RazaMascota::where('id', $request->input('raza_id'))
                ->where('especie_id', $request->input('especie_id'))
                ->exists();
            if (!$razaValida) {
                return response()->json([
                    'estado' => 0,
                    'msj' => 'La raza no pertenece a la especie seleccionada',
                ], 422);
            }
        }

        $galeria = normalizar_galeria_mascota_almacenada($request->input('galeria'));

        $tieneChip = filter_var($request->input('tiene_chip'), FILTER_VALIDATE_BOOLEAN);
        $esterilizado = filter_var($request->input('esterilizado'), FILTER_VALIDATE_BOOLEAN);
        $fechaEsterilizacionDesconocida = filter_var($request->input('fecha_esterilizacion_desconocida'), FILTER_VALIDATE_BOOLEAN);
        $viveConAnimalesInput = $request->input('vive_con_animales');
        $viveConAnimales = null;
        if ($viveConAnimalesInput !== null && $viveConAnimalesInput !== '') {
            $viveConAnimales = filter_var($viveConAnimalesInput, FILTER_VALIDATE_BOOLEAN);
        }
        $tamano = TamanoMascota::find($request->input('tamano_id'));

        $mascota = new Mascota();
        $mascota->id_responsable = $paciente->id;
        $mascota->tiene_chip = $tieneChip;
        $mascota->chip = $tieneChip ? trim((string) $request->input('chip')) : null;
        $mascota->nombre = $request->input('nombre');
        $mascota->especie_id = $request->input('especie_id');
        $mascota->especie = $request->input('especie_id');
        $mascota->raza_id = $request->filled('raza_id') ? $request->input('raza_id') : null;
        $mascota->otra_especie = $request->input('otra_especie');
        $mascota->tamano_id = $request->input('tamano_id');
        $mascota->tamano = $tamano ? $tamano->slug : null;
        $mascota->fecha_nacimiento = $request->input('fecha_nacimiento');
        $mascota->sexo = $request->input('sexo');
        $mascota->foto_perfil = resolver_foto_perfil_mascota($request->input('foto_perfil'), $galeria);
        $mascota->galeria = $galeria;
        $mascota->observaciones_fotos = $request->input('observaciones_fotos');
        $mascota->esterilizado = $esterilizado;
        $mascota->fecha_esterilizacion = ($esterilizado && !$fechaEsterilizacionDesconocida && $request->filled('fecha_esterilizacion'))
            ? $request->input('fecha_esterilizacion')
            : null;
        $mascota->enfermedad_cronica = $request->input('enfermedad_cronica');
        $mascota->dieta = $request->input('dieta');
        $mascota->ultima_desparasitacion = $request->input('ultima_desparasitacion');
        $mascota->producto_desparasitacion = $request->input('producto_desparasitacion');
        $mascota->cirugias = $request->input('cirugias');
        $mascota->vacunas = $request->input('vacunas');
        $mascota->viajes = $request->input('viajes');
        $mascota->vive_con_animales = $viveConAnimales;
        $mascota->vacunas_registro = [];
        $mascota->desparasitaciones_registro = [];
        $mascota->suscripciones_servicios_registro = [];
        $mascota->reservas_servicios_registro = [];
        $mascota->id_user = Auth::id();
        $mascota->estado = 1;
        $mascota->ficha_incompleta_veterfarma = false;

        if ($mascota->save()) {
            $idLugarAtencion = $request->filled('id_lugar_atencion')
                ? (int) $request->input('id_lugar_atencion')
                : null;
            $idInstitucion = LugarAtencionInstitucionResolver::resolve($idLugarAtencion);
            $mascota->vincularLugarAtencion($idLugarAtencion, $idInstitucion, 'portal_responsable');

            return response()->json([
                'estado' => 1,
                'msj' => 'Mascota registrada con exito.',
                'id' => $mascota->id,
                'mascota' => $mascota->fresh(['especieMascota', 'razaMascota', 'tamanoMascota']),
            ]);
        }

        return response()->json([
            'estado' => 0,
            'msj' => 'Problemas al registrar la mascota',
        ], 500);
    }

    public function update(Request $request, Mascota $mascota)
    {
        $user = Auth::user();
        $paciente = Paciente::where('id_usuario', Auth::id())->first();
        $autorizado = $paciente && ((int)$mascota->id_responsable === (int)$paciente->id);

        // Permitir actualización desde ficha profesional cuando exista relación real
        // entre profesional, paciente responsable y mascota.
        if (!$autorizado && $user && ($user->hasRole('Profesional') || $user->hasRole('Admin') || $user->hasRole('admin'))) {
            $idResponsable = (int)$request->input('id_responsable');
            if ($idResponsable > 0 && (int)$mascota->id_responsable === $idResponsable) {
                $profesional = Profesional::where('id_usuario', $user->id)->first();
                if ($profesional) {
                    $tieneHora = HoraMedica::where('id_profesional', $profesional->id)
                        ->where('id_paciente', $idResponsable)
                        ->where('id_mascota', $mascota->id)
                        ->exists();

                    $tieneFicha = FichaAtencion::where('id_profesional', $profesional->id)
                        ->where('id_paciente', $idResponsable)
                        ->where('id_mascota', $mascota->id)
                        ->exists();

                    $autorizado = ($tieneHora || $tieneFicha);
                }
            }
        }

        if (!$autorizado) {
            return response()->json([
                'estado' => 0,
                'msj' => 'Mascota no encontrada',
            ], 404);
        }

        $request->merge([
            'raza_id' => resolver_raza_mascota_id(
                $request->input('raza_id'),
                $request->filled('especie_id') ? (int) $request->input('especie_id') : null
            ),
        ]);

        $validator = Validator::make($request->all(), [
            'tiene_chip' => 'required|boolean',
            'chip' => 'nullable|required_if:tiene_chip,1|string|max:255',
            'nombre' => 'required|string|max:255',
            'especie_id' => 'required|integer|exists:especies_mascotas,id',
            'raza_id' => 'nullable|integer|exists:razas_mascotas,id',
            'otra_especie' => 'nullable|string|max:500',
            'tamano_id' => 'required|integer|exists:tamanos_mascotas,id',
            'fecha_nacimiento' => 'nullable|date|before_or_equal:today',
            'sexo' => 'required|string|in:M,F',
            'foto_perfil' => 'nullable|string|max:255',
            'galeria' => 'nullable',
            'observaciones_fotos' => 'nullable|string',
            'esterilizado' => 'required|boolean',
            'fecha_esterilizacion_desconocida' => 'nullable|boolean',
            'fecha_esterilizacion' => [
                'nullable',
                'date',
                'before_or_equal:today',
                Rule::requiredIf(function () use ($request) {
                    $esterilizado = filter_var($request->input('esterilizado'), FILTER_VALIDATE_BOOLEAN);
                    $fechaDesconocida = filter_var($request->input('fecha_esterilizacion_desconocida'), FILTER_VALIDATE_BOOLEAN);

                    return $esterilizado && !$fechaDesconocida;
                }),
            ],
            'enfermedad_cronica' => 'nullable|string|max:500',
            'dieta' => 'nullable|string',
            'ultima_desparasitacion' => 'nullable|date|before_or_equal:today',
            'producto_desparasitacion' => 'nullable|string|max:255',
            'cirugias' => 'nullable|string',
            'vacunas' => 'nullable|string',
            'viajes' => 'nullable|string',
            'vive_con_animales' => 'nullable|boolean',
            'id_lugar_atencion' => 'nullable|integer|exists:lugares_atencion,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'estado' => 0,
                'msj' => 'campos requeridos',
                'error' => $validator->errors(),
            ], 422);
        }

        $tieneChipSolicitado = filter_var($request->input('tiene_chip'), FILTER_VALIDATE_BOOLEAN);
        if ($tieneChipSolicitado && Mascota::where('chip', trim((string) $request->input('chip')))
            ->where('estado', 1)
            ->where('id', '<>', $mascota->id)
            ->exists()) {
            return response()->json([
                'estado' => 0,
                'msj' => 'El número de chip ya está registrado en otra mascota.',
                'error' => ['chip' => ['El número de chip debe ser único.']],
            ], 422);
        }

        $especieSeleccionada = EspecieMascota::find($request->input('especie_id'));
        if ($especieSeleccionada && $especieSeleccionada->requiere_detalle && !$request->filled('otra_especie')) {
            return response()->json([
                'estado' => 0,
                'msj' => 'Debe indicar cuál es la especie de la mascota.',
                'error' => ['otra_especie' => ['Campo requerido para la especie seleccionada.']],
            ], 422);
        }

        $comboValido = EspecieTamanoMascota::where('especie_id', $request->input('especie_id'))
            ->where('tamano_id', $request->input('tamano_id'))
            ->exists();

        if (!$comboValido) {
            return response()->json([
                'estado' => 0,
                'msj' => 'La combinación de especie y tamaño no es válida',
            ], 422);
        }

        if ($request->filled('raza_id')) {
            $razaValida = RazaMascota::where('id', $request->input('raza_id'))
                ->where('especie_id', $request->input('especie_id'))
                ->exists();
            if (!$razaValida) {
                return response()->json([
                    'estado' => 0,
                    'msj' => 'La raza no pertenece a la especie seleccionada',
                ], 422);
            }
        }

        $galeria = normalizar_galeria_mascota_almacenada($request->input('galeria'));

        $tieneChip = filter_var($request->input('tiene_chip'), FILTER_VALIDATE_BOOLEAN);
        $esterilizado = filter_var($request->input('esterilizado'), FILTER_VALIDATE_BOOLEAN);
        $fechaEsterilizacionDesconocida = filter_var($request->input('fecha_esterilizacion_desconocida'), FILTER_VALIDATE_BOOLEAN);
        $tamano = TamanoMascota::find($request->input('tamano_id'));
        $viveConAnimalesInput = $request->input('vive_con_animales');
        $viveConAnimales = ($viveConAnimalesInput === null || $viveConAnimalesInput === '')
            ? null
            : filter_var($viveConAnimalesInput, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

        $mascota->tiene_chip = $tieneChip;
        $mascota->chip = $tieneChip ? trim((string) $request->input('chip')) : null;
        $mascota->nombre = $request->input('nombre');
        $mascota->especie_id = $request->input('especie_id');
        $mascota->especie = $request->input('especie_id');
        $mascota->raza_id = $request->filled('raza_id') ? $request->input('raza_id') : null;
        $mascota->otra_especie = $request->input('otra_especie');
        $mascota->tamano_id = $request->input('tamano_id');
        $mascota->tamano = $tamano ? $tamano->slug : null;
        $mascota->fecha_nacimiento = $request->input('fecha_nacimiento');
        $mascota->sexo = $request->input('sexo');
        $mascota->foto_perfil = resolver_foto_perfil_mascota($request->input('foto_perfil'), $galeria);
        $mascota->galeria = $galeria;
        $mascota->observaciones_fotos = $request->input('observaciones_fotos');
        $mascota->esterilizado = $esterilizado;
        $mascota->fecha_esterilizacion = ($esterilizado && !$fechaEsterilizacionDesconocida && $request->filled('fecha_esterilizacion'))
            ? $request->input('fecha_esterilizacion')
            : null;
        $mascota->enfermedad_cronica = $request->input('enfermedad_cronica');
        $mascota->dieta = $request->input('dieta');
        $mascota->ultima_desparasitacion = $request->input('ultima_desparasitacion');
        $mascota->producto_desparasitacion = $request->input('producto_desparasitacion');
        $mascota->cirugias = $request->input('cirugias');
        $mascota->vacunas = $request->input('vacunas');
        $mascota->viajes = $request->input('viajes');
        $mascota->vive_con_animales = $viveConAnimales;
        $mascota->id_user = Auth::id();
        $mascota->estado = 1;

        if ($mascota->save()) {
            $idLugarAtencion = $request->filled('id_lugar_atencion')
                ? (int) $request->input('id_lugar_atencion')
                : null;
            $idInstitucion = LugarAtencionInstitucionResolver::resolve($idLugarAtencion);
            $mascota->vincularLugarAtencion($idLugarAtencion, $idInstitucion, 'actualizacion');

            return response()->json([
                'estado' => 1,
                'msj' => 'Mascota actualizada con exito.',
                'id' => $mascota->id,
                'mascota' => $mascota->fresh(['especieMascota', 'razaMascota', 'tamanoMascota']),
            ]);
        }

        return response()->json([
            'estado' => 0,
            'msj' => 'Problemas al actualizar la mascota',
        ], 500);
    }

    public function listar(Request $request)
    {
        $paciente = Paciente::where('id_usuario', Auth::id())->first();
        if (!$paciente) {
            return response()->json([
                'estado' => 0,
                'msj' => 'Paciente no encontrado',
            ], 404);
        }

        $mascotas = Mascota::with(['especieMascota', 'razaMascota', 'tamanoMascota', 'lugaresAtencion'])
            ->where('id_responsable', $paciente->id)
            ->where('estado', 1)
            ->porLugarAtencion($request->input('id_lugar_atencion'))
            ->get();

        return response()->json([
            'estado' => 1,
            'msj' => 'registros',
            'registros' => $mascotas,
        ]);
    }

    public function destroy(Mascota $mascota)
    {
        $paciente = Paciente::where('id_usuario', Auth::id())->first();
        if (!$paciente || $mascota->id_responsable !== $paciente->id) {
            return response()->json([
                'estado' => 0,
                'msj' => 'Mascota no encontrada',
            ], 404);
        }

        $mascota->estado = 0;
        $mascota->save();

        return response()->json([
            'estado' => 1,
            'msj' => 'Mascota eliminada con exito.',
        ]);
    }

    public function memorial($mascotaId)
    {
        $paciente = Paciente::where('id_usuario', Auth::id())->firstOrFail();

        $mascota = Mascota::with(['especieMascota', 'razaMascota', 'tamanoMascota', 'Responsable'])
            ->where('id', $mascotaId)
            ->where('id_responsable', $paciente->id)
            ->where('estado', 1)
            ->where('fallecida', true)
            ->firstOrFail();

        return view('app.paciente.memorial_mascota', [
            'mascota' => $mascota,
            'paciente' => $paciente,
            'album' => mascota_album_memorial($mascota),
        ]);
    }

    public function registrarFallecimiento(Request $request, $mascotaId)
    {
        $mascota = $this->mascotaDelResponsableAutenticado($mascotaId);
        if (! $mascota) {
            return response()->json([
                'estado' => 0,
                'msj' => 'Mascota no encontrada o no autorizada.',
            ], 404);
        }

        if ($mascota->estaFallecida()) {
            return response()->json([
                'estado' => 0,
                'msj' => 'Esta mascota ya tiene registrado su fallecimiento.',
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'fecha_fallecimiento' => 'required|date|before_or_equal:today',
            'mensaje' => 'nullable|string|max:2000',
            'causa' => 'nullable|string|max:500',
            'album' => 'nullable',
            'incluir_fotos_actuales' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'estado' => 0,
                'msj' => 'Revise los datos del formulario.',
                'error' => $validator->errors(),
            ], 422);
        }

        $album = normalizar_album_memorial_mascota($request->input('album'));

        if (filter_var($request->input('incluir_fotos_actuales'), FILTER_VALIDATE_BOOLEAN)) {
            foreach (mascota_album_memorial($mascota) as $foto) {
                $ruta = normalizar_imagen_almacenada($foto['url'] ?? null);
                if ($ruta) {
                    $album[] = [$ruta, $foto['titulo'] ?? 'Recuerdo', basename($ruta), ''];
                }
            }
        }

        $mascota->fallecida = true;
        $mascota->fecha_fallecimiento = $request->input('fecha_fallecimiento');
        $mascota->memorial_registro = [
            'mensaje' => trim((string) $request->input('mensaje')),
            'causa' => trim((string) $request->input('causa')),
            'album' => $album,
            'registrado_en' => now()->toIso8601String(),
        ];
        $mascota->save();

        return response()->json([
            'estado' => 1,
            'msj' => 'Se registró el fallecimiento de ' . $mascota->nombre . '.',
            'memorial_path' => route('paciente.mascotas.memorial', ['mascota' => $mascota->id], false),
        ]);
    }

    public function guardarSolicitudApareamiento(Request $request, $mascotaId)
    {
        $mascota = $this->mascotaDelResponsableAutenticado($mascotaId);
        if (!$mascota) {
            return response()->json([
                'estado' => 0,
                'msj' => 'Mascota no encontrada o no autorizada.',
            ], 404);
        }

        if ($mascota->estaFallecida()) {
            return response()->json([
                'estado' => 0,
                'msj' => 'No se pueden publicar solicitudes para mascotas fallecidas.',
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'raza_buscada' => 'required|string|min:2|max:120',
            'edad_minima' => 'nullable|string|max:60',
            'edad_maxima' => 'nullable|string|max:60',
            'tamano_id' => 'nullable|integer',
            'color_pelaje' => 'nullable|string|max:120',
            'pedigree' => 'nullable|string|in:si,no,indiferente',
            'ubicacion' => 'nullable|string|max:190',
            'observaciones' => 'nullable|string|max:2000',
            'contacto_telefono' => 'nullable|string|max:30',
            'contacto_email' => 'nullable|email|max:190',
        ], [
            'raza_buscada.required' => 'Debe indicar la raza buscada.',
            'raza_buscada.min' => 'La raza buscada debe tener al menos 2 caracteres.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'estado' => 0,
                'msj' => 'Datos incompletos para la solicitud.',
                'error' => $validator->errors(),
            ], 422);
        }

        if (!in_array($mascota->sexo, ['M', 'F'], true)) {
            return response()->json([
                'estado' => 0,
                'msj' => 'Debe registrar el sexo de su mascota antes de publicar una solicitud de apareamiento.',
            ], 422);
        }

        $sexoBuscado = $mascota->sexo === 'M' ? 'F' : 'M';

        $mascota->loadMissing(['especieMascota', 'razaMascota', 'tamanoMascota']);
        $tamano = null;
        if ($request->filled('tamano_id')) {
            $tamano = TamanoMascota::find($request->input('tamano_id'));
        }

        $solicitudId = uniqid('apr_', true);
        $tutorOrigen = Paciente::find($mascota->id_responsable);

        $solicitudEmitida = [
            'id' => $solicitudId,
            'tipo' => 'emitida',
            'mascota_id' => $mascota->id,
            'mascota_nombre' => $mascota->nombre,
            'mascota_sexo' => $mascota->sexo,
            'mascota_especie' => optional($mascota->especieMascota)->nombre,
            'mascota_raza' => optional($mascota->razaMascota)->nombre,
            'tutor_nombre' => $tutorOrigen ? trim(collect([
                $tutorOrigen->nombres,
                $tutorOrigen->apellido_uno,
                $tutorOrigen->apellido_dos,
            ])->filter()->implode(' ')) : '',
            'sexo_buscado' => $request->input('sexo_buscado'),
            'raza_buscada' => trim((string) $request->input('raza_buscada')),
            'edad_minima' => trim((string) $request->input('edad_minima', '')),
            'edad_maxima' => trim((string) $request->input('edad_maxima', '')),
            'tamano_id' => $tamano?->id,
            'tamano_nombre' => $tamano?->nombre,
            'color_pelaje' => trim((string) $request->input('color_pelaje', '')),
            'pedigree' => $request->input('pedigree', 'indiferente'),
            'ubicacion' => trim((string) $request->input('ubicacion', '')),
            'observaciones' => trim((string) $request->input('observaciones', '')),
            'contacto_telefono' => trim((string) $request->input('contacto_telefono', '')),
            'contacto_email' => trim((string) $request->input('contacto_email', '')),
            'estado' => 'activa',
            'created_at' => now()->toDateTimeString(),
        ];

        $solicitudes = $this->normalizarRegistros($mascota->solicitudes_apareamiento_registro);
        $solicitudes[] = $solicitudEmitida;
        $mascota->solicitudes_apareamiento_registro = $solicitudes;
        $mascota->save();

        $destinatarios = $this->distribuirSolicitudApareamiento($mascota, $solicitudEmitida, $tutorOrigen);

        return response()->json([
            'estado' => 1,
            'msj' => $destinatarios > 0
                ? "Solicitud publicada y enviada a {$destinatarios} mascota(s) compatible(s)."
                : 'Solicitud publicada. No se encontraron mascotas compatibles en este momento.',
            'destinatarios' => $destinatarios,
            'solicitudes' => $this->normalizarRegistros($mascota->solicitudes_apareamiento_registro),
        ]);
    }

    public function buzonApareamientoMascotas(Request $request)
    {
        $paciente = Paciente::where('id_usuario', Auth::id())->first();
        if (!$paciente) {
            return response()->json([
                'estado' => 0,
                'msj' => 'Paciente no encontrado.',
            ], 404);
        }

        $columnasMascota = ['id', 'nombre'];
        if (\Illuminate\Support\Facades\Schema::hasColumn('mascotas', 'solicitudes_apareamiento_registro')) {
            $columnasMascota[] = 'solicitudes_apareamiento_registro';
        }

        $mascotas = Mascota::query()
            ->where('id_responsable', $paciente->id)
            ->where('estado', 1)
            ->get($columnasMascota);

        $recibidas = [];
        $emitidas = [];
        $nuevas = 0;

        foreach ($mascotas as $mascota) {
            foreach ($this->normalizarRegistros($mascota->solicitudes_apareamiento_registro) as $item) {
                $tipo = $item['tipo'] ?? 'emitida';
                $registro = array_merge($item, [
                    'mascota_destino_id' => $mascota->id,
                    'mascota_destino_nombre' => $mascota->nombre,
                ]);

                if ($tipo === 'recibida') {
                    if (($item['estado'] ?? '') === 'nueva') {
                        $nuevas++;
                    }
                    $recibidas[] = $registro;
                } else {
                    $emitidas[] = $registro;
                }
            }
        }

        usort($recibidas, fn ($a, $b) => strcmp($b['created_at'] ?? '', $a['created_at'] ?? ''));
        usort($emitidas, fn ($a, $b) => strcmp($b['created_at'] ?? '', $a['created_at'] ?? ''));

        return response()->json([
            'estado' => 1,
            'msj' => 'Buzón de apareamiento',
            'nuevas' => $nuevas,
            'recibidas' => $recibidas,
            'emitidas' => $emitidas,
        ]);
    }

    public function marcarApareamientoLeida(Request $request, $mascotaId, $solicitudId)
    {
        $mascota = $this->mascotaDelResponsableAutenticado($mascotaId);
        if (!$mascota) {
            return response()->json(['estado' => 0, 'msj' => 'Mascota no encontrada.'], 404);
        }

        $solicitudes = $this->normalizarRegistros($mascota->solicitudes_apareamiento_registro);
        $actualizado = false;

        foreach ($solicitudes as &$item) {
            if (($item['id'] ?? '') === $solicitudId && ($item['tipo'] ?? '') === 'recibida') {
                $item['estado'] = 'leida';
                $item['leida_at'] = now()->toDateTimeString();
                $actualizado = true;
                break;
            }
        }
        unset($item);

        if (!$actualizado) {
            return response()->json(['estado' => 0, 'msj' => 'Solicitud no encontrada.'], 404);
        }

        $mascota->solicitudes_apareamiento_registro = $solicitudes;
        $mascota->save();

        return response()->json(['estado' => 1, 'msj' => 'Solicitud marcada como leída.']);
    }

    public function buscarTutorTraspasoMascota(Request $request)
    {
        $rut = trim((string) $request->input('rut', ''));
        if ($rut === '') {
            return response()->json([
                'estado' => 0,
                'msj' => 'Debe ingresar un RUT.',
            ], 422);
        }

        $persona = $this->buscarPersonaPorRut($rut);
        $paciente = $this->buscarPacientePorRut($rut);

        if ($persona) {
            $tutor = array_merge($persona, [
                'id' => $paciente?->id,
                'origen' => $paciente ? 'personas_paciente' : 'personas',
                'requiere_formulario' => !$paciente,
                'nombre' => trim(collect([
                    $persona['nombres'] ?? '',
                    $persona['apellido_uno'] ?? '',
                    $persona['apellido_dos'] ?? '',
                ])->filter()->implode(' ')),
            ]);

            return response()->json([
                'estado' => 1,
                'msj' => $paciente
                    ? 'Tutor verificado en Personas y vinculado a VET-SDI.'
                    : 'Persona encontrada en Personas. Confirme los datos para crear su acceso en VET-SDI.',
                'tutor' => $tutor,
            ]);
        }

        if ($paciente) {
            $sincronizado = $this->guardarPersonaEnApi($this->datosPersonaDesdePaciente($paciente));

            return response()->json([
                'estado' => $sincronizado ? 1 : 0,
                'msj' => $sincronizado
                    ? 'Tutor vinculado con Personas y disponible en VET-SDI.'
                    : 'El tutor existe en VET-SDI, pero Personas no está disponible. Intente nuevamente antes de traspasar.',
                'tutor' => array_merge($this->formatearTutorTraspasoDesdePaciente($paciente), [
                    'origen' => $sincronizado ? 'personas_paciente' : 'paciente_pendiente_personas',
                ]),
            ], $sincronizado ? 200 : 503);
        }

        return response()->json([
            'estado' => 1,
            'msj' => 'RUT no encontrado. Complete los datos del nuevo tutor.',
            'tutor' => [
                'id' => null,
                'rut' => $rut,
                'nombres' => '',
                'apellido_uno' => '',
                'apellido_dos' => '',
                'email' => '',
                'telefono_uno' => '',
                'direccion' => '',
                'nombre' => '',
                'origen' => 'manual',
                'requiere_formulario' => true,
            ],
        ]);
    }

    public function resumenTraspasoMascota(Request $request, $mascotaId)
    {
        $mascota = $this->mascotaDelResponsableAutenticado($mascotaId);
        if (!$mascota) {
            return response()->json([
                'estado' => 0,
                'msj' => 'Mascota no encontrada o no autorizada.',
            ], 404);
        }

        $pacienteActual = Paciente::find($mascota->id_responsable);
        $resumen = $this->construirResumenTraspaso($mascota);

        $nuevoTutor = null;
        if ($request->filled('rut_tutor')) {
            $nuevoTutor = $this->resolverTutorTraspasoParaResumen($request);
            if ($nuevoTutor instanceof \Illuminate\Http\JsonResponse) {
                return $nuevoTutor;
            }
            if ((int) ($nuevoTutor['id'] ?? 0) === (int) $mascota->id_responsable) {
                return response()->json([
                    'estado' => 0,
                    'msj' => 'El nuevo tutor debe ser distinto al tutor actual.',
                ], 422);
            }
        }

        return response()->json([
            'estado' => 1,
            'msj' => 'Resumen de traspaso',
            'mascota' => [
                'id' => $mascota->id,
                'nombre' => $mascota->nombre,
            ],
            'tutor_actual' => $this->formatearPacienteResumen($pacienteActual),
            'tutor_nuevo' => $nuevoTutor,
            'resumen' => $resumen,
        ]);
    }

    public function ejecutarTraspasoMascota(Request $request, $mascotaId)
    {
        $validator = Validator::make($request->all(), [
            'rut_tutor' => 'required|string|max:20',
            'situacion' => 'required|string|min:5|max:1000',
            'confirmar' => 'required|accepted',
            'tutor_nombres' => 'nullable|string|max:120',
            'tutor_apellido_uno' => 'nullable|string|max:120',
            'tutor_apellido_dos' => 'nullable|string|max:120',
            'tutor_email' => 'nullable|email|max:190',
            'tutor_telefono' => 'nullable|string|max:30',
            'tutor_direccion' => 'nullable|string|max:255',
        ], [
            'situacion.required' => 'Debe indicar la situación o motivo del traspaso.',
            'situacion.min' => 'La situación debe tener al menos 5 caracteres.',
            'confirmar.accepted' => 'Debe confirmar el traspaso.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'estado' => 0,
                'msj' => 'Datos incompletos para el traspaso.',
                'error' => $validator->errors(),
            ], 422);
        }

        $mascota = $this->mascotaDelResponsableAutenticado($mascotaId);
        if (!$mascota) {
            return response()->json([
                'estado' => 0,
                'msj' => 'Mascota no encontrada o no autorizada.',
            ], 404);
        }

        $nuevoTutor = $this->resolverOCrearTutorTraspaso($request);
        if ($nuevoTutor instanceof \Illuminate\Http\JsonResponse) {
            return $nuevoTutor;
        }

        if ((int) $nuevoTutor->id === (int) $mascota->id_responsable) {
            return response()->json([
                'estado' => 0,
                'msj' => 'El nuevo tutor debe ser distinto al tutor actual.',
            ], 422);
        }

        $tutorAnteriorId = (int) $mascota->id_responsable;
        $resumen = $this->construirResumenTraspaso($mascota);

        DB::transaction(function () use ($mascota, $nuevoTutor, $tutorAnteriorId) {
            $mascota->id_responsable = $nuevoTutor->id;
            $mascota->save();

            FichaAtencion::where('id_mascota', $mascota->id)
                ->where('id_paciente', $tutorAnteriorId)
                ->update(['id_paciente' => $nuevoTutor->id]);

            HoraMedica::where('id_mascota', $mascota->id)
                ->where('id_paciente', $tutorAnteriorId)
                ->update(['id_paciente' => $nuevoTutor->id]);

            DocumentoLaboratorioMascota::where('id_mascota', $mascota->id)
                ->where('id_responsable', $tutorAnteriorId)
                ->update(['id_responsable' => $nuevoTutor->id]);
        });

        return response()->json([
            'estado' => 1,
            'msj' => 'Mascota traspasada correctamente al nuevo tutor.',
            'mascota' => [
                'id' => $mascota->id,
                'nombre' => $mascota->nombre,
            ],
            'tutor_nuevo' => $this->formatearPacienteResumen($nuevoTutor),
            'resumen' => $resumen,
            'situacion' => trim((string) $request->input('situacion')),
        ]);
    }

    private function mascotaDelResponsableAutenticado($mascotaId): ?Mascota
    {
        $paciente = Paciente::where('id_usuario', Auth::id())->first();
        if (!$paciente) {
            return null;
        }

        return Mascota::where('id', $mascotaId)
            ->where('id_responsable', $paciente->id)
            ->where('estado', 1)
            ->first();
    }

    private function normalizarRut(?string $rut): string
    {
        return strtoupper(preg_replace('/[^0-9kK]/', '', (string) $rut));
    }

    private function buscarPacientePorRut(?string $rut): ?Paciente
    {
        $normalizado = $this->normalizarRut($rut);
        if ($normalizado === '') {
            return null;
        }

        return Paciente::query()
            ->whereRaw("REPLACE(REPLACE(REPLACE(UPPER(rut), '.', ''), '-', ''), ' ', '') = ?", [$normalizado])
            ->first();
    }

    private function formatearPacienteResumen(?Paciente $paciente): ?array
    {
        if (!$paciente) {
            return null;
        }

        return [
            'id' => $paciente->id,
            'rut' => $paciente->rut,
            'nombre' => trim(collect([
                $paciente->nombres,
                $paciente->apellido_uno,
                $paciente->apellido_dos,
            ])->filter()->implode(' ')),
        ];
    }

    private function formatearTutorTraspasoDesdePaciente(Paciente $paciente): array
    {
        $paciente->loadMissing('Direccion');

        return [
            'id' => $paciente->id,
            'rut' => $paciente->rut,
            'nombres' => $paciente->nombres,
            'apellido_uno' => $paciente->apellido_uno,
            'apellido_dos' => $paciente->apellido_dos,
            'email' => $paciente->email,
            'telefono_uno' => $paciente->telefono_uno,
            'direccion' => optional($paciente->Direccion)->direccion ?? '',
            'nombre' => trim(collect([
                $paciente->nombres,
                $paciente->apellido_uno,
                $paciente->apellido_dos,
            ])->filter()->implode(' ')),
            'origen' => 'paciente',
            'requiere_formulario' => false,
        ];
    }

    private function resolverTutorTraspasoParaResumen(Request $request): ?array
    {
        $rut = trim((string) $request->input('rut_tutor', ''));
        if ($rut === '') {
            return null;
        }

        $paciente = $this->buscarPacientePorRut($rut);
        if ($paciente) {
            return $this->formatearTutorTraspasoDesdePaciente($paciente);
        }

        $nombres = trim((string) $request->input('tutor_nombres', ''));
        $apellidoUno = trim((string) $request->input('tutor_apellido_uno', ''));
        $apellidoDos = trim((string) $request->input('tutor_apellido_dos', ''));

        if ($nombres === '' || $apellidoUno === '') {
            return response()->json([
                'estado' => 0,
                'msj' => 'Debe completar nombre y apellido paterno del nuevo tutor.',
            ], 422);
        }

        return [
            'id' => null,
            'rut' => $rut,
            'nombres' => $nombres,
            'apellido_uno' => $apellidoUno,
            'apellido_dos' => $apellidoDos,
            'email' => trim((string) $request->input('tutor_email', '')),
            'telefono_uno' => trim((string) $request->input('tutor_telefono', '')),
            'direccion' => trim((string) $request->input('tutor_direccion', '')),
            'nombre' => trim(collect([$nombres, $apellidoUno, $apellidoDos])->filter()->implode(' ')),
            'origen' => 'manual',
            'requiere_formulario' => true,
        ];
    }

    private function resolverOCrearTutorTraspaso(Request $request)
    {
        $rut = trim((string) $request->input('rut_tutor', ''));
        if ($rut === '') {
            return response()->json([
                'estado' => 0,
                'msj' => 'Debe indicar el RUT del nuevo tutor.',
            ], 422);
        }

        $paciente = $this->buscarPacientePorRut($rut);
        if ($paciente) {
            $persona = $this->buscarPersonaPorRut($rut);
            if (!$persona && !$this->guardarPersonaEnApi($this->datosPersonaDesdePaciente($paciente))) {
                return response()->json([
                    'estado' => 0,
                    'msj' => 'No fue posible vincular el tutor con Personas. El traspaso no se realizó.',
                ], 503);
            }
            return $paciente;
        }

        $persona = $this->buscarPersonaPorRut($rut);
        if ($persona) {
            $request->merge([
                'tutor_nombres' => $persona['nombres'] ?: $request->input('tutor_nombres'),
                'tutor_apellido_uno' => $persona['apellido_uno'] ?: $request->input('tutor_apellido_uno'),
                'tutor_apellido_dos' => $persona['apellido_dos'] ?: $request->input('tutor_apellido_dos'),
                'tutor_email' => $persona['email'] ?: $request->input('tutor_email'),
                'tutor_telefono' => $persona['telefono_uno'] ?: $request->input('tutor_telefono'),
                'tutor_direccion' => $persona['direccion'] ?: $request->input('tutor_direccion'),
            ]);
        }

        $validator = Validator::make($request->all(), [
            'tutor_nombres' => 'required|string|max:120',
            'tutor_apellido_uno' => 'required|string|max:120',
            'tutor_apellido_dos' => 'nullable|string|max:120',
            'tutor_email' => 'nullable|email|max:190',
            'tutor_telefono' => 'nullable|string|max:30',
            'tutor_direccion' => 'nullable|string|max:255',
        ], [
            'tutor_nombres.required' => 'Debe indicar el nombre del nuevo tutor.',
            'tutor_apellido_uno.required' => 'Debe indicar el apellido paterno del nuevo tutor.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'estado' => 0,
                'msj' => 'Complete los datos del nuevo tutor.',
                'error' => $validator->errors(),
            ], 422);
        }

        $nombres = trim((string) $request->input('tutor_nombres'));
        $apellidoUno = trim((string) $request->input('tutor_apellido_uno'));
        $apellidoDos = trim((string) $request->input('tutor_apellido_dos', ''));
        $email = trim((string) $request->input('tutor_email', ''));
        $telefono = trim((string) $request->input('tutor_telefono', ''));
        $direccionTexto = trim((string) $request->input('tutor_direccion', ''));

        if (!$persona) {
            $personaGuardada = $this->guardarPersonaEnApi([
                'rut' => $rut,
                'nombres' => $nombres,
                'apellido_uno' => $apellidoUno,
                'apellido_dos' => $apellidoDos,
                'email' => $email,
                'telefono_uno' => $telefono,
                'direccion' => $direccionTexto,
            ]);

            if (!$personaGuardada) {
                return response()->json([
                    'estado' => 0,
                    'msj' => 'No fue posible registrar al nuevo tutor en Personas. El traspaso no se realizó.',
                ], 503);
            }
        }

        if ($email === '') {
            $email = PacienteController::generarEmailPacienteTemporal($nombres, $apellidoUno, $apellidoDos);
        }

        $idDireccion = null;
        if ($direccionTexto !== '') {
            $direccion = new Direccion();
            $direccion->direccion = $direccionTexto;
            $direccion->numero_dir = '';
            $direccion->id_ciudad = 0;
            $direccion->save();
            $idDireccion = $direccion->id;
        }

        $nuevoPaciente = new Paciente();
        $nuevoPaciente->token = md5(uniqid());
        $nuevoPaciente->rut = $rut;
        $nuevoPaciente->nombres = $nombres;
        $nuevoPaciente->apellido_uno = $apellidoUno;
        $nuevoPaciente->apellido_dos = $apellidoDos;
        $nuevoPaciente->email = $email;
        $nuevoPaciente->telefono_uno = $telefono !== '' ? $telefono : null;
        $nuevoPaciente->id_direccion = $idDireccion;
        $nuevoPaciente->save();

        return $nuevoPaciente;
    }

    private function buscarPersonaPorRut(string $rut): ?array
    {
        $normalizado = $this->normalizarRut($rut);
        if ($normalizado === '') {
            return null;
        }

        $apiUrl = rtrim((string) config('services.personas.url'), '/');
        if ($apiUrl !== '') {
            try {
                $response = Http::acceptJson()
                    ->withToken((string) config('services.personas.token'))
                    ->connectTimeout((int) config('services.personas.connect_timeout', 2))
                    ->timeout((int) config('services.personas.timeout', 5))
                    ->get($apiUrl.'/api/personas/'.$normalizado);

                if ($response->successful() && $response->json('found')) {
                    $personaApi = $response->json('persona', []);

                    return [
                        'rut' => $personaApi['rut_original'] ?? $rut,
                        'nombres' => $personaApi['nombre1'] ?? '',
                        'apellido_uno' => $personaApi['appaterno'] ?? '',
                        'apellido_dos' => $personaApi['apmaterno'] ?? '',
                        'email' => $personaApi['email'] ?? '',
                        'telefono_uno' => $personaApi['telefono'] ?? '',
                        'direccion' => $personaApi['direccion'] ?? '',
                    ];
                }
            } catch (\Throwable $e) {
                Log::warning('No fue posible consultar Personas API para traspaso de mascota.', [
                    'rut' => $normalizado,
                    'message' => $e->getMessage(),
                ]);
            }
        }

        $registro = Personas::query()
            ->whereRaw("REPLACE(REPLACE(REPLACE(UPPER(rut), '.', ''), '-', ''), ' ', '') = ?", [$normalizado])
            ->first();

        if (!$registro) {
            return null;
        }

        return [
            'rut' => $registro->rut,
            'nombres' => $registro->nombre1 ?? '',
            'apellido_uno' => $registro->appaterno ?? '',
            'apellido_dos' => $registro->apmaterno ?? '',
            'email' => $registro->email ?? '',
            'telefono_uno' => $registro->telefono ?? '',
            'direccion' => $registro->direccion ?? '',
        ];
    }

    private function guardarPersonaEnApi(array $persona): bool
    {
        $apiUrl = rtrim((string) config('services.personas.url'), '/');
        if ($apiUrl === '') {
            return false;
        }

        try {
            $response = Http::acceptJson()
                ->withToken((string) config('services.personas.token'))
                ->connectTimeout((int) config('services.personas.connect_timeout', 2))
                ->timeout((int) config('services.personas.timeout', 5))
                ->post($apiUrl.'/api/personas', [
                    'rut' => $persona['rut'] ?? '',
                    'nombre1' => $persona['nombres'] ?? '',
                    'appaterno' => $persona['apellido_uno'] ?? '',
                    'apmaterno' => $persona['apellido_dos'] ?? '',
                    'email' => $persona['email'] ?? null,
                    'telefono' => $persona['telefono_uno'] ?? null,
                    'direccion' => $persona['direccion'] ?? null,
                ]);

            if ($response->successful() && $response->json('ok')) {
                return true;
            }

            Log::warning('Personas API rechazó la sincronización del tutor.', [
                'rut' => $this->normalizarRut($persona['rut'] ?? ''),
                'status' => $response->status(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('No fue posible sincronizar el tutor con Personas API.', [
                'rut' => $this->normalizarRut($persona['rut'] ?? ''),
                'message' => $e->getMessage(),
            ]);
        }

        return false;
    }

    private function datosPersonaDesdePaciente(Paciente $paciente): array
    {
        $paciente->loadMissing('Direccion');

        return [
            'rut' => $paciente->rut,
            'nombres' => $paciente->nombres,
            'apellido_uno' => $paciente->apellido_uno,
            'apellido_dos' => $paciente->apellido_dos,
            'email' => $paciente->email,
            'telefono_uno' => $paciente->telefono_uno,
            'direccion' => optional($paciente->Direccion)->direccion,
        ];
    }

    private function construirResumenTraspaso(Mascota $mascota): array
    {
        $totalFvu = FichaAtencion::where('id_mascota', $mascota->id)->count();
        $vacunas = $this->normalizarRegistros($mascota->vacunas_registro);
        $desparasitaciones = $this->normalizarRegistros($mascota->desparasitaciones_registro);

        return [
            'fvu' => $totalFvu,
            'vacunas' => count($vacunas),
            'desparasitaciones' => count($desparasitaciones),
        ];
    }

    public function suscripcion_servicios(Request $request){
        $paciente = Paciente::where('id_usuario', Auth::id())->first();
        $mascotas = collect();
        $mascotaActiva = null;

        if ($paciente) {
            $mascotas = Mascota::where('id_responsable', $paciente->id)
                ->orderBy('nombre')
                ->get();

            $mascotaActiva = $this->resolverMascota($request->input('id_dependiente_activo'));

            if (!$mascotaActiva && $mascotas->count() === 1) {
                $mascotaActiva = $mascotas->first();
            }
        }

        $petsData = $mascotas->map(function ($mascota) {
            return [
                'id' => $mascota->id,
                'nombre' => $mascota->nombre,
                'suscripciones' => array_values($mascota->suscripciones_servicios_registro ?? []),
                'reservas' => array_values($mascota->reservas_servicios_registro ?? []),
            ];
        })->values();

        $prescripcionesData = DetalleReceta::query()
            ->join('fichas_atenciones as ficha', 'ficha.id', '=', 'detalles_receta.id_ficha')
            ->whereIn('ficha.id_mascota', $mascotas->pluck('id'))
            ->where('detalles_receta.estado', 1)
            ->orderByDesc('detalles_receta.created_at')
            ->get([
                'detalles_receta.id',
                'ficha.id_mascota',
                'detalles_receta.producto',
                'detalles_receta.presentacion',
                'detalles_receta.cantidad_compra',
                'detalles_receta.posologia',
                'detalles_receta.created_at',
            ])
            ->map(function ($receta) {
                return [
                    'id' => (int) $receta->id,
                    'mascota_id' => (int) $receta->id_mascota,
                    'producto' => (string) $receta->producto,
                    'presentacion' => (string) ($receta->presentacion ?: 'Sin presentación indicada'),
                    'cantidad' => max(1, (int) ($receta->cantidad_compra ?: 1)),
                    'posologia' => (string) ($receta->posologia ?: ''),
                    'fecha' => optional($receta->created_at)->format('d-m-Y'),
                ];
            })
            ->values();

        try {
            $comercios = DB::connection('alimentos')->table('locales_venta')->where('activo', 1)
                ->orderBy('nombre')->get(['id','nombre','tipo','direccion','comuna','telefono','email','georeferencia_url']);
        } catch (\Throwable $exception) {
            report($exception);
            $comercios = collect();
        }

        return view('app.paciente_dependiente.suscripcion_servicios', [
            'paciente' => $paciente,
            'mascotas' => $mascotas,
            'mascotaActiva' => $mascotaActiva,
            'petsData' => $petsData,
            'prescripcionesData' => $prescripcionesData,
            'comerciosIntegrados' => $comercios,
            'catalogoServiciosVeterinarios' => VeterinaryReservaCatalog::areas(),
        ]);
    }

    public function guardarSuscripcionServicio(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'id_mascota' => 'required|integer',
                'servicio' => 'required|string|in:alimentos,farmacia,pet_shop,entretencion',
                'lugar_nombre' => 'required|string|max:255',
                'lugar_direccion' => 'nullable|string|max:255',
                'items' => 'required|array|min:1',
                'items.*.name' => 'required|string|max:255',
                'items.*.quantity' => 'required|integer|min:1',
                'items.*.presentation' => 'required|string|max:255',
                'items.*.prescription_id' => 'nullable|integer|exists:detalles_receta,id',
                'items.*.attach_prescription' => 'nullable|boolean',
            ],
            [
                'id_mascota.required' => 'Debe seleccionar una mascota.',
                'servicio.in' => 'El servicio seleccionado no admite suscripción.',
                'items.required' => 'Debe agregar al menos un producto.',
                'items.min' => 'Debe agregar al menos un producto.',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'estado' => 0,
                'msj' => 'Datos inválidos',
                'error' => $validator->errors(),
            ], 422);
        }

        $paciente = Paciente::where('id_usuario', Auth::id())->first();
        if (!$paciente) {
            return response()->json([
                'estado' => 0,
                'msj' => 'Responsable no encontrado',
            ], 404);
        }

        $mascota = Mascota::where('id', $request->input('id_mascota'))
            ->where('id_responsable', $paciente->id)
            ->first();

        if (!$mascota) {
            return response()->json([
                'estado' => 0,
                'msj' => 'Mascota no encontrada',
            ], 404);
        }

        $idsReceta = collect($request->input('items', []))
            ->pluck('prescription_id')->filter()->map(fn ($id) => (int) $id)->unique();
        $recetasValidas = collect();
        if ($idsReceta->isNotEmpty()) {
            $recetasValidas = DetalleReceta::query()
                ->join('fichas_atenciones as ficha', 'ficha.id', '=', 'detalles_receta.id_ficha')
                ->where('ficha.id_mascota', $mascota->id)
                ->whereIn('detalles_receta.id', $idsReceta)
                ->get([
                    'detalles_receta.id',
                    'detalles_receta.producto',
                    'detalles_receta.presentacion',
                    'detalles_receta.cantidad_compra',
                    'detalles_receta.posologia',
                    'detalles_receta.created_at',
                ])
                ->keyBy('id');
            if ($recetasValidas->count() !== $idsReceta->count()) {
                return response()->json([
                    'estado' => 0,
                    'msj' => 'Una de las recetas no corresponde a la mascota seleccionada.',
                ], 422);
            }
        }

        $suscripciones = $this->normalizarRegistros($mascota->suscripciones_servicios_registro);
        $registro = [
            'id' => uniqid('sus_', true),
            'servicio' => $request->input('servicio'),
            'lugar_nombre' => trim((string) $request->input('lugar_nombre')),
            'lugar_direccion' => trim((string) $request->input('lugar_direccion', '')),
            'items' => collect($request->input('items', []))
                ->map(function ($item) use ($recetasValidas) {
                    $prescriptionId = !empty($item['prescription_id']) ? (int) $item['prescription_id'] : null;
                    $receta = $prescriptionId ? $recetasValidas->get($prescriptionId) : null;
                    $adjuntarReceta = $receta && filter_var($item['attach_prescription'] ?? false, FILTER_VALIDATE_BOOLEAN);

                    return [
                        'name' => trim((string) ($item['name'] ?? '')),
                        'quantity' => (int) ($item['quantity'] ?? 0),
                        'presentation' => trim((string) ($item['presentation'] ?? '')),
                        'prescription_id' => $prescriptionId,
                        'attach_prescription' => (bool) $adjuntarReceta,
                        'prescription_attachment' => $adjuntarReceta ? [
                            'id' => (int) $receta->id,
                            'producto' => (string) $receta->producto,
                            'presentacion' => (string) ($receta->presentacion ?: ''),
                            'cantidad' => max(1, (int) ($receta->cantidad_compra ?: 1)),
                            'posologia' => (string) ($receta->posologia ?: ''),
                            'fecha' => optional($receta->created_at)->toDateTimeString(),
                        ] : null,
                    ];
                })
                ->filter(function ($item) {
                    return $item['name'] !== '' && $item['quantity'] > 0 && $item['presentation'] !== '';
                })
                ->values()
                ->all(),
            'estado' => 'pendiente',
            'created_at' => now()->toDateTimeString(),
        ];

        $suscripciones[] = $registro;
        $mascota->suscripciones_servicios_registro = $suscripciones;
        $mascota->save();

        return response()->json([
            'estado' => 1,
            'msj' => 'Suscripción registrada correctamente',
            'registro' => $registro,
            'suscripciones' => $this->normalizarRegistros($mascota->suscripciones_servicios_registro),
        ]);
    }

    public function guardarReservaServicio(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'id_mascota' => 'required|integer',
                'servicio' => 'required|string|in:' . implode(',', $this->serviciosReservaCercanosPermitidos()),
                'lugar_nombre' => 'required|string|max:255',
                'lugar_direccion' => 'nullable|string|max:255',
                'fecha' => 'required|date',
                'hora' => 'required|date_format:H:i',
            ],
            [
                'id_mascota.required' => 'Debe seleccionar una mascota.',
                'servicio.in' => 'El servicio seleccionado no admite reserva.',
                'fecha.required' => 'Debe seleccionar una fecha.',
                'hora.required' => 'Debe seleccionar una hora.',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'estado' => 0,
                'msj' => 'Datos inválidos',
                'error' => $validator->errors(),
            ], 422);
        }

        $paciente = Paciente::where('id_usuario', Auth::id())->first();
        if (!$paciente) {
            return response()->json([
                'estado' => 0,
                'msj' => 'Responsable no encontrado',
            ], 404);
        }

        $mascota = Mascota::where('id', $request->input('id_mascota'))
            ->where('id_responsable', $paciente->id)
            ->first();

        if (!$mascota) {
            return response()->json([
                'estado' => 0,
                'msj' => 'Mascota no encontrada',
            ], 404);
        }

        $reservas = $this->normalizarRegistros($mascota->reservas_servicios_registro);
        $registro = [
            'id' => uniqid('res_', true),
            'servicio' => $request->input('servicio'),
            'lugar_nombre' => trim((string) $request->input('lugar_nombre')),
            'lugar_direccion' => trim((string) $request->input('lugar_direccion', '')),
            'fecha' => $request->input('fecha'),
            'hora' => $request->input('hora'),
            'estado' => 'pendiente',
            'created_at' => now()->toDateTimeString(),
        ];

        $reservas[] = $registro;
        $mascota->reservas_servicios_registro = $reservas;
        $mascota->save();

        return response()->json([
            'estado' => 1,
            'msj' => 'Reserva registrada correctamente',
            'registro' => $registro,
            'reservas' => $this->normalizarRegistros($mascota->reservas_servicios_registro),
        ]);
    }

    public function inscripcion_alimentos(Request $request){
        return redirect()->route('paciente.mascotas.suscripcion_servicios', [
            'servicio' => 'alimentos',
            'id_dependiente_activo' => $request->input('id_dependiente_activo'),
        ]);
    }

    public function inscripcion_medicamentos(Request $request){
        return redirect()->route('paciente.mascotas.suscripcion_servicios', [
            'servicio' => 'farmacia',
            'id_dependiente_activo' => $request->input('id_dependiente_activo'),
        ]);
    }

    public function promociones_especiales(){
        $paciente = Paciente::where('id_usuario', Auth::id())->first();
        $direccion = $paciente?->Direccion;
        $ubicacionUsuario = $direccion?->Ciudad?->nombre;

        $comunas = collect([
            'Santiago', 'Providencia', 'Las Condes', 'Ñuñoa', 'La Florida',
            'Maipú', 'Puente Alto', 'San Bernardo', 'Valparaíso', 'Viña del Mar',
            'Quilpué', 'Concepción', 'Talcahuano', 'Chillán', 'Temuco', 'Valdivia',
            'Osorno', 'Puerto Montt', 'Antofagasta', 'Calama', 'La Serena',
            'Coquimbo', 'Iquique', 'Arica',
        ]);

        if ($ubicacionUsuario && !$comunas->contains($ubicacionUsuario)) {
            $comunas->prepend($ubicacionUsuario);
        }

        return view('app.paciente_dependiente.promociones_especiales', compact(
            'ubicacionUsuario',
            'comunas'
        ));
    }

    public function promociones_generales(){
        return view('app.paciente_dependiente.promociones_generales');
    }

     public function pagos_suscripcion(){
        return view('app.paciente_dependiente.pagos_suscripcion');
    }

    public function razasPorEspecie($especie)
    {
        $razas = RazaMascota::where('especie_id', $especie)
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'especie_id']);

        return response()->json([
            'estado' => 1,
            'razas' => $razas,
        ]);
    }
    public function registro_vacunas(Request $request, $id_mascota = null)
    {
        $paciente = Paciente::where('id_usuario', Auth::user()->id)->first();
        $mascota = $this->resolverMascota($id_mascota ?? $request->route('id_mascota') ?? $request->input('id_dependiente_activo') ?? $request->input('id_mascota'));

        if (!$mascota) {
            return back()->with('error', 'Mascota no encontrada');
        }

        return view('app.paciente_dependiente.registro_vacunas', [
            'mascota' => $mascota,
            'paciente' => $paciente,
            'vacunas' => $this->normalizarRegistros($mascota->vacunas_registro),
        ]);
    }

    public function carnetSanitario($mascotaId)
    {
        $paciente = Paciente::where('id_usuario', Auth::id())->firstOrFail();

        $mascota = Mascota::with(['especieMascota', 'razaMascota', 'tamanoMascota', 'Responsable'])
            ->where('id', $mascotaId)
            ->where('id_responsable', $paciente->id)
            ->firstOrFail();

        return view('app.paciente.carnet_sanitario_mascota', [
            'mascota' => $mascota,
            'paciente' => $paciente,
            'vacunas' => $this->normalizarRegistros($mascota->vacunas_registro),
            'desparasitaciones' => $this->normalizarRegistros($mascota->desparasitaciones_registro),
        ]);
    }

    public function registro_desparasitacion(Request $request, $id_mascota = null)
    {
        $paciente = Paciente::where('id_usuario', Auth::user()->id)->first();
        $mascota = $this->resolverMascota($id_mascota ?? $request->route('id_mascota') ?? $request->input('id_dependiente_activo') ?? $request->input('id_mascota'));

        if (!$mascota) {
            return back()->with('error', 'Mascota no encontrada');
        }

        return view('app.paciente_dependiente.registro_desparasitacion',[
            'mascota' => $mascota,
            'paciente' => $paciente,
            'desparasitaciones' => $this->normalizarRegistros($mascota->desparasitaciones_registro),
        ]);
    }

    public function obtenerRegistrosSanitarios($mascotaId)
    {
        $mascota = $this->resolverMascota($mascotaId);
        if (!$mascota) {
            return response()->json([
                'estado' => 0,
                'msj' => 'Mascota no encontrada',
                'vacunas' => [],
                'desparasitaciones' => [],
            ], 404);
        }

        return [
            'estado' => 1,
            'mascota' => [
                'id' => $mascota->id,
                'nombre' => $mascota->nombre,
                'fecha_nacimiento' => optional($mascota->fecha_nacimiento)->format('Y-m-d'),
                'especie' => optional($mascota->especieMascota)->nombre
                    ?? $mascota->otra_especie
                    ?? $mascota->especie,
            ],
            'vacunas' => $this->normalizarRegistros($mascota->vacunas_registro),
            'desparasitaciones' => $this->normalizarRegistros($mascota->desparasitaciones_registro),
        ];
    }

    public function guardarVacunaDesdeModal(Request $request, $mascotaId)
    {
        $mascota = $this->resolverMascota($mascotaId);
        if (!$mascota) {
            return response()->json([
                'estado' => 0,
                'msj' => 'Mascota no encontrada',
            ], 404);
        }

        $validator = Validator::make(
            $request->all(),
            [
                'edad' => 'nullable|string|max:120',
                'fecha_dosis' => 'required|date',
                'vacuna' => 'required|string|max:255',
                'proxima_dosis' => 'nullable|date|after_or_equal:fecha_dosis',
                'especie' => 'nullable|string|in:canina,felina,otra',
            ],
            [
                'fecha_dosis.required' => 'Debe ingresar la fecha de dosis.',
                'fecha_dosis.date' => 'La fecha de dosis no es válida.',
                'vacuna.required' => 'Debe ingresar el nombre de la vacuna.',
                'vacuna.max' => 'El nombre de la vacuna no puede superar 255 caracteres.',
                'proxima_dosis.date' => 'La próxima dosis no es una fecha válida.',
                'proxima_dosis.after_or_equal' => 'La próxima dosis debe ser igual o posterior a la fecha de dosis.',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'estado' => 0,
                'msj' => 'Datos inválidos',
                'error' => $validator->errors(),
            ], 422);
        }

        $vacunas = $this->normalizarRegistros($mascota->vacunas_registro);
        $vacunas[] = [
            'id' => uniqid('vac_', true),
            'edad' => trim((string) $request->input('edad', '')),
            'fecha_dosis' => $request->input('fecha_dosis'),
            'vacuna' => trim((string) $request->input('vacuna')),
            'proxima_dosis' => $request->input('proxima_dosis'),
            'especie' => $request->input('especie'),
            'created_at' => now()->toDateTimeString(),
        ];

        $mascota->vacunas_registro = $vacunas;
        $mascota->save();

        return [
            'estado' => 1,
            'msj' => 'Vacuna agregada correctamente',
            'vacunas' => $this->normalizarRegistros($mascota->vacunas_registro),
        ];
    }

    public function guardarDesparasitacionDesdeModal(Request $request, $mascotaId)
    {
        $mascota = $this->resolverMascota($mascotaId);
        if (!$mascota) {
            return response()->json([
                'estado' => 0,
                'msj' => 'Mascota no encontrada',
            ], 404);
        }

        $validator = Validator::make(
            $request->all(),
            [
                'fecha_dosis' => 'required|date',
                'antiparasitario' => 'required|string|max:255',
                'tipo' => 'required|string|in:Externo,Interno,Interno y Externo',
                'via' => 'nullable|string|in:Oral,Tópica,Inyectable,Collar,Otra',
                'peso' => 'nullable|numeric|min:0|max:999.99',
                'dosis' => 'nullable|string|max:120',
                'lote' => 'nullable|string|max:120',
                'proxima_dosis' => 'nullable|date|after_or_equal:fecha_dosis',
            ],
            [
                'fecha_dosis.required' => 'Debe ingresar la fecha de dosis.',
                'fecha_dosis.date' => 'La fecha de dosis no es válida.',
                'antiparasitario.required' => 'Debe ingresar el antiparasitario.',
                'antiparasitario.max' => 'El antiparasitario no puede superar 255 caracteres.',
                'tipo.required' => 'Debe seleccionar el tipo.',
                'tipo.in' => 'El tipo seleccionado no es válido.',
                'proxima_dosis.date' => 'La próxima dosis no es una fecha válida.',
                'proxima_dosis.after_or_equal' => 'La próxima dosis debe ser igual o posterior a la fecha de dosis.',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'estado' => 0,
                'msj' => 'Datos inválidos',
                'error' => $validator->errors(),
            ], 422);
        }

        $desparasitaciones = $this->normalizarRegistros($mascota->desparasitaciones_registro);
        $desparasitaciones[] = [
            'id' => uniqid('des_', true),
            'fecha_dosis' => $request->input('fecha_dosis'),
            'antiparasitario' => trim((string) $request->input('antiparasitario')),
            'tipo' => $request->input('tipo'),
            'via' => $request->input('via'),
            'peso' => $request->filled('peso') ? (float) $request->input('peso') : null,
            'dosis' => trim((string) $request->input('dosis', '')),
            'lote' => trim((string) $request->input('lote', '')),
            'proxima_dosis' => $request->input('proxima_dosis'),
            'created_at' => now()->toDateTimeString(),
        ];

        $mascota->desparasitaciones_registro = $desparasitaciones;
        $mascota->ultima_desparasitacion = $request->input('fecha_dosis');
        $mascota->producto_desparasitacion = trim((string) $request->input('antiparasitario'));
        $mascota->save();

        return [
            'estado' => 1,
            'msj' => 'Desparasitación agregada correctamente',
            'desparasitaciones' => $this->normalizarRegistros($mascota->desparasitaciones_registro),
        ];
    }

    private function serviciosReservaCercanosPermitidos(): array
    {
        $catalogo = collect(VeterinaryReservaCatalog::areas())->pluck('slug')->all();

        return array_values(array_unique(array_merge(
            $catalogo,
            ['peluqueria', 'hotel_mascotas', 'procedimiento', 'servicios']
        )));
    }

    private function normalizarRegistros($registros)
    {
        if (empty($registros)) {
            return [];
        }

        if (is_string($registros)) {
            $decoded = json_decode($registros, true);
            $registros = is_array($decoded) ? $decoded : [];
        }

        if (!is_array($registros)) {
            return [];
        }

        return array_values(array_filter($registros, function ($item) {
            return is_array($item);
        }));
    }

    private function resolverMascota($id)
    {
        if (empty($id)) {
            return null;
        }

        $mascota = Mascota::find($id);
        if ($mascota) {
            return $mascota;
        }

        // Compatibilidad con IDs legacy (id_paciente dependiente).
        $dependencia = PacientesDependientes::where('id_paciente', $id)->first();
        if (!$dependencia) {
            return null;
        }

        $pacienteDependiente = Paciente::find($id);
        $nombreMascota = trim((string) ($pacienteDependiente->nombres ?? ''));
        if ($nombreMascota === '') {
            $nombreMascota = 'Mascota #' . $id;
        }

        $mascota = Mascota::where('id_responsable', $dependencia->id_responsable)
            ->where('nombre', $nombreMascota)
            ->first();

        return $mascota;
    }

    private function distribuirSolicitudApareamiento(Mascota $mascotaOrigen, array $solicitudEmitida, ?Paciente $tutorOrigen): int
    {
        $compatibles = $this->buscarMascotasCompatiblesApareamiento($mascotaOrigen, $solicitudEmitida);
        $enviados = 0;
        $tutoresNotificados = [];

        foreach ($compatibles as $mascotaDestino) {
            $registros = $this->normalizarRegistros($mascotaDestino->solicitudes_apareamiento_registro);

            $yaExiste = collect($registros)->contains(function ($item) use ($solicitudEmitida) {
                return ($item['solicitud_origen_id'] ?? '') === ($solicitudEmitida['id'] ?? '');
            });

            if ($yaExiste) {
                continue;
            }

            $registros[] = array_merge($solicitudEmitida, [
                'tipo' => 'recibida',
                'solicitud_origen_id' => $solicitudEmitida['id'],
                'mascota_origen_id' => $mascotaOrigen->id,
                'mascota_origen_nombre' => $mascotaOrigen->nombre,
                'mascota_origen_sexo' => $mascotaOrigen->sexo,
                'mascota_origen_raza' => optional($mascotaOrigen->razaMascota)->nombre,
                'tutor_origen_nombre' => $solicitudEmitida['tutor_nombre'] ?? '',
                'estado' => 'nueva',
                'created_at' => now()->toDateTimeString(),
            ]);

            $mascotaDestino->solicitudes_apareamiento_registro = $registros;
            $mascotaDestino->save();
            $enviados++;

            $responsableId = (int) $mascotaDestino->id_responsable;
            if ($responsableId > 0 && !isset($tutoresNotificados[$responsableId])) {
                $this->enviarMensajeApareamientoTutor($responsableId, $mascotaOrigen, $mascotaDestino, $solicitudEmitida);
                $tutoresNotificados[$responsableId] = true;
            }
        }

        return $enviados;
    }

    private function buscarMascotasCompatiblesApareamiento(Mascota $mascotaOrigen, array $solicitud): Collection
    {
        $query = Mascota::query()
            ->with(['razaMascota', 'tamanoMascota', 'especieMascota', 'genealogia'])
            ->where('estado', 1)
            ->where('id', '!=', $mascotaOrigen->id)
            ->where('id_responsable', '!=', $mascotaOrigen->id_responsable);

        if ($mascotaOrigen->especie_id) {
            $query->where('especie_id', $mascotaOrigen->especie_id);
        }

        $sexoBuscado = $solicitud['sexo_buscado'] ?? '';
        if (in_array($sexoBuscado, ['M', 'F'], true)) {
            $query->where('sexo', $sexoBuscado);
        }

        if (!empty($solicitud['tamano_id'])) {
            $query->where('tamano_id', $solicitud['tamano_id']);
        }

        $query->where(function ($sub) {
            $sub->where('esterilizado', false)->orWhereNull('esterilizado');
        });

        return $query->get()->filter(function (Mascota $candidata) use ($solicitud) {
            if (!$this->mascotaCumpleRazaApareamiento($candidata, (string) ($solicitud['raza_buscada'] ?? ''))) {
                return false;
            }

            if (!$this->mascotaCumpleEdadApareamiento($candidata, $solicitud)) {
                return false;
            }

            return $this->mascotaCumplePedigreeApareamiento($candidata, (string) ($solicitud['pedigree'] ?? 'indiferente'));
        })->values();
    }

    private function mascotaCumpleRazaApareamiento(Mascota $mascota, string $razaBuscada): bool
    {
        $razaBuscada = $this->normalizarTextoComparacion($razaBuscada);
        if ($razaBuscada === '') {
            return true;
        }

        $razaMascota = $this->normalizarTextoComparacion(optional($mascota->razaMascota)->nombre ?? '');
        if ($razaMascota === '') {
            return false;
        }

        return str_contains($razaMascota, $razaBuscada) || str_contains($razaBuscada, $razaMascota);
    }

    private function mascotaCumpleEdadApareamiento(Mascota $mascota, array $solicitud): bool
    {
        $edadMascota = $this->obtenerEdadAniosMascota($mascota);
        $edadMin = $this->parseEdadAniosTexto($solicitud['edad_minima'] ?? null);
        $edadMax = $this->parseEdadAniosTexto($solicitud['edad_maxima'] ?? null);

        if ($edadMin === null && $edadMax === null) {
            return true;
        }

        if ($edadMascota === null) {
            return false;
        }

        if ($edadMin !== null && $edadMascota < $edadMin) {
            return false;
        }

        if ($edadMax !== null && $edadMascota > $edadMax) {
            return false;
        }

        return true;
    }

    private function mascotaCumplePedigreeApareamiento(Mascota $mascota, string $pedigree): bool
    {
        if ($pedigree === 'indiferente') {
            return true;
        }

        $tieneGenealogia = $mascota->relationLoaded('genealogia')
            ? $mascota->genealogia !== null
            : $mascota->genealogia()->exists();

        return $pedigree === 'si' ? $tieneGenealogia : !$tieneGenealogia;
    }

    private function parseEdadAniosTexto(?string $texto): ?float
    {
        $texto = trim((string) $texto);
        if ($texto === '') {
            return null;
        }

        if (preg_match('/(\d+(?:[.,]\d+)?)/', $texto, $coincidencias)) {
            return (float) str_replace(',', '.', $coincidencias[1]);
        }

        return null;
    }

    private function obtenerEdadAniosMascota(Mascota $mascota): ?float
    {
        if (!$mascota->fecha_nacimiento) {
            return null;
        }

        return round($mascota->fecha_nacimiento->diffInMonths(now()) / 12, 1);
    }

    private function normalizarTextoComparacion(?string $texto): string
    {
        $texto = mb_strtolower(trim((string) $texto));

        return preg_replace('/\s+/', ' ', $texto) ?? '';
    }

    private function enviarMensajeApareamientoTutor(
        int $responsableId,
        Mascota $mascotaOrigen,
        Mascota $mascotaDestino,
        array $solicitud
    ): void {
        try {
            $sexoLabel = ['M' => 'macho', 'F' => 'hembra', 'I' => 'indiferente'][$solicitud['sexo_buscado'] ?? ''] ?? 'indiferente';
            $mensajeTexto = "Hay una solicitud de apareamiento compatible para {$mascotaDestino->nombre}.\n\n";
            $mensajeTexto .= "Mascota solicitante: {$mascotaOrigen->nombre}\n";
            $mensajeTexto .= "Busca: {$sexoLabel}, raza {$solicitud['raza_buscada']}\n";

            if (!empty($solicitud['edad_minima']) || !empty($solicitud['edad_maxima'])) {
                $mensajeTexto .= 'Edad: '
                    . ($solicitud['edad_minima'] ?: 'sin mínimo')
                    . ' - '
                    . ($solicitud['edad_maxima'] ?: 'sin máximo')
                    . "\n";
            }

            if (!empty($solicitud['ubicacion'])) {
                $mensajeTexto .= "Ubicación: {$solicitud['ubicacion']}\n";
            }

            if (!empty($solicitud['observaciones'])) {
                $mensajeTexto .= "\nDetalle: {$solicitud['observaciones']}\n";
            }

            $mensajeTexto .= "\nRevise la bandeja en Mis Mascotas > Solicitud de apareamiento.";

            $nuevoMensaje = new Mensajes();
            $nuevoMensaje->id_usuario = Auth::id();
            $nuevoMensaje->id_receptor = $responsableId;
            $nuevoMensaje->estado = 1;
            $nuevoMensaje->datos_mensaje = json_encode([
                'asunto' => 'Solicitud de apareamiento compatible',
                'mensaje' => $mensajeTexto,
                'tipo' => 'apareamiento',
                'solicitud_id' => $solicitud['id'] ?? null,
                'mascota_origen_id' => $mascotaOrigen->id,
                'mascota_destino_id' => $mascotaDestino->id,
            ], JSON_UNESCAPED_UNICODE);
            $nuevoMensaje->tipo_mensaje = 5;
            $nuevoMensaje->fecha_envio = Carbon::now()->format('Y-m-d H:i:s');
            $nuevoMensaje->save();
        } catch (\Throwable $e) {
            Log::warning('No fue posible enviar mensaje de apareamiento.', [
                'responsable_id' => $responsableId,
                'message' => $e->getMessage(),
            ]);
        }
    }
}

