<?php

use App\Services\CuentasService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{DB, Schema};
use Spatie\Permission\Models\Role;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('usuario_roles', fn (Blueprint $t) => $t->enum('tipo', CuentasService::TIPOS)->change());
        Role::findOrCreate('GuarderiaHotel', 'web');
        Role::findOrCreate('CementerioMascotas', 'web');

        Schema::create('centros_servicios_mascotas', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('id_usuario');
            $t->string('tipo', 32);
            $t->string('nombre', 150);
            $t->string('direccion', 250);
            $t->string('telefono', 30);
            $t->string('email', 254);
            $t->boolean('publicado')->default(false);
            $t->json('configuracion')->nullable();
            $t->timestamps();
            $t->unique(['id_usuario', 'tipo']);
        });
        Schema::create('cementerio_lugares', function (Blueprint $t) {
            $t->id();
            $t->foreignId('centro_id')->constrained('centros_servicios_mascotas');
            $t->string('codigo', 50);
            $t->string('sector', 100);
            $t->string('descripcion', 250)->nullable();
            $t->unsignedInteger('precio');
            $t->boolean('disponible')->default(true);
            $t->timestamps();
            $t->unique(['centro_id', 'codigo']);
        });
        Schema::create('servicios_mascotas_solicitudes', function (Blueprint $t) {
            $t->id();
            $t->foreignId('centro_id')->constrained('centros_servicios_mascotas');
            $t->foreignId('lugar_id')->nullable()->constrained('cementerio_lugares');
            $t->unsignedBigInteger('tutor_id')->index();
            $t->unsignedBigInteger('mascota_id')->index();
            $t->string('servicio', 30);
            $t->string('estado', 20);
            $t->date('inicio');
            $t->date('fin');
            $t->string('periodo', 15)->nullable();
            $t->unsignedSmallInteger('cantidad')->default(1);
            $t->boolean('retiro')->default(false);
            $t->string('direccion_retiro', 250)->nullable();
            $t->boolean('peluqueria')->default(false);
            $t->unsignedBigInteger('precio')->nullable();
            $t->text('indicaciones')->nullable();
            $t->timestamps();
            $t->index(['centro_id', 'servicio', 'estado', 'inicio'], 'serv_mascota_capacidad');
        });
        Schema::create('servicios_mascotas_bitacora', function (Blueprint $t) {
            $t->id();
            $t->foreignId('solicitud_id')->constrained('servicios_mascotas_solicitudes');
            $t->unsignedBigInteger('usuario_id');
            $t->string('tipo', 30);
            $t->text('detalle');
            $t->timestamps();
        });
    }

    public function down(): void
    {
        if (DB::table('usuario_roles')->whereIn('tipo', ['guarderia_hotel', 'cementerio_mascotas'])->exists()) {
            throw new RuntimeException('Existen perfiles activos de Guardería y Hotel o Cementerio de Mascotas.');
        }
        Schema::dropIfExists('servicios_mascotas_bitacora');
        Schema::dropIfExists('servicios_mascotas_solicitudes');
        Schema::dropIfExists('cementerio_lugares');
        Schema::dropIfExists('centros_servicios_mascotas');
        Schema::table('usuario_roles', fn (Blueprint $t) => $t->enum('tipo', ['tutor', 'profesional', 'asistente', 'clinica', 'peluqueria', 'centro_estetico'])->change());
    }
};
