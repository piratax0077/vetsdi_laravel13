<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{DB,Schema};
use Illuminate\Support\Str;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('vet_categorias_trabajadores',function(Blueprint $t){
            $t->id();$t->unsignedBigInteger('id_institucion')->index();$t->string('nombre',100);$t->string('clave',120);$t->boolean('activa')->default(true);$t->timestamps();$t->unique(['id_institucion','clave']);
        });
        Schema::create('vet_trabajadores',function(Blueprint $t){
            $t->id();$t->unsignedBigInteger('id_institucion')->index();$t->foreignId('categoria_id')->constrained('vet_categorias_trabajadores')->restrictOnDelete();
            $t->string('rut',20);$t->string('nombres',100);$t->string('apellido_uno',80);$t->string('apellido_dos',80)->nullable();
            $t->string('email')->nullable();$t->string('telefono',30)->nullable();$t->string('direccion')->nullable();
            $t->string('banco',100)->nullable();$t->string('tipo_cuenta',60)->nullable();$t->string('numero_cuenta',50)->nullable();
            $t->text('observaciones')->nullable();$t->boolean('activo')->default(true);$t->unsignedBigInteger('creado_por');$t->timestamps();
            $t->unique(['id_institucion','rut']);
        });
        Schema::create('vet_trabajador_lugares',function(Blueprint $t){
            $t->id();$t->foreignId('trabajador_id')->constrained('vet_trabajadores')->restrictOnDelete();$t->unsignedBigInteger('id_lugar_atencion')->index();$t->unique(['trabajador_id','id_lugar_atencion']);
        });
        Schema::create('vet_contratos_trabajadores',function(Blueprint $t){
            $t->id();$t->unsignedBigInteger('id_institucion')->index();$t->foreignId('trabajador_id')->constrained('vet_trabajadores')->restrictOnDelete();
            $t->string('tipo',25);$t->date('fecha_inicio');$t->date('fecha_fin')->nullable();$t->date('fecha_termino')->nullable();
            $t->decimal('horas_semanales',5,2)->nullable();$t->unsignedBigInteger('remuneracion')->default(0);$t->text('observaciones')->nullable();$t->string('estado',20)->default('vigente');
            $t->unsignedBigInteger('creado_por');$t->timestamps();$t->index(['id_institucion','estado']);
        });
        Schema::create('vet_mensajes_trabajadores',function(Blueprint $t){
            $t->id();$t->unsignedBigInteger('id_institucion')->index();$t->foreignId('trabajador_id')->constrained('vet_trabajadores')->restrictOnDelete();$t->unsignedBigInteger('id_usuario');
            $t->uuid('token')->unique();$t->string('destinatario');$t->string('asunto',200);$t->text('mensaje');$t->string('estado',20)->default('pendiente');$t->timestamp('enviado_en')->nullable();$t->timestamps();
        });
        if(Schema::hasTable('instituciones')) DB::table('instituciones')->select('id')->orderBy('id')->chunkById(100,function($instituciones){
            foreach($instituciones as $i)foreach(['Mantención','Limpieza','Contabilidad','Administración','Seguridad','Otros'] as $nombre)
                DB::table('vet_categorias_trabajadores')->insert(['id_institucion'=>$i->id,'nombre'=>$nombre,'clave'=>Str::slug($nombre),'activa'=>true,'created_at'=>now(),'updated_at'=>now()]);
        });
    }

    public function down(): void
    {
        foreach(['vet_mensajes_trabajadores','vet_contratos_trabajadores','vet_trabajador_lugares','vet_trabajadores','vet_categorias_trabajadores'] as $tabla)Schema::dropIfExists($tabla);
    }
};
