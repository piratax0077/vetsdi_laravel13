<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

return new class extends Migration {
    private const COLUMNAS = [
        'profesional_id' => 'id_profesional',
        'logo' => 'logo_ruta',
        'ruta' => 'archivo_ruta',
        'archivo' => 'archivo_nombre',
        'mime' => 'archivo_mime',
    ];

    public function up(): void
    {
        Schema::rename('vet_plantillas_documentos', 'documentos_profesional');
        Schema::table('documentos_profesional', function (Blueprint $table) {
            foreach (self::COLUMNAS as $anterior => $actual) {
                $table->renameColumn($anterior, $actual);
            }
            $table->unsignedBigInteger('id_lugar_atencion')->nullable()->index();
            $table->unsignedBigInteger('archivo_peso')->nullable();
            $table->softDeletes();
        });
        if (in_array(DB::connection()->getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::statement('ALTER TABLE documentos_profesional ENGINE=InnoDB');
        }

        DB::table('documentos_profesional')->where('seccion', 'indicaciones')->update(['seccion' => 'indicacion']);
        DB::table('documentos_profesional')->where('seccion', 'consentimientos')->update(['seccion' => 'consentimiento']);

        $disco = Storage::disk('local');
        DB::table('documentos_profesional')->whereNotNull('archivo_ruta')->chunkById(200, function ($plantillas) use ($disco) {
            foreach ($plantillas as $plantilla) {
                if ($disco->exists($plantilla->archivo_ruta)) {
                    DB::table('documentos_profesional')->where('id', $plantilla->id)
                        ->update(['archivo_peso' => $disco->size($plantilla->archivo_ruta)]);
                }
            }
        });

        DB::table('documentos_profesional')->where('alcance', 'compartida')->chunkById(200, function ($plantillas) {
            foreach ($plantillas as $plantilla) {
                $lugares = DB::table('profesionales_lugares_atencion')->where('id_profesional', $plantilla->id_profesional)
                    ->where('estado', 1)->where('id_lugar_atencion', '>', 0)->distinct()->limit(2)->pluck('id_lugar_atencion');
                if ($lugares->count() === 1) {
                    DB::table('documentos_profesional')->where('id', $plantilla->id)
                        ->update(['id_lugar_atencion' => $lugares->first()]);
                }
            }
        });
    }

    public function down(): void
    {
        DB::table('documentos_profesional')->whereNotNull('deleted_at')->delete();
        DB::table('documentos_profesional')->where('seccion', 'indicacion')->update(['seccion' => 'indicaciones']);
        DB::table('documentos_profesional')->where('seccion', 'consentimiento')->update(['seccion' => 'consentimientos']);
        Schema::table('documentos_profesional', function (Blueprint $table) {
            $table->dropIndex(['id_lugar_atencion']);
            $table->dropColumn(['id_lugar_atencion', 'archivo_peso', 'deleted_at']);
            foreach (self::COLUMNAS as $anterior => $actual) {
                $table->renameColumn($actual, $anterior);
            }
        });
        Schema::rename('documentos_profesional', 'vet_plantillas_documentos');
    }
};
