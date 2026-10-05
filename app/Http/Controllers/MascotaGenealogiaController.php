<?php

namespace App\Http\Controllers;

use App\Models\Mascota;
use App\Models\MascotaGenealogia;
use App\Models\MascotaHermano;
use App\Models\EspecieMascota;
use App\Models\Paciente;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class MascotaGenealogiaController extends Controller
{
    private const RELACIONES = [
        'genealogia.padre.especieMascota',
        'genealogia.madre.especieMascota',
        'genealogia.abueloPaterno.especieMascota',
        'genealogia.abuelaPaterna.especieMascota',
        'genealogia.abueloMaterno.especieMascota',
        'genealogia.abuelaMaterna.especieMascota',
        'especieMascota',
        'razaMascota',
    ];

    // Familiares de padres y abuelos: la clave es el prefijo de sus columnas en mascota_genealogias
    private const PARENTESCOS = [
        'padre' => ['relacion' => 'padre', 'etiqueta' => 'Padre', 'sexo' => 'M'],
        'madre' => ['relacion' => 'madre', 'etiqueta' => 'Madre', 'sexo' => 'F'],
        'abuelo_paterno' => ['relacion' => 'abueloPaterno', 'etiqueta' => 'Abuelo paterno', 'sexo' => 'M'],
        'abuela_paterna' => ['relacion' => 'abuelaPaterna', 'etiqueta' => 'Abuela paterna', 'sexo' => 'F'],
        'abuelo_materno' => ['relacion' => 'abueloMaterno', 'etiqueta' => 'Abuelo materno', 'sexo' => 'M'],
        'abuela_materna' => ['relacion' => 'abuelaMaterna', 'etiqueta' => 'Abuela materna', 'sexo' => 'F'],
    ];

    private const ESPECIES_EXTERNAS = ['Canino', 'Felino', 'Otro'];
    private const TIPOS_HERMANO = ['completo', 'medio_padre', 'medio_madre'];

    public function index()
    {
        $mascotas = $this->mascotasPermitidas()->with(['especieMascota', 'razaMascota'])->orderBy('nombre')->get();

        return view('app.paciente.genealogia.index', compact('mascotas'));
    }

    public function show(Mascota $mascota)
    {
        $this->autorizarMascota($mascota);
        $mascota->load(array_merge(self::RELACIONES, ['hermanosRegistrados.hermano.especieMascota']));
        $especies = EspecieMascota::orderBy('nombre')->get(['id', 'nombre', 'requiere_detalle']);
        $crias = MascotaGenealogia::with('mascota.especieMascota')
            ->where(fn (Builder $query) => $query->where('padre_id', $mascota->id)->orWhere('madre_id', $mascota->id))
            ->get();

        // lista para el select del formulario de familiares
        $opcionesMascotas = $this->mascotasPermitidas()
            ->with('especieMascota')
            ->whereKeyNot($mascota->id)
            ->orderBy('nombre')
            ->get()
            ->map(fn (Mascota $opcion) => [
                'id' => $opcion->id,
                'nombre' => $opcion->nombre,
                'sexo' => $opcion->sexo,
                'especie' => $opcion->tipo_especie,
                'foto' => $opcion->foto_url,
            ])
            ->values();

        return view('app.paciente.genealogia.show', [
            'mascota' => $mascota,
            'especies' => $especies,
            'crias' => $crias,
            'familiares' => $this->familiares($mascota),
            'hermanos' => $this->hermanos($mascota),
            'opcionesMascotas' => $opcionesMascotas,
            'parentescos' => self::PARENTESCOS,
        ]);
    }

    // Datos generales del pedigrí: especie y foto de la mascota, registro, criador y observaciones
    public function store(Request $request, Mascota $mascota)
    {
        $this->autorizarMascota($mascota);

        $datos = $request->validate([
            'especie_id' => ['required', 'integer', Rule::exists('especies_mascotas', 'id')],
            'numero_registro' => 'nullable|string|max:255',
            'criador' => 'nullable|string|max:255',
            'observaciones' => 'nullable|string|max:5000',
            'foto_mascota' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
        ]);

        $especieId = (int) $datos['especie_id'];
        unset($datos['foto_mascota'], $datos['especie_id']);

        DB::transaction(function () use ($request, $mascota, $datos, $especieId) {
            $genealogia = MascotaGenealogia::firstOrNew(['mascota_id' => $mascota->id]);
            $genealogia->fill($datos);
            $genealogia->save();

            $mascota->especie_id = $especieId;
            $mascota->especie = $especieId;

            if ($request->hasFile('foto_mascota')) {
                $this->eliminarFotoGenealogia($mascota->foto_perfil);
                $mascota->foto_perfil = $this->guardarFoto(
                    $request->file('foto_mascota'),
                    $mascota->id
                );
            }
            $mascota->save();
        });

        return redirect()->route('mascotas.genealogia.show', $mascota)->with('ok', 'Genealogía actualizada correctamente.');
    }

    // Formulario único: agrega o edita un familiar (padre, madre, abuelos o hermano)
    public function guardarFamiliar(Request $request, Mascota $mascota)
    {
        $this->autorizarMascota($mascota);
        $idsPermitidos = $this->mascotasPermitidas()->whereKeyNot($mascota->id)->pluck('id')->all();
        $esExterno = $request->input('origen') === 'externo';

        $datos = $request->validate([
            'parentesco' => ['required', Rule::in($this->clavesParentesco())],
            'origen' => ['required', Rule::in(['registrado', 'externo'])],
            'mascota_id' => ['nullable', Rule::requiredIf(!$esExterno), 'integer', Rule::in($idsPermitidos)],
            'nombre' => ['nullable', Rule::requiredIf($esExterno), 'string', 'max:255'],
            'especie' => ['nullable', Rule::requiredIf($esExterno), Rule::in(self::ESPECIES_EXTERNAS)],
            'sexo' => ['nullable', Rule::in(['M', 'F'])],
            'tipo' => ['nullable', Rule::in(self::TIPOS_HERMANO)],
            'registro_hermano_id' => ['nullable', 'integer'],
            'foto' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
        ], [
            'required' => 'Falta indicar :attribute.',
            'in' => 'El valor elegido en :attribute no es válido.',
            'mascota_id.in' => 'Elige una de tus mascotas de la lista.',
            'max' => 'El campo :attribute es demasiado largo.',
            'foto.image' => 'La foto debe ser una imagen.',
            'foto.mimes' => 'La foto debe ser JPG, PNG o WEBP.',
            'foto.max' => 'La foto no puede pesar más de 5 MB.',
        ], [
            'parentesco' => 'familiar',
            'origen' => 'origen',
            'mascota_id' => 'mascota registrada',
            'nombre' => 'nombre',
            'especie' => 'especie',
            'foto' => 'foto',
        ]);

        // si la mascota registrada ya tiene foto de perfil no se guarda otra
        $fotoNueva = $request->file('foto');
        if ($fotoNueva && !$esExterno && optional(Mascota::find($datos['mascota_id']))->foto_url) {
            $fotoNueva = null;
        }

        $mensaje = DB::transaction(fn () => $datos['parentesco'] === 'hermano'
            ? $this->guardarHermano($mascota, $datos, $fotoNueva)
            : $this->guardarProgenitor($mascota, $datos, $fotoNueva));

        return redirect()->route('mascotas.genealogia.show', $mascota)->with('ok', $mensaje);
    }

    public function quitarFamiliar(Request $request, Mascota $mascota)
    {
        $this->autorizarMascota($mascota);

        $datos = $request->validate([
            'parentesco' => ['required', Rule::in($this->clavesParentesco())],
            'registro_hermano_id' => ['nullable', 'integer', 'required_if:parentesco,hermano'],
        ]);

        if ($datos['parentesco'] === 'hermano') {
            $registro = MascotaHermano::where('mascota_id', $mascota->id)->findOrFail($datos['registro_hermano_id']);
            $this->eliminarFotoGenealogia($registro->foto);
            $registro->delete();
        } elseif ($genealogia = $mascota->genealogia) {
            $clave = $datos['parentesco'];
            $this->eliminarFotoGenealogia($genealogia->{"{$clave}_foto"});
            foreach (['id', 'nombre', 'especie', 'foto'] as $campo) {
                $genealogia->{"{$clave}_{$campo}"} = null;
            }
            $genealogia->save();
        }

        return redirect()->route('mascotas.genealogia.show', $mascota)->with('ok', 'Familiar quitado del árbol.');
    }

    public function certificado(Mascota $mascota)
    {
        $this->autorizarMascota($mascota);
        $mascota->load(array_merge(self::RELACIONES, ['hermanosRegistrados.hermano.especieMascota']));

        return view('app.paciente.genealogia.certificado', [
            'mascota' => $mascota,
            'familiares' => $this->familiares($mascota),
            'hermanos' => $this->hermanos($mascota),
            'parentescos' => self::PARENTESCOS,
        ]);
    }

    private function guardarProgenitor(Mascota $mascota, array $datos, $fotoNueva): string
    {
        $clave = $datos['parentesco'];
        $genealogia = MascotaGenealogia::firstOrNew(['mascota_id' => $mascota->id]);
        $existia = $genealogia->{"{$clave}_id"} || $genealogia->{"{$clave}_nombre"};

        if ($datos['origen'] === 'registrado') {
            $cambioFamiliar = $genealogia->{"{$clave}_id"} != $datos['mascota_id'];
            $genealogia->{"{$clave}_id"} = $datos['mascota_id'];
            $genealogia->{"{$clave}_nombre"} = null;
            $genealogia->{"{$clave}_especie"} = null;
        } else {
            $cambioFamiliar = (bool) $genealogia->{"{$clave}_id"};
            $genealogia->{"{$clave}_id"} = null;
            $genealogia->{"{$clave}_nombre"} = $datos['nombre'];
            $genealogia->{"{$clave}_especie"} = $datos['especie'];
        }

        $this->actualizarFoto($genealogia, "{$clave}_foto", $cambioFamiliar, $fotoNueva, $mascota->id);
        $genealogia->save();

        return $existia ? 'Familiar actualizado.' : 'Familiar agregado al árbol.';
    }

    private function guardarHermano(Mascota $mascota, array $datos, $fotoNueva): string
    {
        $registro = !empty($datos['registro_hermano_id'])
            ? MascotaHermano::where('mascota_id', $mascota->id)->findOrFail($datos['registro_hermano_id'])
            : new MascotaHermano(['mascota_id' => $mascota->id]);
        $existia = $registro->exists;

        if ($datos['origen'] === 'registrado') {
            $repetido = MascotaHermano::where('mascota_id', $mascota->id)
                ->where('hermano_id', $datos['mascota_id'])
                ->when($existia, fn ($query) => $query->whereKeyNot($registro->id))
                ->exists();
            if ($repetido) {
                throw ValidationException::withMessages(['mascota_id' => 'Esa mascota ya está en el árbol como hermano.']);
            }

            $cambioFamiliar = $registro->hermano_id != $datos['mascota_id'];
            $registro->fill(['hermano_id' => $datos['mascota_id'], 'nombre' => null, 'especie' => null, 'sexo' => null]);
        } else {
            $cambioFamiliar = (bool) $registro->hermano_id;
            $registro->fill([
                'hermano_id' => null,
                'nombre' => $datos['nombre'],
                'especie' => $datos['especie'],
                'sexo' => $datos['sexo'] ?? null,
            ]);
        }

        $registro->tipo = $datos['tipo'] ?? 'completo';
        $this->actualizarFoto($registro, 'foto', $cambioFamiliar, $fotoNueva, $mascota->id);
        $registro->save();

        return $existia ? 'Hermano actualizado.' : 'Hermano agregado al árbol.';
    }

    // Si cambió el familiar la foto anterior ya no le corresponde
    private function actualizarFoto(Model $modelo, string $campo, bool $cambioFamiliar, $fotoNueva, int $mascotaId): void
    {
        if ($cambioFamiliar || $fotoNueva) {
            $this->eliminarFotoGenealogia($modelo->{$campo});
            $modelo->{$campo} = null;
        }
        if ($fotoNueva) {
            $modelo->{$campo} = $this->guardarFoto($fotoNueva, $mascotaId);
        }
    }

    // Padre, madre y abuelos que tienen datos; los vacíos quedan en null
    private function familiares(Mascota $mascota): array
    {
        $genealogia = $mascota->genealogia;
        $familiares = [];

        foreach (self::PARENTESCOS as $clave => $parentesco) {
            $registrado = optional($genealogia)->{$parentesco['relacion']};
            $nombreExterno = optional($genealogia)->{"{$clave}_nombre"};

            if (!$registrado && !$nombreExterno) {
                $familiares[$clave] = null;
                continue;
            }

            $especieExterna = optional($genealogia)->{"{$clave}_especie"};
            $fotoGenealogia = storage_public_url(optional($genealogia)->{"{$clave}_foto"});

            $familiares[$clave] = [
                'parentesco' => $clave,
                'etiqueta' => $parentesco['etiqueta'],
                'origen' => $registrado ? 'registrado' : 'externo',
                'mascota_id' => optional($registrado)->id,
                'nombre' => $registrado ? $registrado->nombre : $nombreExterno,
                'especie' => $registrado ? $registrado->tipo_especie : ($especieExterna ?: 'Especie no indicada'),
                'especie_externa' => $especieExterna,
                'sexo' => $parentesco['sexo'],
                'foto' => optional($registrado)->foto_url ?: $fotoGenealogia,
                'foto_genealogia' => $fotoGenealogia,
            ];
        }

        return $familiares;
    }

    // Hermanos agregados a mano más los que comparten padre o madre registrados
    private function hermanos(Mascota $mascota)
    {
        $manuales = $mascota->hermanosRegistrados
            ->map(function (MascotaHermano $registro) {
                $hermano = $registro->hermano;
                $fotoGenealogia = storage_public_url($registro->foto);
                $sexo = $hermano ? $hermano->sexo : $registro->sexo;

                return [
                    'id' => $registro->id,
                    'automatico' => false,
                    'origen' => $hermano ? 'registrado' : 'externo',
                    'mascota_id' => optional($hermano)->id,
                    'nombre' => $hermano ? $hermano->nombre : $registro->nombre,
                    'especie' => $hermano ? $hermano->tipo_especie : ($registro->especie ?: 'Especie no indicada'),
                    'especie_externa' => $registro->especie,
                    'sexo' => $sexo,
                    'tipo' => $registro->tipo,
                    'etiqueta' => $this->etiquetaHermano($sexo, $registro->tipo),
                    'foto' => optional($hermano)->foto_url ?: $fotoGenealogia,
                    'foto_genealogia' => $fotoGenealogia,
                ];
            })
            ->filter(fn ($hermano) => $hermano['nombre']);

        $idsManuales = $manuales->pluck('mascota_id')->filter()->all();

        $automaticos = $this->hermanosPorPadres($mascota)
            ->reject(fn ($registro) => in_array($registro->mascota_id, $idsManuales))
            ->map(fn ($registro) => [
                'id' => null,
                'automatico' => true,
                'origen' => 'registrado',
                'mascota_id' => $registro->mascota->id,
                'nombre' => $registro->mascota->nombre,
                'especie' => $registro->mascota->tipo_especie,
                'especie_externa' => null,
                'sexo' => $registro->mascota->sexo,
                'tipo' => $registro->tipo_hermano,
                'etiqueta' => $this->etiquetaHermano($registro->mascota->sexo, $registro->tipo_hermano),
                'foto' => $registro->mascota->foto_url,
                'foto_genealogia' => null,
            ]);

        return $manuales->concat($automaticos)
            ->sortBy('nombre', SORT_NATURAL | SORT_FLAG_CASE)
            ->values();
    }

    // Hermanos y medios hermanos: comparten padre o madre registrados en el sistema
    private function hermanosPorPadres(Mascota $mascota)
    {
        $genealogia = $mascota->genealogia;
        if (!$genealogia || (!$genealogia->padre_id && !$genealogia->madre_id)) {
            return collect();
        }

        return MascotaGenealogia::with('mascota.especieMascota')
            ->where('mascota_id', '!=', $mascota->id)
            ->where(function (Builder $query) use ($genealogia) {
                if ($genealogia->padre_id) {
                    $query->orWhere('padre_id', $genealogia->padre_id);
                }
                if ($genealogia->madre_id) {
                    $query->orWhere('madre_id', $genealogia->madre_id);
                }
            })
            ->get()
            ->filter(fn ($registro) => $registro->mascota)
            ->map(function ($registro) use ($genealogia) {
                // solo es medio hermano si se sabe que el otro progenitor es distinto
                $padreDistinto = $genealogia->padre_id && $registro->padre_id && $registro->padre_id != $genealogia->padre_id;
                $madreDistinta = $genealogia->madre_id && $registro->madre_id && $registro->madre_id != $genealogia->madre_id;
                $registro->tipo_hermano = $madreDistinta ? 'medio_padre' : ($padreDistinto ? 'medio_madre' : 'completo');

                return $registro;
            });
    }

    private function etiquetaHermano(?string $sexo, ?string $tipo): string
    {
        if ($tipo === 'medio_padre' || $tipo === 'medio_madre') {
            $medio = $sexo === 'F' ? 'Media hermana' : ($sexo === 'M' ? 'Medio hermano' : 'Medio hermano/a');

            return $medio . ($tipo === 'medio_padre' ? ' (padre)' : ' (madre)');
        }

        return $sexo === 'F' ? 'Hermana' : ($sexo === 'M' ? 'Hermano' : 'Hermano/a');
    }

    private function clavesParentesco(): array
    {
        return array_merge(array_keys(self::PARENTESCOS), ['hermano']);
    }

    private function mascotasPermitidas(): Builder
    {
        $query = Mascota::query();
        $paciente = Paciente::where('id_usuario', Auth::id())->first();

        return $paciente ? $query->where('id_responsable', $paciente->id) : $query;
    }

    private function autorizarMascota(Mascota $mascota): void
    {
        abort_unless($this->mascotasPermitidas()->whereKey($mascota->id)->exists(), 403);
    }

    private function eliminarFotoGenealogia(?string $ruta): void
    {
        if ($ruta && str_starts_with($ruta, 'mascotas/genealogia/')) {
            File::delete(public_path('storage/' . $ruta));
        }
    }

    private function guardarFoto($archivo, int $mascotaId): string
    {
        $rutaRelativa = "mascotas/genealogia/{$mascotaId}";
        $directorio = public_path('storage/' . $rutaRelativa);
        File::ensureDirectoryExists($directorio);
        $nombre = Str::uuid() . '.' . strtolower($archivo->getClientOriginalExtension());
        $archivo->move($directorio, $nombre);

        return $rutaRelativa . '/' . $nombre;
    }
}
