<?php

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Read-only smoke check: never saves a record or sends a notification.
$mascotas = \App\Models\Mascota::whereIn('id', \App\Models\FichaAtencion::select('id_mascota')->whereNotNull('id_mascota'))
    ->orderByDesc('id')->limit(6)->get();
$resultados = [];
foreach ($mascotas as $indice => $mascota) {
    try {
        $html = view('general.secciones_ficha.fmu_mascota', ['mascota' => $mascota, 'responsable_mascota' => $mascota->Responsable])->render();
        $resultados[] = ['muestra' => $indice + 1, 'correcto' => str_contains($html, 'fvu-vet-shell')];
    } catch (\Throwable $error) {
        $resultados[] = ['muestra' => $indice + 1, 'correcto' => false, 'tipo_error' => get_class($error), 'error' => preg_replace('/\s+/', ' ', substr($error->getMessage(), 0, 350))];
    }
}
echo json_encode(['muestras' => count($resultados), 'resultados' => $resultados], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE).PHP_EOL;
exit(collect($resultados)->contains('correcto', false) || !$resultados ? 1 : 0);
