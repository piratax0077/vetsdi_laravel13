<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class SitioWeb extends Model
{
    protected $table = 'sitios_web';

    protected $fillable = [
        'id_usuario',
        'tipo',
        'slug',
        'titulo',
        'slogan',
        'descripcion',
        'telefono',
        'email',
        'whatsapp',
        'instagram',
        'facebook',
        'tiktok',
        'youtube',
        'linkedin',
        'web',
        'publicado',
    ];

    protected $casts = [
        'publicado' => 'boolean',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }

    public function lugares(): BelongsToMany
    {
        return $this->belongsToMany(
            LugarAtencion::class,
            'sitio_web_lugares',
            'id_sitio_web',
            'id_lugar_atencion'
        )->withTimestamps();
    }

    public function scopePublicados($query)
    {
        return $query->where('publicado', true);
    }

    public function esClinica(): bool
    {
        return $this->tipo === 'clinica';
    }

    public function esProfesional(): bool
    {
        return $this->tipo === 'profesional';
    }

    public static function slugDesdeTitulo(string $titulo, ?int $exceptId = null): string
    {
        return static::slugUnico(Str::slug($titulo) ?: 'sitio', $exceptId);
    }

    public static function slugProfesional(string $nombre, string $apellidoUno = '', ?int $exceptId = null): string
    {
        $base = 'dr-'.preg_replace('/[^a-z0-9]/', '', Str::slug($nombre.$apellidoUno, ''));

        return static::slugUnico($base !== 'dr-' ? $base : 'dr-profesional', $exceptId);
    }

    public static function slugClinica(string $nombre, ?int $exceptId = null): string
    {
        return static::slugUnico(Str::slug($nombre) ?: 'clinica', $exceptId);
    }

    public static function slugUnico(string $base, ?int $exceptId = null): string
    {
        $base = strtolower(preg_replace('/[^a-z0-9\-]/', '', $base) ?: 'sitio');
        $slug = $base;
        $i = 2;

        while (static::query()
            ->when($exceptId, fn ($q) => $q->where('id', '!=', $exceptId))
            ->where('slug', $slug)
            ->exists() || static::slugEstaReservado($slug)) {
            $slug = $base.'-'.$i;
            $i++;
        }

        return $slug;
    }

    public static function slugEstaReservado(string $slug): bool
    {
        return in_array(strtolower($slug), [
            'ingreso', 'login', 'home', 'sitio', 'profesional', 'paciente', 'admin',
            'vetsdinicio', 'registro', 'css', 'js', 'images', 'storage', 'api', 'pdf',
            'configurar', 'dashboard', 'livewire', 'horizon', 'up', 'public',
        ], true);
    }

    public function urlPublica(bool $absolute = true): string
    {
        if ($absolute && function_exists('app_route_url')) {
            return app_route_url('sitio.ficha', ['slug' => $this->slug], '/'.$this->slug);
        }

        return '/'.$this->slug;
    }

    public function idsLugaresConectados(): array
    {
        $this->loadMissing(['usuario.profesional', 'usuario.institucion', 'usuario.lugaresAtencion']);

        $ids = collect();
        $usuario = $this->usuario;
        $profesional = $usuario?->profesional;

        if ($profesional) {
            $ids = $ids->merge(static::idsLugaresDelProfesional($profesional));
        }

        if ($usuario) {
            $ids = $ids->merge($usuario->lugaresAtencion()->pluck('lugares_atencion.id'));
        }

        $institucion = $usuario?->institucion;
        if ($institucion && $institucion->id_lugar_atencion) {
            foreach (preg_split('/[,\s]+/', (string) $institucion->id_lugar_atencion) as $idLugar) {
                if (is_numeric($idLugar)) {
                    $ids->push((int) $idLugar);
                }
            }
        }

        return $ids->filter()->map(fn ($id) => (int) $id)->unique()->values()->all();
    }

    public static function idsLugaresDelProfesional(Profesional $profesional): array
    {
        $lugares = $profesional->lugaresAtencionParaAgenda();

        if ($lugares->isEmpty()) {
            $lugares = $profesional->LugaresAtencion()
                ->get()
                ->filter(fn (LugarAtencion $lugar) => (int) ($lugar->pivot->estado ?? 0) !== 3)
                ->values();
        }

        return $lugares->pluck('id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();
    }

    public function lugaresPublicos(): Collection
    {
        $conectados = $this->idsLugaresConectados();
        $publicados = $this->lugares()->pluck('lugares_atencion.id')->map(fn ($id) => (int) $id)->all();
        $ids = $conectados;

        if ($publicados !== []) {
            $filtrados = array_values(array_intersect($conectados, $publicados));
            if ($filtrados !== []) {
                $ids = $filtrados;
            }
        }

        if ($ids === []) {
            return collect();
        }

        return LugarAtencion::with('Direccion.Ciudad.Region')
            ->whereIn('id', $ids)
            ->orderBy('nombre')
            ->get();
    }

    public function sincronizarLugaresConectados(): void
    {
        $ids = $this->idsLugaresConectados();

        if ($ids === []) {
            return;
        }

        $this->lugares()->syncWithoutDetaching($ids);
        $this->unsetRelation('lugares');
    }

    public function profesionalesEnLugares(Collection $lugares): Collection
    {
        $lugarIds = $lugares->pluck('id')->filter()->values();
        $titular = $this->usuario?->profesional;

        if ($this->esProfesional() && $titular) {
            return collect([$titular->loadMissing(['Especialidad', 'TipoEspecialidad'])]);
        }

        if ($lugarIds->isEmpty()) {
            return collect();
        }

        return Profesional::query()
            ->with(['Especialidad', 'TipoEspecialidad', 'Usuario.sitioWeb'])
            ->whereHas('LugaresAtencion', function ($query) use ($lugarIds) {
                $query->whereIn('lugares_atencion.id', $lugarIds)
                    ->where(function ($pivot) {
                        $pivot->where('profesionales_lugares_atencion.estado', 1)
                            ->orWhereNull('profesionales_lugares_atencion.estado');
                    });
            })
            ->orderBy('apellido_uno')
            ->orderBy('nombre')
            ->get();
    }

    public function horariosPublicos(Collection $lugares, Collection $equipo): Collection
    {
        $lugarIds = $lugares->pluck('id')->all();
        $profesionalIds = $equipo->pluck('id')->all();

        if ($lugarIds === [] || $profesionalIds === []) {
            return collect();
        }

        return ProfesionalHorario::query()
            ->whereIn('id_profesional', $profesionalIds)
            ->whereIn('id_lugar_atencion', $lugarIds)
            ->orderBy('dia')
            ->get();
    }

    public function redesPublicas(): Collection
    {
        return collect([
            ['key' => 'whatsapp', 'label' => 'WhatsApp', 'url' => $this->urlWhatsapp()],
            ['key' => 'instagram', 'label' => 'Instagram', 'url' => $this->urlRed('instagram', 'https://instagram.com/')],
            ['key' => 'facebook', 'label' => 'Facebook', 'url' => $this->urlRed('facebook', 'https://facebook.com/')],
            ['key' => 'tiktok', 'label' => 'TikTok', 'url' => $this->urlRed('tiktok', 'https://www.tiktok.com/@')],
            ['key' => 'youtube', 'label' => 'YouTube', 'url' => $this->urlRed('youtube', 'https://youtube.com/')],
            ['key' => 'linkedin', 'label' => 'LinkedIn', 'url' => $this->urlRed('linkedin', 'https://www.linkedin.com/in/')],
            ['key' => 'web', 'label' => 'Sitio web', 'url' => $this->urlRed('web')],
        ])->filter(fn (array $red) => !empty($red['url']))->values();
    }

    public function urlWhatsapp(): ?string
    {
        $numero = preg_replace('/\D+/', '', (string) $this->whatsapp);

        return $numero !== '' ? 'https://wa.me/'.$numero : null;
    }

    private function urlRed(string $campo, ?string $prefijo = null): ?string
    {
        $valor = trim((string) $this->{$campo});

        if ($valor === '') {
            return null;
        }

        if (Str::startsWith($valor, ['http://', 'https://'])) {
            return $valor;
        }

        $usuario = ltrim($valor, '@/');

        if ($prefijo === null) {
            return Str::startsWith($usuario, ['www.']) ? 'https://'.$usuario : 'https://'.$usuario;
        }

        return $prefijo.$usuario;
    }
}
