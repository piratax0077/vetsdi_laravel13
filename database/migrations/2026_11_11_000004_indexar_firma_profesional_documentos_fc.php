<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        $mysql = in_array(DB::connection()->getDriverName(), ['mysql', 'mariadb'], true);
        Schema::table('documento_fc_paciente', function (Blueprint $table) use ($mysql) {
            $token = $table->string('firma_profesional_token', 64)->nullable()->unique();
            if ($mysql) {
                $token->charset('ascii')->collation('ascii_bin');
            }
        });
    }

    public function down(): void
    {
        Schema::table('documento_fc_paciente', function (Blueprint $table) {
            $table->dropUnique(['firma_profesional_token']);
            $table->dropColumn('firma_profesional_token');
        });
    }
};
