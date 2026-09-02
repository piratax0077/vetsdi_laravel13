<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SdiRegistry
{
    public function tutor(array $data): ?array { return $this->post('tutors', $data); }
    public function pet(array $data): ?array { return $this->post('pets', $data); }

    private function post(string $resource, array $data): ?array
    {
        if (!config('services.sdi_hub.enabled')) return null;
        try {
            $response = Http::acceptJson()->timeout((int) config('services.sdi_hub.timeout', 5))
                ->withHeaders(['X-SDI-App' => config('services.sdi_hub.app'), 'X-SDI-Key' => config('services.sdi_hub.key')])
                ->post(rtrim(config('services.sdi_hub.url'), '/').'/api/v1/registry/'.$resource, $data);
            if ($response->failed()) Log::warning('SDI Hub rechazó sincronización', ['resource' => $resource, 'status' => $response->status(), 'body' => $response->json()]);
            return $response->successful() ? $response->json() : null;
        } catch (\Throwable $e) {
            Log::warning('SDI Hub no disponible; la operación local continúa', ['resource' => $resource, 'error' => $e->getMessage()]);
            return null;
        }
    }
}
