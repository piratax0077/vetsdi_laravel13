<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('horas_medicas', function (Blueprint $table) {
            if (! Schema::hasColumn('horas_medicas', 'voucher_externo_id')) {
                $table->unsignedBigInteger('voucher_externo_id')->nullable()->after('id_mascota');
            }
            if (! Schema::hasColumn('horas_medicas', 'voucher_codigo')) {
                $table->string('voucher_codigo', 100)->nullable()->after('voucher_externo_id');
            }
            if (! Schema::hasColumn('horas_medicas', 'voucher_estado')) {
                $table->string('voucher_estado', 40)->nullable()->after('voucher_codigo');
            }
        });
    }

    public function down(): void
    {
        Schema::table('horas_medicas', function (Blueprint $table) {
            $columns = array_filter(
                ['voucher_externo_id', 'voucher_codigo', 'voucher_estado'],
                fn (string $column) => Schema::hasColumn('horas_medicas', $column)
            );

            if ($columns) {
                $table->dropColumn($columns);
            }
        });
    }
};
