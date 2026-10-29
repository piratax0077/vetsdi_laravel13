<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Las referencias a tablas heredadas no asumen el ancho de sus claves primarias.
        Schema::create('vet_establecimientos', function (Blueprint $t) {
            $t->id(); $t->unsignedBigInteger('id_institucion')->unique();
            $t->string('tipo', 20)->default('centro');
            $t->boolean('hospitalizacion_habilitada')->default(true);
            $t->json('servicios_habilitados'); $t->timestamps();
        });
        Schema::create('vet_plazas', function (Blueprint $t) {
            $t->id(); $t->unsignedBigInteger('id_institucion')->index();
            $t->string('codigo', 50); $t->string('tipo', 30); $t->string('especies', 150)->nullable();
            $t->text('observaciones')->nullable(); $t->boolean('activa')->default(true); $t->timestamps();
            $t->unique(['id_institucion', 'codigo']);
        });
        Schema::create('vet_internaciones', function (Blueprint $t) {
            $t->id(); $t->unsignedBigInteger('id_institucion')->index(); $t->unsignedBigInteger('id_mascota')->index();
            $t->foreignId('plaza_id')->constrained('vet_plazas')->restrictOnDelete();
            $t->string('responsable', 150); $t->dateTime('ingreso'); $t->dateTime('egreso')->nullable();
            $t->string('estado', 25)->default('hospitalizada'); $t->text('motivo');
            $t->text('indicaciones')->nullable(); $t->text('observaciones_alta')->nullable();
            $t->unsignedBigInteger('id_usuario'); $t->timestamps();
            $t->index(['id_institucion', 'estado']);
        });
        Schema::create('vet_evoluciones', function (Blueprint $t) {
            $t->id(); $t->foreignId('internacion_id')->constrained('vet_internaciones')->restrictOnDelete();
            $t->dateTime('fecha'); $t->text('observaciones');
            $t->decimal('peso', 8, 3)->nullable(); $t->decimal('temperatura', 5, 2)->nullable();
            $t->unsignedBigInteger('id_usuario'); $t->timestamps();
        });
        Schema::create('vet_catalogo_servicios', function (Blueprint $t) {
            $t->id(); $t->unsignedBigInteger('id_institucion')->index(); $t->string('modulo', 30);
            $t->string('nombre', 150); $t->unsignedInteger('duracion_minutos');
            $t->unsignedBigInteger('precio')->default(0); $t->boolean('activo')->default(true); $t->timestamps();
            $t->unique(['id_institucion', 'modulo', 'nombre']);
        });
        Schema::create('vet_reservas_servicios', function (Blueprint $t) {
            $t->id(); $t->unsignedBigInteger('id_institucion')->index(); $t->unsignedBigInteger('id_mascota')->index();
            $t->foreignId('servicio_id')->constrained('vet_catalogo_servicios')->restrictOnDelete();
            $t->string('modulo', 30); $t->string('servicio', 150); $t->unsignedBigInteger('precio');
            $t->string('responsable', 150); $t->dateTime('inicio'); $t->dateTime('fin');
            $t->string('estado', 25)->default('reservada'); $t->text('observaciones')->nullable();
            $t->text('resultado')->nullable(); $t->unsignedBigInteger('id_usuario'); $t->timestamps();
            $t->index(['id_institucion', 'inicio', 'fin']);
        });
        Schema::create('vet_convocatorias', function (Blueprint $t) {
            $t->id(); $t->unsignedBigInteger('id_institucion')->index();
            $t->string('cargo', 150); $t->string('jornada', 100); $t->text('descripcion');
            $t->date('fecha_cierre')->nullable(); $t->string('estado', 20)->default('borrador');
            $t->unsignedBigInteger('id_usuario'); $t->timestamps();
        });
        Schema::create('vet_postulaciones', function (Blueprint $t) {
            $t->id(); $t->foreignId('convocatoria_id')->constrained('vet_convocatorias')->restrictOnDelete();
            $t->string('nombre', 150); $t->string('email'); $t->string('telefono', 30)->nullable();
            $t->string('estado', 20)->default('recibida'); $t->text('observaciones')->nullable();
            $t->unsignedBigInteger('id_usuario'); $t->timestamps();
            $t->unique(['convocatoria_id', 'email']);
        });
        Schema::create('vet_obligaciones', function (Blueprint $t) {
            $t->id(); $t->unsignedBigInteger('id_institucion')->index();
            $t->string('concepto', 150); $t->string('periodo', 7); $t->string('referencia', 100)->nullable();
            $t->unsignedBigInteger('monto'); $t->date('vencimiento'); $t->date('fecha_pago')->nullable();
            $t->string('estado', 20)->default('pendiente'); $t->text('observaciones')->nullable();
            $t->unsignedBigInteger('id_usuario'); $t->timestamps();
            $t->index(['id_institucion', 'estado', 'vencimiento']);
        });
        Schema::create('vet_operacion_auditoria', function (Blueprint $t) {
            $t->id(); $t->unsignedBigInteger('id_institucion')->index(); $t->unsignedBigInteger('id_usuario');
            $t->string('tabla', 50); $t->unsignedBigInteger('registro_id');
            $t->json('antes')->nullable(); $t->json('despues'); $t->timestamp('created_at');
        });
    }

    public function down(): void
    {
        foreach (['vet_operacion_auditoria', 'vet_obligaciones', 'vet_postulaciones', 'vet_convocatorias', 'vet_reservas_servicios', 'vet_catalogo_servicios', 'vet_evoluciones', 'vet_internaciones', 'vet_plazas', 'vet_establecimientos'] as $tabla) Schema::dropIfExists($tabla);
    }
};
