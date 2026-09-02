<?php

namespace App\Http\Controllers;

use App\Models\Mascota;
use App\Models\MascotaGenealogia;
use App\Models\EspecieMascota;
use App\Models\Paciente;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

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

    public function index()
    {
        $mascotas = $this->mascotasPermitidas()->with(['especieMascota', 'razaMascota'])->orderBy('nombre')->get();

        return view('app.paciente.genealogia.index', compact('mascotas'));
    }

    public function show(Mascota $mascota)
    {
        $this->autorizarMascota($mascota);
        $mascota->load(self::RELACIONES);
        $mascotas = $this->mascotasPermitidas()->whereKeyNot($mascota->id)->orderBy('nombre')->get();
        $especies = EspecieMascota::orderBy('nombre')->get(['id', 'nombre', 'requiere_detalle']);
        $crias = MascotaGenealogia::with('mascota.especieMascota')
            ->where(fn (Builder $query) => $query->where('padre_id', $mascota->id)->orWhere('madre_id', $mascota->id))
            ->get();

        return view('app.paciente.genealogia.show', compact('mascota', 'mascotas', 'crias', 'especies'));
    }

    public function store(Request $request, Mascota $mascota)
    {
        $this->autorizarMascota($mascota);
        $idsPermitidos = $this->mascotasPermitidas()->whereKeyNot($mascota->id)->pluck('id');
        $reglaMascota = ['nullable', 'integer', Rule::in($idsPermitidos->all())];

        $datos = $request->validate([
            'especie_id' => ['required', 'integer', Rule::exists('especies_mascotas', 'id')],
            'padre_id' => $reglaMascota,
            'madre_id' => $reglaMascota,
            'abuelo_paterno_id' => $reglaMascota,
            'abuela_paterna_id' => $reglaMascota,
            'abuelo_materno_id' => $reglaMascota,
            'abuela_materna_id' => $reglaMascota,
            'padre_nombre' => 'nullable|string|max:255',
            'madre_nombre' => 'nullable|string|max:255',
            'abuelo_paterno_nombre' => 'nullable|string|max:255',
            'abuela_paterna_nombre' => 'nullable|string|max:255',
            'abuelo_materno_nombre' => 'nullable|string|max:255',
            'abuela_materna_nombre' => 'nullable|string|max:255',
            'padre_especie' => 'nullable|in:Canino,Felino,Otro',
            'madre_especie' => 'nullable|in:Canino,Felino,Otro',
            'abuelo_paterno_especie' => 'nullable|in:Canino,Felino,Otro',
            'abuela_paterna_especie' => 'nullable|in:Canino,Felino,Otro',
            'abuelo_materno_especie' => 'nullable|in:Canino,Felino,Otro',
            'abuela_materna_especie' => 'nullable|in:Canino,Felino,Otro',
            'numero_registro' => 'nullable|string|max:255',
            'criador' => 'nullable|string|max:255',
            'observaciones' => 'nullable|string|max:5000',
            'foto_mascota' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'padre_foto' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'madre_foto' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'abuelo_paterno_foto' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'abuela_paterna_foto' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'abuelo_materno_foto' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'abuela_materna_foto' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
        ]);

        $archivos = [
            'padre_foto', 'madre_foto',
            'abuelo_paterno_foto', 'abuela_paterna_foto',
            'abuelo_materno_foto', 'abuela_materna_foto',
        ];

        $especieId = (int) $datos['especie_id'];
        unset($datos['foto_mascota'], $datos['especie_id']);
        foreach ($archivos as $campo) {
            unset($datos[$campo]);
        }

        DB::transaction(function () use ($request, $mascota, $datos, $archivos, $especieId) {
            $genealogia = MascotaGenealogia::firstOrNew(['mascota_id' => $mascota->id]);
            $genealogia->fill($datos);

            foreach ($archivos as $campo) {
                if (!$request->hasFile($campo)) {
                    continue;
                }
                $this->eliminarFotoGenealogia($genealogia->{$campo});
                $genealogia->{$campo} = $this->guardarFoto(
                    $request->file($campo),
                    $mascota->id
                );
            }
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

    public function certificado(Mascota $mascota)
    {
        $this->autorizarMascota($mascota);
        $mascota->load(self::RELACIONES);

        return view('app.paciente.genealogia.certificado', compact('mascota'));
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
