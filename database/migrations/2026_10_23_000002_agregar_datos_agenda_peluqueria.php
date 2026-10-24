<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Datos nuevos del modal "Agendar hora". Todas las columnas son opcionales: lo que ya guarda o lee
// la peluquería (registro de clientes, ficha, historial) sigue funcionando igual
return new class extends Migration {
    private array $clientes = ['rut', 'nombres', 'apellido_uno', 'direccion', 'id_ciudad', 'id_region', 'especie_exotico'];
    private array $citas = ['peso', 'procedimiento_precio_id', 'forma_pago', 'monto_recibido', 'estado_pago'];

    public function up(): void {
        Schema::table('peluqueria_clientes', function (Blueprint $t) {
            $nueva = fn ($columna) => !Schema::hasColumn('peluqueria_clientes', $columna);
            if ($nueva('rut')) { $t->string('rut', 12)->nullable(); $t->index(['peluqueria_id', 'rut']); }
            if ($nueva('nombres')) $t->string('nombres', 150)->nullable();
            if ($nueva('apellido_uno')) $t->string('apellido_uno', 100)->nullable();
            if ($nueva('direccion')) $t->string('direccion')->nullable();
            if ($nueva('id_ciudad')) $t->unsignedBigInteger('id_ciudad')->nullable();
            if ($nueva('id_region')) $t->unsignedBigInteger('id_region')->nullable();
            if ($nueva('especie_exotico')) $t->string('especie_exotico', 100)->nullable();
        });
        Schema::table('peluqueria_citas', function (Blueprint $t) {
            $nueva = fn ($columna) => !Schema::hasColumn('peluqueria_citas', $columna);
            // Peso con que se calculó el precio y el precio por especie y tamaño que se usó
            if ($nueva('peso')) $t->decimal('peso', 5, 1)->nullable();
            if ($nueva('procedimiento_precio_id')) $t->unsignedBigInteger('procedimiento_precio_id')->nullable();
            // efectivo / en_linea, y pagado / pendiente
            if ($nueva('forma_pago')) $t->string('forma_pago', 20)->nullable();
            if ($nueva('monto_recibido')) $t->unsignedInteger('monto_recibido')->nullable();
            if ($nueva('estado_pago')) $t->string('estado_pago', 20)->nullable();
        });
    }

    public function down(): void {
        $existentes = fn ($tabla, $columnas) => array_values(array_filter($columnas, fn ($c) => Schema::hasColumn($tabla, $c)));
        $clientes = $existentes('peluqueria_clientes', $this->clientes);
        $citas = $existentes('peluqueria_citas', $this->citas);
        if ($clientes) {
            Schema::table('peluqueria_clientes', function (Blueprint $t) use ($clientes) {
                if (in_array('rut', $clientes, true)) $t->dropIndex(['peluqueria_id', 'rut']);
                $t->dropColumn($clientes);
            });
        }
        if ($citas) Schema::table('peluqueria_citas', fn (Blueprint $t) => $t->dropColumn($citas));
    }
};
