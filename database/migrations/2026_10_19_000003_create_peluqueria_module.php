<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;
return new class extends Migration {
    public function up(): void {
        Schema::table('usuario_roles',function(Blueprint $t){$t->enum('tipo',['tutor','profesional','asistente','clinica','peluqueria'])->change();});
        Schema::create('peluquerias',function(Blueprint $t){
            $t->id();$t->unsignedBigInteger('id_usuario')->unique();$t->string('nombre');
            $t->string('telefono',30);$t->string('direccion');$t->timestamps();
        });
        Schema::create('peluqueria_clientes',function(Blueprint $t){
            $t->id();$t->unsignedBigInteger('peluqueria_id')->index();$t->string('tutor');
            $t->string('telefono',30);$t->string('email')->nullable();$t->string('mascota',100);
            $t->string('especie',60);$t->string('raza',100)->nullable();$t->text('cuidados')->nullable();$t->timestamps();
        });
        Schema::create('peluqueria_citas',function(Blueprint $t){
            $t->id();$t->unsignedBigInteger('peluqueria_id');$t->unsignedBigInteger('cliente_id')->index();
            $t->dateTime('inicio');$t->dateTime('fin');$t->string('servicio',150);
            $t->string('estado',20)->default('reservada');$t->decimal('valor',12,0)->default(0);
            $t->text('observaciones')->nullable();$t->text('registro')->nullable();$t->timestamp('finalizada_en')->nullable();
            $t->timestamps();$t->index(['peluqueria_id','inicio','fin']);
        });
        Role::findOrCreate('Peluqueria','web');
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }
    public function down(): void {
        Schema::dropIfExists('peluqueria_citas');Schema::dropIfExists('peluqueria_clientes');Schema::dropIfExists('peluquerias');
    }
};
