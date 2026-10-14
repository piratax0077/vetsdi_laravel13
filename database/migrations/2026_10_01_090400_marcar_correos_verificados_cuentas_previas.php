<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * La verificacion de correo pasa a ser obligatoria solo para las cuentas nuevas.
 * Las que ya estaban creadas quedan verificadas para que nadie quede fuera.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')
            ->whereNull('email_verified_at')
            ->update(['email_verified_at' => now()]);
    }

    public function down(): void
    {
        // No se revierte: no hay forma de saber cuales estaban verificados antes.
    }
};
