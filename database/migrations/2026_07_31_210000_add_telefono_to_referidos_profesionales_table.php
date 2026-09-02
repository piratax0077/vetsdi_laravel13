<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // El módulo ahora acepta tutores y comercios, por lo que el tipo deja de ser un ENUM cerrado.
        \DB::statement("ALTER TABLE referidos_profesionales MODIFY tipo VARCHAR(30) NOT NULL DEFAULT 'profesional'");
        if (!Schema::hasColumn('referidos_profesionales', 'telefono_invitado')) {
            Schema::table('referidos_profesionales', function (Blueprint $table) {
                $table->string('telefono_invitado', 30)->nullable()->after('email_invitado');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('referidos_profesionales', 'telefono_invitado')) {
            Schema::table('referidos_profesionales', function (Blueprint $table) {
                $table->dropColumn('telefono_invitado');
            });
        }
        \DB::statement("ALTER TABLE referidos_profesionales MODIFY tipo ENUM('profesional','centro') NOT NULL DEFAULT 'profesional'");
    }
};
