<?php

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

if (DB::getDriverName() !== 'mysql') throw new RuntimeException('Se requiere MySQL.');
if ($app->isDownForMaintenance()) throw new RuntimeException('Ya existe mantenimiento; no se modificará su estado.');
if (!in_array('--autorizado', $argv, true)) throw new RuntimeException('Se requiere autorización explícita.');

try {
    if (Artisan::call('down', ['--retry'=>60]) !== 0) throw new RuntimeException('No se pudo activar mantenimiento.');
    echo 'Mantenimiento activado.'.PHP_EOL;
    // Allow existing web requests to complete before schema changes.
    sleep(5);
    $migration = require __DIR__.'/pendientes/2026_10_13_235100_make_veterinary_workflows_transactional.php';
    $migration->up();
    echo 'Conversión terminada.'.PHP_EOL;
} finally {
    Artisan::call('up');
    echo 'Aplicación disponible.'.PHP_EOL;
}
