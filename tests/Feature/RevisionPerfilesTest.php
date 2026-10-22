<?php
namespace Tests\Feature;

use App\Http\Controllers\{ComercioSsoController, EscritorioPaciente};
use App\Http\Controllers\Api\VeterfarmaPetController;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth,DB,Schema};
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\{CuentasAisladas,TestCase};

class RevisionPerfilesTest extends TestCase
{
    use CuentasAisladas;

    protected function setUp(): void
    {
        parent::setUp(); $this->prepararCuentasAisladas();
        Schema::table('profesionales',fn(Blueprint $t)=>$t->text('foto_perfil')->nullable());
        Schema::create('fichas_atenciones',function(Blueprint $t) { $t->id(); $t->integer('id_paciente'); $t->integer('id_mascota')->nullable(); $t->integer('id_profesional'); $t->boolean('desvincular')->default(false); $t->timestamps(); });
        DB::table('users')->insert(['id'=>1,'name'=>'Tutor','email'=>'tutor@example.test']);
        DB::table('pacientes')->insert(['id'=>9,'id_usuario'=>1]);
        Auth::setUser(User::find(1));
    }

    public function test_mis_profesionales_omite_referencias_huerfanas_y_tolera_profesion_no_configurada(): void
    {
        DB::table('profesionales')->insert(['id'=>10,'nombre'=>'Profesional sin especialidad','rut'=>'11111111-1']);
        DB::table('fichas_atenciones')->insert([['id_paciente'=>9,'id_profesional'=>999],['id_paciente'=>9,'id_profesional'=>10]]);
        $vista=app(EscritorioPaciente::class)->miProfesionales();
        $this->assertSame([10],collect($vista->getData()['profesional'])->pluck('id')->all());
        $this->assertSame([],$vista->getData()['lista_especialidad']);
    }

    public function test_un_tutor_no_puede_desvincular_profesionales_de_otro_tutor(): void
    {
        $this->expectException(HttpException::class);
        app(EscritorioPaciente::class)->miProfesionales(2,10);
    }

    public function test_profesionales_de_la_mascota_toleran_referencias_huerfanas(): void
    {
        DB::table('mascotas')->insert(['id'=>1,'nombre'=>'Mascota','id_responsable'=>9]);
        DB::table('profesionales')->insert(['id'=>10,'nombre'=>'Veterinario']);
        DB::table('fichas_atenciones')->insert([['id_paciente'=>9,'id_mascota'=>1,'id_profesional'=>999],['id_paciente'=>9,'id_mascota'=>1,'id_profesional'=>10]]);
        $vista=app(\App\Http\Controllers\EscritorioPacienteDependiente::class)->miProfesionales(1);
        $this->assertSame([10],collect($vista->getData()['profesional'])->pluck('id')->all());
        $this->assertSame(['Veterinaria'],$vista->getData()['lista_especialidad']);
    }

    public function test_desvincular_exige_post_y_proteccion_csrf(): void
    {
        foreach (['paciente.desvincular_profesional','paciente.dependiente.desvincular_profesional'] as $nombre) {
            $ruta=app('router')->getRoutes()->getByName($nombre);
            $this->assertSame(['POST'],$ruta->methods());
            $this->assertContains('web',$ruta->gatherMiddleware());
        }
    }

    public function test_veterfarma_rechaza_una_clave_de_integracion_sin_configurar(): void
    {
        config(['services.sdi_sso.key'=>null]);
        $respuesta=app(VeterfarmaPetController::class)->store(Request::create('/api/mascotas','POST'));
        $this->assertSame(403,$respuesta->status());
    }

    public function test_acceso_a_la_tienda_no_firma_con_una_clave_vacia(): void
    {
        config(['services.sdi_sso.key'=>null]);
        $this->expectException(HttpException::class);
        app(ComercioSsoController::class)->farmacia();
    }
}
