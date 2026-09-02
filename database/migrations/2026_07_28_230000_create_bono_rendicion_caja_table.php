<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bono_rendicion_caja', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_bono');
            $table->unsignedBigInteger('id_rendicion_caja');
            $table->timestamps();

            $table->unique(['id_bono', 'id_rendicion_caja'], 'bono_rendicion_unique');
            $table->index('id_rendicion_caja');
        });

        $bonosExistentes = DB::table('bonos')->pluck('id')->mapWithKeys(
            fn ($id) => [(int) $id => true]
        );

        DB::table('rendicion_caja')
            ->select(['id', 'bonos'])
            ->whereNotNull('bonos')
            ->orderBy('id')
            ->chunkById(100, function ($rendiciones) use ($bonosExistentes): void {
                $filas = [];
                $ahora = now();

                foreach ($rendiciones as $rendicion) {
                    $ids = collect(preg_split('/[|,;]+/', (string) $rendicion->bonos))
                        ->map(fn ($id) => (int) trim($id))
                        ->filter(fn ($id) => $id > 0 && isset($bonosExistentes[$id]))
                        ->unique();

                    foreach ($ids as $idBono) {
                        $filas[] = [
                            'id_bono' => $idBono,
                            'id_rendicion_caja' => $rendicion->id,
                            'created_at' => $ahora,
                            'updated_at' => $ahora,
                        ];
                    }
                }

                if ($filas !== []) {
                    DB::table('bono_rendicion_caja')->insertOrIgnore($filas);
                }
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('bono_rendicion_caja');
    }
};
