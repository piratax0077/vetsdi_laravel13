<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('fotos:publicar-existentes', function () {
    $disk = \Illuminate\Support\Facades\Storage::disk('public');
    $copiadas = 0;
    foreach (['fotos_perfil', 'imagenes', 'images', 'mascotas'] as $directorio) {
        foreach ($disk->allFiles($directorio) as $ruta) {
            if (!in_array(strtolower(pathinfo($ruta, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif', 'webp'])) continue;
            if (is_file(public_path('storage/' . $ruta))) continue;
            app(\App\Services\Fotografias::class)->publicar($ruta);
            $copiadas++;
        }
    }
    $this->info("Fotografías publicadas: {$copiadas}. No se reemplazaron archivos existentes.");
})->purpose('Recuperar la copia pública de las fotografías existentes');
