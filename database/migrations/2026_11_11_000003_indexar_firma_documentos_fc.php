<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        $mysql = in_array(DB::connection()->getDriverName(), ['mysql', 'mariadb'], true);
        if ($mysql) {
            $cuerpo = collect(Schema::getColumns('documento_fc_paciente'))->firstWhere('name', 'cuerpo');
            if (in_array($cuerpo['type_name'], ['varchar', 'tinytext', 'text', 'mediumtext'], true)) {
                Schema::table('documento_fc_paciente', function (Blueprint $table) use ($cuerpo) {
                    $table->longText('cuerpo')->nullable($cuerpo['nullable'])->collation($cuerpo['collation'])->change();
                });
            }
        }

        Schema::table('documento_fc_paciente', function (Blueprint $table) use ($mysql) {
            $token = $table->string('firma_token', 64)->nullable()->unique();
            if ($mysql) {
                $token->charset('ascii')->collation('ascii_bin');
            }
        });

        DB::table('documento_fc_paciente')->select(['id', 'cuerpo'])
            ->where('otro', 'consentimiento_informado_veterinario')
            ->chunkById(500, function ($documentos) {
                foreach ($documentos as $documento) {
                    $cuerpo = json_decode((string) $documento->cuerpo, true);
                    $token = $cuerpo['firma_tutor']['token'] ?? null;
                    if (is_string($token) && preg_match('/\A[A-Za-z0-9]{64}\z/', $token)) {
                        DB::table('documento_fc_paciente')->where('id', $documento->id)
                            ->whereNull('firma_token')->update(['firma_token' => $token]);
                    }
                }
            });
    }

    public function down(): void
    {
        Schema::table('documento_fc_paciente', function (Blueprint $table) {
            $table->dropUnique(['firma_token']);
            $table->dropColumn('firma_token');
        });
    }
};
