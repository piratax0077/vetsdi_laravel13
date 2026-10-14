<?php
require __DIR__.'/../../vendor/autoload.php';
$app=require __DIR__.'/../../bootstrap/app.php';$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
foreach(['solicitudes_soporte','con_consentimientos_pcte','log_users_devices'] as $table) echo $table.': '.json_encode(Illuminate\Support\Facades\Schema::getColumnListing($table)).PHP_EOL;
