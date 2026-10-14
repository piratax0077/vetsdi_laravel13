<?php
require __DIR__.'/../../vendor/autoload.php';$app=require __DIR__.'/../../bootstrap/app.php';$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
echo json_encode(Illuminate\Support\Facades\DB::select("SELECT TABLE_NAME, ENGINE, TABLE_ROWS, DATA_LENGTH FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND ENGINE='MyISAM'"),JSON_PRETTY_PRINT);
