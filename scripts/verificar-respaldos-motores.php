<?php

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$total = 0;
$archivos = glob(storage_path('app/auditoria/motores_resp/*.sql.gz.json'));
if (!$archivos) throw new RuntimeException('No hay manifiestos de respaldo.');
foreach ($archivos as $archivo) {
    $meta = json_decode(file_get_contents($archivo), true, 512, JSON_THROW_ON_ERROR);
    $tabla = $meta['tabla'];
    $motor = DB::selectOne('SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=?', [$tabla]);
    if (!$motor || strtoupper($motor->ENGINE) !== 'INNODB') throw new RuntimeException('Motor incorrecto: '.$tabla);
    $respaldo = substr($archivo, 0, -5);
    if (hash_file('sha256', $respaldo) !== $meta['sha256_respaldo'] || gzdecode(file_get_contents($respaldo)) === false) throw new RuntimeException('Respaldo inválido: '.$tabla);
    $filas = DB::table($tabla)->orderBy('id')->get()->map(fn ($r)=>(array)$r)->all();
    if (count($filas) !== $meta['registros'] || hash('sha256', json_encode($filas, JSON_THROW_ON_ERROR)) !== $meta['sha256_datos']) throw new RuntimeException('Los datos cambiaron desde la conversión: '.$tabla);
    $total += count($filas);
    echo $tabla.': InnoDB, respaldo íntegro, datos coincidentes.'.PHP_EOL;
}
echo count($archivos).' tablas; '.$total.' registros verificados.'.PHP_EOL;
