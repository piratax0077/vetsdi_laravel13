<?php

namespace App\Support;

use App\Models\AdminInstServ;
use App\Models\Instituciones;
use App\Models\LugarAtencion;
use App\Models\Profesional;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

final class UserCenterContext
{
    public static function forAdmin(User $user, Request $request): array
    {
        $adminIds = AdminInstServ::where('id_admin', $user->id)->pluck('id');

        $institutionIds = Instituciones::query()
            ->where('id_usuario', $user->id)
            ->when($adminIds->isNotEmpty(), fn ($query) => $query->orWhereIn('id_responsable', $adminIds))
            ->pluck('id');

        if (Schema::hasTable('administrativos_lugar_atencion') && $adminIds->isNotEmpty()) {
            $institutionIds = $institutionIds->merge(
                DB::table('administrativos_lugar_atencion')
                    ->whereIn('id_admin', $adminIds)
                    ->whereNotNull('id_institucion')
                    ->pluck('id_institucion')
            );
        }

        // Conserva el acceso histórico del administrador maestro utilizado por Vet SDI.
        if ((int) $user->id === 3 && Instituciones::whereKey(5)->exists()) {
            $institutionIds->prepend(5);
        }

        $institutions = Instituciones::whereIn('id', $institutionIds->filter()->unique())
            ->orderByRaw((int) $user->id === 3 ? 'id = 5 DESC, nombre ASC' : 'nombre ASC')
            ->get();

        $contexts = $institutions->flatMap(fn (Instituciones $institution) => self::adminContextsForInstitution($institution, $adminIds));

        return self::result($contexts, $request, 'admin_center_context');
    }

    public static function forProfessional(User $user, Request $request): array
    {
        $professional = Profesional::where('id_usuario', $user->id)->first();
        if (!$professional || !Schema::hasTable('profesionales_lugares_atencion')) {
            return ['contexts' => collect(), 'active' => null];
        }

        $links = DB::table('profesionales_lugares_atencion')
            ->where('id_profesional', $professional->id)
            ->where('estado', 1)
            ->get();

        $places = LugarAtencion::whereIn('id', $links->pluck('id_lugar_atencion')->filter()->unique())->get()->keyBy('id');
        $institutions = Instituciones::whereIn('id', $links->pluck('id_institucion')->filter()->unique())->get()->keyBy('id');

        $contexts = $links->map(function ($link) use ($places, $institutions) {
            $placeId = (int) $link->id_lugar_atencion;
            $institutionId = !empty($link->id_institucion)
                ? (int) $link->id_institucion
                : LugarAtencionInstitucionResolver::resolve($placeId);
            $place = $places->get($placeId);
            $institution = $institutions->get($institutionId) ?: ($institutionId ? Instituciones::find($institutionId) : null);

            return self::context(
                'profesional:'.($institutionId ?: 0).':'.$placeId,
                $institutionId,
                $placeId,
                [$placeId],
                trim(($institution->nombre ?? 'Centro veterinario').' · '.($place->nombre ?? 'Lugar '.$placeId)),
                'Profesional'
            );
        })->unique('key')->values();

        return self::result($contexts, $request, 'professional_center_context');
    }

    private static function adminContextsForInstitution(Instituciones $institution, Collection $adminIds): Collection
    {
        $placeIds = self::parseIds($institution->id_lugar_atencion);
        $branchNames = collect();

        if (Schema::hasTable('sucursal')) {
            $branches = DB::table('sucursal')
                ->where('id_institucion', $institution->id)
                ->where('estado', 1)
                ->get();
            $placeIds = $placeIds->merge($branches->pluck('id_lugar_atencion'));
            $branchNames = $branches->filter(fn ($branch) => !empty($branch->id_lugar_atencion))->keyBy('id_lugar_atencion');
        }

        if (Schema::hasTable('administrativos_lugar_atencion') && $adminIds->isNotEmpty()) {
            $placeIds = $placeIds->merge(
                DB::table('administrativos_lugar_atencion')
                    ->whereIn('id_admin', $adminIds)
                    ->where('id_institucion', $institution->id)
                    ->where('estado', 1)
                    ->pluck('id_lugar_atencion')
            );
        }

        $placeIds = $placeIds->filter()->map(fn ($id) => (int) $id)->filter()->unique()->values();
        $places = LugarAtencion::whereIn('id', $placeIds)->get()->keyBy('id');
        $contexts = collect();

        $contexts->push(self::context(
            'admin:'.$institution->id.':all',
            (int) $institution->id,
            $placeIds->first(),
            $placeIds->all(),
            $institution->nombre.($placeIds->count() > 1 ? ' · Todas las sucursales' : ''),
            'Administrador General'
        ));

        if ($placeIds->count() > 1) {
            foreach ($placeIds as $placeId) {
                $label = $branchNames->get($placeId)->nombre ?? $places->get($placeId)->nombre ?? 'Sucursal '.$placeId;
                $contexts->push(self::context(
                    'admin:'.$institution->id.':'.$placeId,
                    (int) $institution->id,
                    $placeId,
                    [$placeId],
                    $institution->nombre.' · '.$label,
                    'Administrador de Sucursal'
                ));
            }
        }

        return $contexts;
    }

    private static function result(Collection $contexts, Request $request, string $sessionKey): array
    {
        $contexts = $contexts->filter()->unique('key')->values();
        $requestedKey = (string) $request->query('contexto', '');
        $storedKey = (string) $request->session()->get($sessionKey, '');
        $active = $contexts->firstWhere('key', $requestedKey)
            ?? $contexts->firstWhere('key', $storedKey)
            ?? $contexts->first();

        if ($active) {
            $request->session()->put($sessionKey, $active['key']);
        }

        return ['contexts' => $contexts, 'active' => $active];
    }

    private static function context(string $key, ?int $institutionId, ?int $placeId, array $scope, string $label, string $roleLabel): array
    {
        return [
            'key' => $key,
            'id_institucion' => $institutionId,
            'id_lugar_atencion' => $placeId,
            'scope_lugar_ids' => array_values(array_unique(array_map('intval', array_filter($scope)))),
            'label' => $label,
            'role_label' => $roleLabel,
        ];
    }

    private static function parseIds($value): Collection
    {
        if (is_array($value)) {
            return collect($value);
        }

        return collect(preg_split('/\s*,\s*/', (string) $value, -1, PREG_SPLIT_NO_EMPTY));
    }
}
