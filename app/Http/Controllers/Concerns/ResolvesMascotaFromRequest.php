<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\Request;

trait ResolvesMascotaFromRequest
{
    protected function resolveMascotaIdFromRequest(Request $request): ?int
    {
        $id = $request->route('id_mascota')
            ?? $request->route('id_dependiente_activo')
            ?? $request->input('id_mascota')
            ?? $request->input('id_dependiente_activo');

        return $id !== null && $id !== '' ? (int) $id : null;
    }
}
