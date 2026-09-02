<?php

namespace App\Services;

use App\Models\Mascota;
use App\Models\Paciente;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VeterinaryVoucherService
{
    public function disponibles(Paciente $tutor, Mascota $mascota): Collection
    {
        $rut = $this->normalizarRut($tutor->rut);
        if ($rut === '') {
            return collect();
        }

        try {
            return DB::connection('bono_veterinario')
                ->table('vouchers')
                ->select([
                    'id',
                    'codigo',
                    'tipo_servicio',
                    'valor',
                    'estado',
                    'fecha_vencimiento',
                    'mascota_nombre',
                ])
                ->where('cliente_rut_hash', hash('sha256', $rut))
                ->whereIn('estado', ['activo', 'vigente', 'pagado', 'validado_atencion'])
                ->where(function ($query) {
                    $query->whereNull('qr_usado')->orWhere('qr_usado', false);
                })
                ->where(function ($query) {
                    $query->whereNull('fecha_vencimiento')
                        ->orWhereDate('fecha_vencimiento', '>=', now()->toDateString());
                })
                ->where(function ($query) use ($mascota) {
                    $query->whereNull('mascota_nombre')
                        ->orWhere('mascota_nombre', '')
                        ->orWhereRaw('LOWER(TRIM(mascota_nombre)) = ?', [mb_strtolower(trim((string) $mascota->nombre))]);
                })
                ->orderByDesc('id')
                ->get();
        } catch (\Throwable $exception) {
            Log::warning('No fue posible consultar los vouchers veterinarios.', [
                'tutor_id' => $tutor->id,
                'mascota_id' => $mascota->id,
                'error' => $exception->getMessage(),
            ]);

            return collect();
        }
    }

    public function disponible(int $voucherId, Paciente $tutor, Mascota $mascota): ?object
    {
        return $this->disponibles($tutor, $mascota)->firstWhere('id', $voucherId);
    }

    private function normalizarRut(?string $rut): string
    {
        return strtoupper((string) preg_replace('/[^0-9K]/i', '', (string) $rut));
    }
}
