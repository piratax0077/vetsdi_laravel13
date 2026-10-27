<?php
require __DIR__.'/../vendor/autoload.php';$app=require __DIR__.'/../bootstrap/app.php';$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;
$tablas=['users','usuario_roles','roles','permissions','model_has_roles','model_has_permissions','role_has_permissions','peluquerias','profesionales_estetica','peluqueria_personal','peluqueria_sucursales','peluqueria_procedimientos','peluqueria_bloqueos','peluqueria_clientes','peluqueria_citas','peluqueria_fichas','pacientes','mascotas','especies_mascotas','razas_mascotas','tamanos_mascotas','especie_tamano_mascota','verificaciones_correo','password_resets','registro_correo','cache','cache_locks','sessions','jobs','job_batches','failed_jobs','regiones','ciudades'];
$resultado=DB::table('information_schema.TABLES')->where('TABLE_SCHEMA',DB::connection()->getDatabaseName())->whereIn('TABLE_NAME',$tablas)->select('TABLE_NAME','TABLE_ROWS',DB::raw('ROUND((DATA_LENGTH+INDEX_LENGTH)/1024/1024,2) as MB'))->get();
echo json_encode($resultado,JSON_PRETTY_PRINT).PHP_EOL;
