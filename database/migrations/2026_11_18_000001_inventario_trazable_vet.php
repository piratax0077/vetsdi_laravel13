<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('vet_inventario_lotes', function (Blueprint $t) {
            $t->id();
            $t->string('titular', 60)->index();
            $t->string('categoria', 20);
            $t->string('producto', 180);
            $t->string('registro_sag', 100)->nullable();
            $t->string('condicion_venta', 30);
            $t->string('lote', 100);
            $t->date('vencimiento');
            $t->string('unidad', 40);
            $t->string('ubicacion', 150);
            $t->string('conservacion', 250);
            $t->decimal('saldo', 14, 3)->default(0);
            $t->timestamps();
            $t->unique(['titular', 'producto', 'lote', 'ubicacion'], 'vet_inventario_lote_unico');
        });
        Schema::create('vet_inventario_movimientos', function (Blueprint $t) {
            $t->id();
            $t->foreignId('lote_id')->constrained('vet_inventario_lotes');
            $t->unsignedBigInteger('usuario_id');
            $t->uuid('operacion')->unique();
            $t->string('tipo', 15);
            $t->decimal('cantidad', 14, 3);
            $t->decimal('saldo', 14, 3);
            $t->string('contraparte', 200);
            $t->string('documento', 150);
            $t->string('receta', 150)->nullable();
            $t->text('observaciones')->nullable();
            $t->timestamp('created_at');
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('vet_inventario_movimientos');
        Schema::dropIfExists('vet_inventario_lotes');
    }
};
