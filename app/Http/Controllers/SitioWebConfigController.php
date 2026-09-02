<?php

namespace App\Http\Controllers;

use App\Models\LugarAtencion;
use App\Models\SitioWeb;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SitioWebConfigController extends Controller
{
    public function editar()
    {
        $usuario = User::with([
            'profesional.Especialidad',
            'profesional.TipoEspecialidad',
            'institucion',
            'sitioWeb.lugares',
            'lugaresAtencion',
        ])->findOrFail(Auth::id());
        $sitio = $this->sitioDe($usuario);
        $sitio->sincronizarLugaresConectados();
        $sitio->load('lugares');
        $lugares = $this->lugaresDisponibles($usuario, $sitio);

        return view('sitio.configurar', [
            'usuario' => $usuario,
            'sitio' => $sitio,
            'lugares' => $lugares,
            'seleccionados' => $sitio->lugares->pluck('id')->all(),
        ]);
    }

    public function guardar(Request $request)
    {
        $usuario = User::with(['profesional.Especialidad', 'profesional.TipoEspecialidad', 'institucion', 'sitioWeb'])->findOrFail(Auth::id());

        $datos = $request->validate([
            'titulo' => 'required|string|max:160',
            'slug' => 'required|string|max:80|regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
            'slogan' => 'nullable|string|max:180',
            'descripcion' => 'nullable|string|max:400',
            'telefono' => 'nullable|string|max:40',
            'email' => 'nullable|email|max:200',
            'whatsapp' => 'nullable|string|max:40',
            'instagram' => 'nullable|string|max:180',
            'facebook' => 'nullable|string|max:180',
            'tiktok' => 'nullable|string|max:180',
            'youtube' => 'nullable|string|max:180',
            'linkedin' => 'nullable|string|max:180',
            'web' => 'nullable|string|max:180',
            'publicado' => 'nullable|boolean',
            'lugares' => 'nullable|array',
            'lugares.*' => 'integer|exists:lugares_atencion,id',
        ]);

        $sitio = $this->sitioDe($usuario);
        $slug = SitioWeb::slugUnico(strtolower($datos['slug']), $sitio->id);
        $permitidos = $this->lugaresDisponibles($usuario, $sitio)->pluck('id')->all();
        $lugares = collect($datos['lugares'] ?? [])
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => in_array($id, $permitidos, true))
            ->unique()
            ->values()
            ->all();

        $sitio->fill([
            'titulo' => $datos['titulo'],
            'slogan' => $datos['slogan'] ?? null,
            'descripcion' => $datos['descripcion'] ?? null,
            'telefono' => $datos['telefono'] ?? null,
            'email' => $datos['email'] ?? $usuario->email,
            'whatsapp' => $datos['whatsapp'] ?? null,
            'instagram' => $datos['instagram'] ?? null,
            'facebook' => $datos['facebook'] ?? null,
            'tiktok' => $datos['tiktok'] ?? null,
            'youtube' => $datos['youtube'] ?? null,
            'linkedin' => $datos['linkedin'] ?? null,
            'web' => $datos['web'] ?? null,
            'publicado' => $request->boolean('publicado'),
            'slug' => $slug,
        ]);
        $sitio->save();
        $sitio->lugares()->sync($lugares);

        foreach ($lugares as $idLugar) {
            $usuario->lugaresAtencion()->syncWithoutDetaching([$idLugar]);
        }

        return redirect()
            ->route('sitio.configurar')
            ->with('mensaje', 'Sitio web actualizado. Las agendas de los lugares seleccionados quedan publicadas.');
    }

    private function sitioDe(User $usuario): SitioWeb
    {
        if ($usuario->sitioWeb) {
            return $usuario->sitioWeb;
        }

        $profesional = $usuario->profesional;
        $institucion = $usuario->institucion;
        $esClinica = $usuario->hasRole(['Institucion', 'Adm_Institucion']) && !$profesional;

        $titulo = $profesional
            ? trim($profesional->nombre.' '.$profesional->apellido_uno.' '.$profesional->apellido_dos)
            : ($institucion->nombre ?? $usuario->name ?? 'Mi sitio');

        $slug = $profesional
            ? SitioWeb::slugProfesional($profesional->nombre, $profesional->apellido_uno)
            : SitioWeb::slugClinica($titulo);

        return SitioWeb::create([
            'id_usuario' => $usuario->id,
            'tipo' => $esClinica ? 'clinica' : 'profesional',
            'slug' => $slug,
            'titulo' => $titulo !== '' ? $titulo : 'Mi sitio Vet SDI',
            'slogan' => $profesional
                ? (optional($profesional->TipoEspecialidad)->nombre ?: optional($profesional->Especialidad)->nombre)
                : 'Clínica veterinaria',
            'email' => $profesional?->email ?? $institucion?->email ?? $usuario->email,
            'telefono' => $profesional?->telefono_uno ?? $profesional?->telefono ?? $institucion?->telefono ?? null,
            'publicado' => false,
        ]);
    }

    private function lugaresDisponibles(User $usuario, SitioWeb $sitio)
    {
        $ids = collect($sitio->idsLugaresConectados());

        if ($ids->isEmpty()) {
            return collect();
        }

        return LugarAtencion::with('Direccion.Ciudad.Region')
            ->whereIn('id', $ids)
            ->orderBy('nombre')
            ->get();
    }
}
