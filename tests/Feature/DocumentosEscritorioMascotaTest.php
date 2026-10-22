<?php

namespace Tests\Feature;

use App\Http\Controllers\EscritorioPacienteDependiente;
use App\Http\Middleware\AutorizarFichaVeterinaria;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, DB, Schema};
use Tests\TestCase;

class DocumentosEscritorioMascotaTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default'=>'sqlite', 'database.connections.sqlite.database'=>':memory:']); DB::purge('sqlite');
        foreach ([
            'pacientes'=>['id_usuario'], 'mascotas'=>['id_responsable','nombre','chip','estado','fallecida'], 'profesionales'=>['nombre','id_usuario','apellido_uno','apellido_dos','sexo','rut','email','certificado','estado','provisorio'],
            'fichas_atenciones'=>['id_mascota','id_paciente','id_profesional','id_lugar_atencion','motivo','hipotesis_diagnostico','indicaciones','antecedentes','der_por','confidencial','profesional_visible','finalizada'],
            'recomendacion'=>['atencion'], 'detalles_receta'=>['id_ficha'], 'examenes_ppf'=>['id_ficha_atencion','examen'],
            'horas_medicas'=>['id_paciente','id_mascota','fecha_consulta','hora_inicio','id_ficha_atencion'],
        ] as $tabla=>$campos) Schema::create($tabla, function (Blueprint $t) use ($campos) { $t->id(); foreach ($campos as $c) $t->text($c)->nullable(); $t->timestamps(); });
        DB::table('pacientes')->insert(['id'=>2,'id_usuario'=>7]);
        DB::table('mascotas')->insert([['id'=>4,'id_responsable'=>2], ['id'=>5,'id_responsable'=>2], ['id'=>6,'id_responsable'=>9]]);
        DB::table('profesionales')->insert(['id'=>3,'nombre'=>'Veterinario']);
        foreach ([10=>4,20=>5,30=>6] as $id=>$mascota) {
            DB::table('fichas_atenciones')->insert(['id'=>$id,'id_mascota'=>$mascota,'id_paciente'=>$mascota===6?9:2,'id_profesional'=>3]);
            DB::table('recomendacion')->insert(['atencion'=>$id]);
            DB::table('examenes_ppf')->insert(['id_ficha_atencion'=>$id,'examen'=>'Hemograma']);
        }
        $usuario = \Mockery::mock(User::class)->makePartial(); $usuario->id=7;
        $usuario->shouldReceive('hasRole')->with('Profesional')->andReturnFalse();
        Auth::setUser($usuario);
    }

    public function test_escritorio_recetas_y_examenes_separan_las_mascotas_del_mismo_tutor(): void
    {
        $controller = app(EscritorioPacienteDependiente::class);
        foreach ([4=>10,5=>20] as $mascota=>$ficha) {
            $request = Request::create('/', 'GET', ['id_mascota'=>$mascota]);
            $this->assertSame([$ficha], $controller->index($request)->getData()['documentos_emitidos']->pluck('id')->all());
            $recetas = $controller->receta_misrecetas($request)->getData()['fichas'];
            $this->assertSame([$ficha], $recetas->pluck('id')->all());
            $this->assertCount(1, $recetas->first()->RecetasEmitidas);
            $examenes = $controller->receta_misexamenes($request)->getData()['fichas'];
            $this->assertSame([$ficha], $examenes->pluck('id')->all());
            $this->assertSame('Hemograma', $examenes->first()->OrdenesExamenes->first()->examen);
        }
    }

    public function test_formulario_externo_carga_mascotas_y_preselecciona_solo_una_propia(): void
    {
        $controller = app(\App\Http\Controllers\EscritorioPaciente::class);
        $datos = $controller->acceso_pni(Request::create('/', 'GET', ['id_mascota'=>4]))->getData();
        $this->assertEquals(4, $datos['mascotaSeleccionada']);
        $this->assertSame([4,5], $datos['mascotas']->pluck('id')->all());
        $html = view('app.paciente.acceso_profesional_no_inscrito', $datos)->render();
        $this->assertLessThan(strpos($html, 'id="rut_profesional"'), strpos($html, 'id="atencion_id_mascota"'));
        $this->assertMatchesRegularExpression('/value="4"\s+selected/', $html);
        $this->assertStringNotContainsString('buscar_profesional(', $html);
        $this->assertNull($controller->acceso_pni(new Request())->getData()['mascotaSeleccionada']);
        try {
            $controller->acceso_pni(new Request(['id_mascota'=>6])); $this->fail('No debe seleccionar mascotas de otro tutor.');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) { $this->assertSame(403, $e->getStatusCode()); }
    }

    public function test_atencion_externa_se_guarda_con_rut_y_correo_sin_consultar_personas(): void
    {
        \Illuminate\Support\Facades\Http::preventStrayRequests();
        $respuesta = app(\App\Http\Controllers\EscritorioPaciente::class)->guardarAtencionProfesionalNoInscrito(new Request([
            'rut_profesional'=>'20.145.378-K', 'email_profesional'=>'externo@example.test', 'id_mascota'=>4,
            'fecha_atencion'=>now()->format('Y-m-d'), 'procedimiento'=>'Atención externa de prueba',
        ]));
        $this->assertSame(1, $respuesta->getData(true)['estado']);
        $ficha = \App\Models\FichaAtencion::findOrFail($respuesta->getData(true)['ficha_id']);
        $this->assertEquals(4,$ficha->id_mascota);
        $this->assertEquals(1,$ficha->finalizada);
        $this->assertSame('externo@example.test',$ficha->Profesional->email);
        $this->assertEquals(1,$ficha->Profesional->provisorio);
        \Illuminate\Support\Facades\Http::assertNothingSent();
    }

    public function test_tutor_puede_leer_su_documento_pero_no_el_de_otro_tutor(): void
    {
        $middleware = app(AutorizarFichaVeterinaria::class);
        $this->assertSame('permitido', $middleware->handle(Request::create('/', 'GET', ['id_ficha_atencion'=>10]), fn ()=>'permitido'));
        try {
            $middleware->handle(Request::create('/', 'GET', ['id_ficha_atencion'=>30]), fn ()=>'no permitido');
            $this->fail('Debe rechazar el documento ajeno.');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $error) { $this->assertSame(403,$error->getStatusCode()); }
    }

    public function test_emitir_persiste_receta_y_orden_antes_de_notificar_al_tutor(): void
    {
        Schema::table('recomendacion', function (Blueprint $t) {
            foreach (['salida','herir','cuadro','activo','aficionado','control','cod_doc','cod_auto','info'] as $c) $t->text($c)->nullable();
        });
        Schema::table('examenes_ppf', function (Blueprint $t) {
            foreach (['id_prioridad','id_paciente','id_profesional','id_ficha_otros_prof','id_ficha_gineco_obstetrica','id_examen','examen_especialidad','tipo_examen','tipo_ficha','con_contraste','codigo_fonasa','archivo','sospecha','observacion','prioridad','otro'] as $c) $t->text($c)->nullable();
        });
        foreach ([
            'recomendacion_detalle'=>['id_recomendacion','control','id_articulo','articulo','componente','id_apariencia','apariencia','id_cuota','cuota','id_regimen','regimen','id_lapso','lapso','uso_frecuente','volumen_compra','volumen','volumen_entregado','comentario','cod_doc'],
            'profesional_firma'=>['id_profesional','id_autorizacion','id_log','id_tipo','id_documento'],
            'articulos'=>['nombre','tipo_cont'], 'examenes_medicos'=>['nombre_examen','codigo'],
        ] as $tabla=>$campos) Schema::create($tabla, function (Blueprint $t) use ($campos) { $t->id(); foreach ($campos as $c) $t->text($c)->nullable(); $t->timestamps(); });
        DB::table('articulos')->insert(['id'=>1,'nombre'=>'Medicamento de prueba','tipo_cont'=>0]);
        $correo = \Mockery::mock(\App\Services\EnviarDocumentoMascota::class);
        $correo->shouldReceive('enviar')->once()->withArgs(fn ($f, $tipo) => $f->id === 10 && $tipo === 'receta' && $f->RecetasEmitidas()->count() === 1)
            ->andReturn(['correo_enviado'=>true]);
        $correo->shouldReceive('enviar')->once()->withArgs(fn ($f, $tipo) => $f->id === 10 && $tipo === 'examenes' && $f->OrdenesExamenes()->count() === 1)
            ->andReturn(['correo_enviado'=>true]);
        $this->app->instance(\App\Services\EnviarDocumentoMascota::class, $correo);
        $medicamento = ['id_tipo_control'=>0,'id_producto'=>1,'producto'=>'Medicamento de prueba','componente'=>'', 'id_dosis'=>0,'dosis'=>'Presentación de prueba', 'id_posologia'=>0,'posologia'=>'Indicación de prueba', 'id_via_administracion'=>0,'via_administracion'=>'Vía de prueba', 'id_periodo'=>0,'periodo'=>'Duración de prueba','uso_cronico'=>0,'cantidad'=>1,'cantidad_comprar'=>'1 unidad'];
        $receta = app(\App\Http\Controllers\RecomendacionController::class)->registroRecomendacion(new Request(['id_ficha'=>10,'id_paciente'=>2,'id_profesional'=>3,'id_lugar_atencion'=>8,'medicamentos'=>json_encode([$medicamento])]));
        $this->assertTrue($receta['correo_enviado']);
        $this->assertSame(1, DB::table('recomendacion_detalle')->count());
        $examen = app(\App\Http\Controllers\ExamenesPPFController::class)->registroExamenes_r(new Request(['id_ficha_atencion'=>10,'id_paciente'=>2,'id_profesional'=>3,'examenes'=>json_encode([['id_examen'=>1,'nombre_examen'=>'Hemograma','nombre_examen_especialidad'=>'Laboratorio','tipo'=>'General','prioridad'=>'Media']])]));
        $this->assertTrue($examen['correo_enviado']);
        $this->assertSame(0, (int) $examen['falla']);
        $escritorio = app(EscritorioPacienteDependiente::class)->index(Request::create('/', 'GET', ['id_mascota'=>4]))->getData();
        $this->assertSame([10], $escritorio['documentos_emitidos']->pluck('id')->all());
    }
}
