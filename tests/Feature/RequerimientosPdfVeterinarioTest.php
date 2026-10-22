<?php

namespace Tests\Feature;

use App\Http\Controllers\EscritorioProfesional;
use App\Mail\CorreoGenerico;
use App\Models\{Bono, ConvenioVeterinario, HoraMedica, Profesional, User, VerificacionCorreo};
use App\Services\{DescuentoConvenioVeterinario, RecuperacionContrasenaService, VerificacionCorreoService};
use App\Support\PrevisionesVeterinarias;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, Blade, DB, Hash, Mail, Password, Schema};
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class RequerimientosPdfVeterinarioTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:', 'mail.default' => 'array']);
        DB::purge('sqlite');
        foreach ([
            'users' => ['name','nombres','apellido_uno','apellido_dos','email','rut','telefono','password','email_verified_at','remember_token'],
            'profesionales' => ['id_usuario','nombre','apellido_uno','apellido_dos','id_especialidad'],
            'mascotas' => ['id_responsable','nombre'],
            'mascotas_lugares_atencion' => ['id_mascota','id_lugar_atencion','id_institucion','origen'],
            'sucursal' => ['id_lugar_atencion','id_institucion'],
            'asistentes_lugar_atencion' => ['id_lugar_atencion','id_institucion'],
            'administrativos_lugar_atencion' => ['id_lugar_atencion','id_institucion'],
            'especialidades_cm' => ['id_lugar_atencion','id_institucion'],
            'asistentes' => ['id_usuario'],
            'pacientes' => ['nombres','apellido_uno','apellido_dos','email'],
            'ciudades' => ['nombre'],
            'direcciones' => ['direccion','numero_dir','id_ciudad'],
            'lugares_atencion' => ['nombre','email','rut','telefono','id_direccion','tipo'],
            'profesionales_lugares_atencion' => ['id_institucion','id_profesional','id_lugar_atencion','estado'],
            'horas_medicas' => ['id_presupuesto','id_ficha_otros_prof','alias_examen','id_procedimiento','descripcion','id_asistente','id_paciente','id_mascota','id_profesional','id_ficha_atencion','id_estado','id_lugar_atencion','fecha_consulta','hora_inicio','hora_termino','tipo_hora_medica','comentarios_cancelacion'],
            'fichas_atenciones' => ['id_paciente','id_mascota','id_profesional','id_lugar_atencion','finalizada'],
            'bonos' => ['pago_confirmado_en','convenio','numero_bono','numero_boleta','bonificacion','aporte_seguro','glosa','rendido','id_asistente','id_paciente','id_clase_bono','numero_sesiones','estado_consulta','id_profesional','id_referencia','id_tipo_bono','fecha_atencion','devuelto','a_pagar','valor_atencion','id_convenio_veterinario','nombre_convenio_veterinario','porcentaje_convenio_veterinario','descuento_convenio_veterinario'],
            'convenios_veterinarios' => ['id_profesional','id_lugar_atencion','nombre','porcentaje','activo'],
            'detalles_receta' => ['id_ficha'],
            'certificados_reposos' => ['id_profesional','id_ficha_atencion'],
            'con_consentimientos_pcte' => ['id_profesional','id_fc'],
            'examen_especialidad' => ['id_profesional','id_ficha_atencion'],
            'verificaciones_correo' => ['id_usuario','email','token','expira_en','verificado_en','enviado_en'],
            'registro_correo' => ['id_user','data','fecha_envio','estado'],
        ] as $tabla => $campos) {
            Schema::create($tabla, function (Blueprint $t) use ($campos) {
                $t->id();
                foreach ($campos as $campo) $t->text($campo)->nullable();
                $t->timestamps();
            });
        }
        Schema::create('password_resets', function (Blueprint $t) {
            $t->string('email')->primary(); $t->string('token'); $t->timestamp('created_at')->nullable();
        });
        config(['auth.passwords.users.table' => 'password_resets']);
        $usuario = new User();
        $usuario->forceFill(['id' => 1, 'name' => 'Veterinario Prueba', 'email' => 'vet@example.test', 'password' => Hash::make('Anterior1')])->save();
        Auth::setUser($usuario);
        DB::table('profesionales')->insert(['id' => 3, 'id_usuario' => 1, 'nombre' => 'Profesional']);
        DB::table('lugares_atencion')->insert(['id' => 4, 'nombre' => 'Consulta', 'email' => 'contacto@example.test', 'telefono' => '+56 9 1234 5678']);
        DB::table('profesionales_lugares_atencion')->insert(['id_profesional' => 3, 'id_lugar_atencion' => 4, 'estado' => 1]);
    }

    public function test_agenda_distingue_mascotas_del_mismo_tutor_y_conserva_duplicados_de_la_misma(): void
    {
        DB::table('horas_medicas')->insert(['id_paciente' => 10, 'id_mascota' => 11, 'id_profesional' => 3, 'fecha_consulta' => '2026-10-26', 'id_estado' => 1]);
        $query = HoraMedica::where('id_paciente', 10)->where('fecha_consulta', '2026-10-26');
        $this->assertTrue((clone $query)->paraMascotaAgenda(11)->exists());
        $this->assertFalse((clone $query)->paraMascotaAgenda(12)->exists());
        $this->assertTrue((clone $query)->paraMascotaAgenda(null)->exists());
    }

    public function test_lugar_nuevo_acepta_correo_y_telefono_ya_usados(): void
    {
        DB::table('ciudades')->insert(['id' => 1, 'nombre' => 'Santiago']);
        $request = Request::create('/', 'POST', [
            'nombre_lugar_atencion' => 'Segunda consulta', 'direccion_lugar_atencion' => 'Calle',
            'numero_lugar_atencion' => '20', 'ciudad_agregar' => 1, 'tipo_agregar_lugar_atencion' => 1,
            'email_lugar_atencion' => 'contacto@example.test', 'telefono_lugar_atencion' => '+56 9 1234 5678',
        ]);
        app(EscritorioProfesional::class)->agregar_lugar_atencion($request);
        $this->assertSame(2, DB::table('lugares_atencion')->where('email', 'contacto@example.test')->count());
        $this->assertSame(2, DB::table('profesionales_lugares_atencion')->where('id_profesional', 3)->count());
    }

    public function test_select_veterinario_excluye_previsiones_humanas(): void
    {
        $opciones = collect(['Fonasa','Isapre Colmena','Particular','Sin convenio'])->map(fn ($n) => (object) ['nombre' => $n]);
        $this->assertSame(['Particular', 'Sin convenio'], PrevisionesVeterinarias::paraSelect($opciones)->pluck('nombre')->all());
    }

    public function test_ficha_general_no_solicita_ni_consulta_prevision(): void
    {
        $fuente = file_get_contents(resource_path('views/atencion_veterinaria/secciones_especialidad/ficha_veterinaria_general.blade.php'));
        $this->assertStringNotContainsString('prevision_paciente_fc', $fuente);
        $this->assertStringNotContainsString('id_prevision', $fuente);
        foreach (['in_solic_pabellon', 'ingreso_hosp'] as $modal) {
            $fuente = file_get_contents(resource_path('views/general/hospitalizacion/modals/'.$modal.'.blade.php'));
            $this->assertStringNotContainsString('$paciente->Prevision', $fuente);
        }
    }

    public function test_descuento_se_calcula_y_conserva_el_nombre_y_porcentaje_del_cobro(): void
    {
        $convenio = ConvenioVeterinario::create(['id_profesional' => 3, 'id_lugar_atencion' => 4, 'nombre' => 'Rescate local', 'porcentaje' => 12.5, 'activo' => true]);
        $hora = new HoraMedica(); $hora->id_profesional = 3; $hora->id_lugar_atencion = 4;
        $bono = new Bono();
        app(DescuentoConvenioVeterinario::class)->aplicar($bono, $hora, $convenio->id, 25000);
        $this->assertSame(21875, $bono->a_pagar);
        $this->assertSame(3125, $bono->descuento_convenio_veterinario);
        $this->assertSame('Rescate local', $bono->nombre_convenio_veterinario);
        $convenio->update(['porcentaje' => 50, 'nombre' => 'Nombre cambiado']);
        $this->assertSame('12.50', $bono->porcentaje_convenio_veterinario);
        app(DescuentoConvenioVeterinario::class)->aplicar($bono, $hora, null, 25000);
        $this->assertSame(25000, $bono->a_pagar);
        $this->assertNull($bono->id_convenio_veterinario);
    }

    public function test_convenio_ajeno_o_de_otro_lugar_no_se_aplica(): void
    {
        $convenio = ConvenioVeterinario::create(['id_profesional' => 99, 'nombre' => 'Ajeno', 'porcentaje' => 100, 'activo' => true]);
        $hora = new HoraMedica(); $hora->id_profesional = 3; $hora->id_lugar_atencion = 4;
        $this->expectException(ValidationException::class);
        app(DescuentoConvenioVeterinario::class)->aplicar(new Bono(), $hora, $convenio->id, 25000);
    }

    public function test_reporte_cuenta_mascotas_fichas_finalizadas_inasistencias_y_documentos_veterinarios(): void
    {
        $this->travelTo(now()->setDate(2026, 10, 19));
        DB::table('fichas_atenciones')->insert([
            ['id' => 1, 'id_mascota' => 11, 'id_paciente' => 10, 'id_profesional' => 3, 'id_lugar_atencion' => 4, 'finalizada' => 1, 'created_at' => '2026-09-10'],
            ['id' => 2, 'id_mascota' => 12, 'id_paciente' => 10, 'id_profesional' => 3, 'id_lugar_atencion' => 4, 'finalizada' => 0, 'created_at' => '2026-09-11'],
            ['id' => 3, 'id_mascota' => 11, 'id_paciente' => 10, 'id_profesional' => 3, 'id_lugar_atencion' => 5, 'finalizada' => 1, 'created_at' => '2026-09-12'],
        ]);
        DB::table('horas_medicas')->insert([
            ['id' => 1, 'id_mascota' => 11, 'id_profesional' => 3, 'id_lugar_atencion' => 4, 'id_ficha_atencion' => 1, 'id_estado' => 6, 'fecha_consulta' => '2026-09-10'],
            ['id' => 2, 'id_mascota' => 12, 'id_profesional' => 3, 'id_lugar_atencion' => 4, 'id_ficha_atencion' => null, 'id_estado' => 7, 'fecha_consulta' => '2026-09-11'],
        ]);
        DB::table('bonos')->insert(['id_profesional' => 3, 'id_referencia' => 1, 'id_tipo_bono' => 1, 'fecha_atencion' => '2026-09-10', 'pago_confirmado_en' => '2026-09-10 10:00:00', 'devuelto' => 0, 'a_pagar' => 21875, 'nombre_convenio_veterinario' => 'Rescate local']);
        DB::table('certificados_reposos')->insert([
            ['id_profesional' => 3, 'id_ficha_atencion' => 1, 'created_at' => '2026-09-10'],
            ['id_profesional' => 3, 'id_ficha_atencion' => 3, 'created_at' => '2026-09-12'],
        ]);
        $datos = app(EscritorioProfesional::class)->reporte_estadisticas(new Request(['mes' => 9, 'lugar' => 4]))->getData();
        $this->assertSame(1, $datos['totalPacientes']);
        $this->assertSame(1, $datos['atencionesRealizadas']);
        $this->assertSame(1, $datos['noAsistieron']);
        $this->assertSame(21875, $datos['ingresoTotal']);
        $this->assertSame(1, $datos['documentos']['Certificados veterinarios']);
        $this->assertArrayNotHasKey('Licencias médicas', $datos['documentos']);
        $this->assertSame('Sep', $datos['atencionesMes']->last()['mes']);
        $this->assertSame('Rescate local', $datos['convenios']->first()->nombre);
        $csv = app(EscritorioProfesional::class)->reporte_estadisticas(new Request(['mes' => 9, 'lugar' => 4, 'formato' => 'csv']));
        ob_start(); $csv->sendContent(); $contenido = ob_get_clean();
        $this->assertStringContainsString('Mascotas atendidas', $contenido);
        $this->assertStringContainsString('21875', $contenido);
        $this->assertStringNotContainsString('Licencias médicas', $contenido);
    }

    public function test_verificacion_llega_al_destinatario_y_el_enlace_funciona_una_vez(): void
    {
        Mail::fake();
        config(['mail.redirect_to' => 'pruebas@example.test', 'mail.from.address' => 'cuentas@example.test', 'mail.from.name' => 'Veterchile']);
        $usuario = User::findOrFail(1);
        $this->assertTrue(app(VerificacionCorreoService::class)->enviar($usuario));
        Mail::assertSent(CorreoGenerico::class, fn ($correo) => $correo->hasTo('vet@example.test') && !$correo->hasTo('pruebas@example.test'));
        $token = VerificacionCorreo::firstOrFail()->token;
        $this->assertNotNull(app(VerificacionCorreoService::class)->confirmar($token));
        $this->assertNotNull($usuario->fresh()->email_verified_at);
        $this->assertNull(app(VerificacionCorreoService::class)->confirmar($token));
        $correo = new CorreoGenerico(['body' => ['nombre' => 'Prueba', 'enlace' => 'https://example.test', 'horas' => 24], 'url_archivo' => '', 'blade' => 'verificacion_cuenta', 'asunto' => 'Confirma']);
        $correo->build();
        $this->assertSame('cuentas@example.test', $correo->from[0]['address']);
    }

    public function test_recuperacion_por_correo_cambia_la_contrasena_de_la_identidad(): void
    {
        Mail::fake();
        config(['mail.redirect_to' => 'pruebas@example.test']);
        $usuario = User::findOrFail(1);
        $this->assertTrue(app(RecuperacionContrasenaService::class)->enviarEnlace($usuario));
        $token = null;
        Mail::assertSent(CorreoGenerico::class, function ($correo) use (&$token) {
            parse_str(parse_url($correo->detalle['body']['enlace'], PHP_URL_QUERY), $query);
            $token = basename(parse_url($correo->detalle['body']['enlace'], PHP_URL_PATH));
            return $correo->hasTo('vet@example.test') && $query['email'] === 'vet@example.test';
        });
        $estado = Password::reset(['email' => $usuario->email, 'token' => $token, 'password' => 'NuevaClave1'], function ($user, $password) {
            $user->password = Hash::make($password); $user->save();
        });
        $this->assertSame(Password::PASSWORD_RESET, $estado);
        $this->assertTrue(Hash::check('NuevaClave1', $usuario->fresh()->password));
        $this->assertFalse(Password::broker()->tokenExists($usuario, $token));
    }
    public function test_registro_publico_crea_cuenta_y_envia_verificacion_antes_de_completar_perfil(): void
    {
        Auth::forgetGuards();
        Mail::fake();
        $cuentas = \Mockery::mock(\App\Services\CuentasService::class);
        $cuentas->shouldReceive('asignarRol')->once()->withArgs(fn ($user, $tipo) => $user->email === 'camila@example.test' && $tipo === 'tutor')->andReturn(new \App\Models\UsuarioRol());
        $this->app->instance(\App\Services\CuentasService::class, $cuentas);
        $this->post(route('registro.cuenta'), [
            'tipo_cuenta' => 'tutor', 'nombres' => 'Camila', 'apellido_uno' => 'Soto',
            'rut' => '20.145.378-K', 'email' => 'Camila@example.test', 'telefono' => '+56 9 6543 2109',
            'password' => 'Veterchile1', 'password_confirmation' => 'Veterchile1',
        ])->assertRedirect(route('registro.enviado'))->assertSessionHas('correo_enviado', true);
        $usuario = User::where('email', 'camila@example.test')->firstOrFail();
        $this->assertNull($usuario->email_verified_at);
        $this->assertTrue(Hash::check('Veterchile1', $usuario->password));
        Mail::assertSent(CorreoGenerico::class, fn ($correo) => $correo->hasTo('camila@example.test'));
    }

    public function test_codigo_celular_no_se_reutiliza_y_se_invalida_tras_cinco_errores(): void
    {
        $usuario = User::findOrFail(1); $usuario->telefono = '+56 9 1234 5678';
        $codigo = null;
        $mensajeria = \Mockery::mock(\App\Services\Mensajeria\MensajeriaService::class);
        $mensajeria->shouldReceive('enviarWhatsapp')->twice()->andReturnUsing(function ($telefono, $texto) use (&$codigo) {
            preg_match('/es (\d{6})/', $texto, $captura); $codigo = $captura[1];
            return ['estado' => 1];
        });
        $servicio = new RecuperacionContrasenaService($mensajeria);
        $this->assertTrue($servicio->enviarCodigo($usuario));
        $this->assertTrue($servicio->codigoCorrecto($usuario, $codigo));
        $this->assertFalse($servicio->codigoCorrecto($usuario, $codigo));
        $this->assertTrue($servicio->enviarCodigo($usuario));
        $errado = $codigo === '000000' ? '111111' : '000000';
        for ($i = 0; $i < 5; $i++) $this->assertFalse($servicio->codigoCorrecto($usuario, $errado));
        $this->assertFalse($servicio->codigoCorrecto($usuario, $codigo));
    }

    public function test_reenvio_fallido_conserva_codigo_vigente_del_celular(): void
    {
        $usuario = User::findOrFail(1); $usuario->telefono = '+56 9 1234 5678';
        $codigo = null;
        $mensajeria = \Mockery::mock(\App\Services\Mensajeria\MensajeriaService::class);
        $mensajeria->shouldReceive('enviarWhatsapp')->once()->andReturnUsing(function ($telefono, $texto) use (&$codigo) {
            preg_match('/es (\d{6})/', $texto, $captura); $codigo = $captura[1];
            return ['estado'=>1];
        });
        $mensajeria->shouldReceive('enviarWhatsapp')->once()->andReturn(['estado'=>0]);
        $servicio = new RecuperacionContrasenaService($mensajeria);
        $this->assertTrue($servicio->enviarCodigo($usuario));
        $this->assertFalse($servicio->enviarCodigo($usuario));
        $this->assertTrue($servicio->codigoCorrecto($usuario, $codigo));
        $this->assertFalse($servicio->codigoCorrecto($usuario, $codigo));
    }

    public function test_migracion_agrega_convenios_y_se_revierte_sin_borrar_bonos(): void
    {
        Schema::drop('convenios_veterinarios');
        Schema::drop('bonos');
        Schema::create('bonos', fn (Blueprint $t) => $t->id());
        DB::table('bonos')->insert(['id' => 1]);
        $migracion = require database_path('migrations/2026_10_19_000001_create_convenios_veterinarios.php');
        $migracion->up();
        $this->assertTrue(Schema::hasColumn('bonos', 'descuento_convenio_veterinario'));
        $this->assertTrue(Schema::hasTable('convenios_veterinarios'));
        $migracion->down();
        $this->assertSame(1, DB::table('bonos')->count());
        $this->assertFalse(Schema::hasTable('convenios_veterinarios'));
    }

    public function test_recepcion_de_pago_persiste_el_descuento_calculado_en_servidor(): void
    {
        $convenio = ConvenioVeterinario::create(['id_profesional' => 3, 'id_lugar_atencion' => 4, 'nombre' => 'Rescate', 'porcentaje' => 20, 'activo' => true]);
        DB::table('horas_medicas')->insert(['id' => 1, 'id_paciente' => 10, 'id_mascota' => 11, 'id_profesional' => 3, 'id_lugar_atencion' => 4, 'id_estado' => 2]);
        $respuesta = app(EscritorioProfesional::class)->recibir_bono(new Request([
            'id_referencia' => 1, 'id_tipo_bono' => 1, 'id_clase_bono' => 1,
            'valor_atencion' => 25000, 'convenio_veterinario_id' => $convenio->id,
        ]));
        $this->assertSame(1, $respuesta['estado']);
        $this->assertTrue($respuesta['pendiente_atencion']);
        $bono = Bono::firstOrFail();
        $this->assertEquals(20000, $bono->a_pagar);
        $this->assertEquals(5000, $bono->descuento_convenio_veterinario);
        $this->assertSame('Rescate', $bono->nombre_convenio_veterinario);
    }

    public function test_convenio_de_otro_lugar_o_desactivado_se_rechaza(): void
    {
        $hora = new HoraMedica(); $hora->id_profesional = 3; $hora->id_lugar_atencion = 4;
        foreach ([['id_lugar_atencion' => 9, 'activo' => true], ['id_lugar_atencion' => 4, 'activo' => false]] as $datos) {
            $convenio = ConvenioVeterinario::create(array_merge(['id_profesional' => 3, 'nombre' => 'Convenio', 'porcentaje' => 10], $datos));
            try {
                app(DescuentoConvenioVeterinario::class)->aplicar(new Bono(), $hora, $convenio->id, 10000);
                $this->fail('Debía rechazar el convenio fuera de su ámbito.');
            } catch (ValidationException $e) {
                $this->assertArrayHasKey('convenio_veterinario_id', $e->errors());
            }
        }
    }

    public function test_agenda_profesional_reserva_dos_mascotas_del_tutor_el_mismo_dia(): void
    {
        // La agenda dental usa el mismo flujo con bloques de 15 minutos.
        DB::table('profesionales')->where('id', 3)->update(['id_especialidad' => 2]);
        DB::table('pacientes')->insert(['id' => 10, 'nombres' => 'Tutora', 'apellido_uno' => 'Prueba']);
        DB::table('mascotas')->insert([
            ['id' => 11, 'id_responsable' => 10, 'nombre' => 'Primera'],
            ['id' => 12, 'id_responsable' => 10, 'nombre' => 'Segunda'],
        ]);
        foreach ([11, 12] as $i => $idMascota) {
            $respuesta = json_decode(app(EscritorioProfesional::class)->agendar_horas(new Request([
                'reserva_hora_id' => 10, 'id_mascota' => $idMascota, 'tipo_hora_medica' => 'D',
                'id_lugar_atencion' => 4, 'fecha_consulta' => '2026-10-26 08:'.($i === 0 ? '00' : '30').':00',
            ])), true);
            $this->assertArrayHasKey('id', $respuesta);
            $this->assertEquals($idMascota, $respuesta['id_mascota']);
        }
        $respuesta = json_decode(app(EscritorioProfesional::class)->agendar_horas(new Request([
            'reserva_hora_id' => 10, 'id_mascota' => 11, 'tipo_hora_medica' => 'D',
            'id_lugar_atencion' => 4, 'fecha_consulta' => '2026-10-26 09:00:00',
        ])), true);
        $this->assertSame('error', $respuesta['estado']);
        $this->assertSame(2, HoraMedica::count());
    }

    public function test_recepcionar_finalizar_y_confirmar_reutiliza_el_cobro_y_conserva_estado_y_descuento(): void
    {
        $convenio = ConvenioVeterinario::create(['id_profesional' => 3, 'nombre' => 'Convenio', 'porcentaje' => 20, 'activo' => true]);
        DB::table('fichas_atenciones')->insert(['id' => 1, 'id_mascota' => 11, 'id_profesional' => 3, 'finalizada' => 0]);
        DB::table('horas_medicas')->insert(['id' => 1, 'id_profesional' => 3, 'id_lugar_atencion' => 4, 'id_mascota' => 11, 'id_paciente' => 10, 'id_ficha_atencion' => 1, 'id_estado' => 5]);
        $controller = app(EscritorioProfesional::class);
        $primera = $controller->recibir_bono(new Request(['id_referencia' => 1, 'valor_atencion' => 25000, 'convenio_veterinario_id' => $convenio->id]));
        $this->assertFalse($primera['pago_confirmado']);
        $this->assertEquals(5, HoraMedica::find(1)->id_estado);
        $idBono = Bono::firstOrFail()->id;
        $ficha = \App\Models\FichaAtencion::find(1); $ficha->finalizada = 1; $ficha->save();
        DB::table('horas_medicas')->where('id', 1)->update(['id_estado' => 6]);
        $this->assertNull(Bono::find($idBono)->pago_confirmado_en);
        $convenio->update(['porcentaje' => 50, 'activo' => false]);
        $confirmado = $controller->recibir_bono(new Request(['id_referencia' => 1, 'valor_atencion' => 25000]));
        $this->assertTrue($confirmado['pago_confirmado']);
        $this->assertEquals(20000, Bono::find($idBono)->a_pagar);
        $this->assertEquals(6, HoraMedica::find(1)->id_estado);
        $fecha = Bono::find($idBono)->pago_confirmado_en;
        $repetido = $controller->recibir_bono(new Request(['id_referencia' => 1, 'valor_atencion' => 99000, 'id_tipo_bono' => 2]));
        $this->assertSame($idBono, $repetido['bono']['id']);
        $this->assertSame(1, Bono::count());
        $this->assertEquals(20000, Bono::find($idBono)->a_pagar);
        $this->assertSame($fecha, Bono::find($idBono)->pago_confirmado_en);
    }

    public function test_ingresos_excluyen_pendientes_y_cuentan_el_ultimo_dia_por_fecha_de_pago(): void
    {
        $this->travelTo(now()->setDate(2026, 10, 19));
        DB::table('horas_medicas')->insert(['id' => 1, 'id_profesional' => 3, 'id_lugar_atencion' => 4, 'id_mascota' => 11]);
        DB::table('bonos')->insert([
            ['id_profesional' => 3, 'id_referencia' => 1, 'devuelto' => 0, 'a_pagar' => 12000, 'fecha_atencion' => '2026-09-30 23:59:59', 'pago_confirmado_en' => null],
            ['id_profesional' => 3, 'id_referencia' => 1, 'devuelto' => 0, 'a_pagar' => 22000, 'fecha_atencion' => '2026-08-01', 'pago_confirmado_en' => '2026-09-30 23:59:59'],
            ['id_profesional' => 3, 'id_referencia' => 1, 'devuelto' => 1, 'a_pagar' => 9000, 'fecha_atencion' => '2026-09-10', 'pago_confirmado_en' => '2026-09-30 12:00:00'],
        ]);
        $datos = app(EscritorioProfesional::class)->reporte_estadisticas(new Request(['mes' => 9, 'lugar' => 4]))->getData();
        $this->assertSame(22000, $datos['ingresoTotal']);
        $this->assertSame(12000, $datos['cobrosPendientes']);
    }

    public function test_qr_no_reescribe_un_pago_ya_confirmado(): void
    {
        DB::table('horas_medicas')->insert(['id' => 1, 'id_profesional' => 3, 'id_mascota' => 11, 'id_estado' => 6]);
        DB::table('bonos')->insert(['id_profesional' => 3, 'id_referencia' => 1, 'id_tipo_bono' => 1, 'devuelto' => 0, 'a_pagar' => 20000, 'numero_bono' => 'ORIGINAL', 'pago_confirmado_en' => '2026-10-19 10:00:00']);
        app(EscritorioProfesional::class)->recibir_qr_bono_pendiente(new Request(['id_hora_medica' => 1, 'codigo_qr' => 'OTRO']));
        $this->assertSame('ORIGINAL', Bono::first()->numero_bono);
        $this->assertEquals(20000, Bono::first()->a_pagar);
        $this->assertEquals(6, HoraMedica::find(1)->id_estado);
    }

    public function test_asistente_autorizado_consulta_convenios_del_profesional_y_lugar_de_la_hora(): void
    {
        $user = new User(); $user->forceFill(['id' => 2, 'email' => 'asistente@example.test'])->save(); Auth::setUser($user);
        DB::table('asistentes')->insert(['id' => 9, 'id_usuario' => 2]);
        DB::table('horas_medicas')->insert(['id' => 1, 'id_profesional' => 3, 'id_lugar_atencion' => 4, 'id_mascota' => 11]);
        foreach ([[3, 4, true, 'Permitido'], [3, 9, true, 'Otro lugar'], [99, 4, true, 'Otro profesional'], [3, 4, false, 'Inactivo']] as [$prof, $lugar, $activo, $nombre]) {
            ConvenioVeterinario::create(['id_profesional' => $prof, 'id_lugar_atencion' => $lugar, 'activo' => $activo, 'nombre' => $nombre, 'porcentaje' => 10]);
        }
        $permisos = \Mockery::mock(\App\Services\AutorizacionesAsistentes::class);
        $permisos->shouldReceive('exigir')->once()->withArgs(fn ($asistente, $lugar, $prof, $permiso) => $asistente->id === 9 && $lugar === 4 && $prof === 3 && $permiso === 'permiso_entrega_caja');
        $this->app->instance(\App\Services\AutorizacionesAsistentes::class, $permisos);
        $json = app(\App\Http\Controllers\ConveniosVeterinariosController::class)->disponibles(new Request(['id_hora_medica' => 1]))->getData(true);
        $this->assertSame(['Permitido'], array_column($json['convenios'], 'nombre'));
    }

    public function test_pago_ajeno_no_modifica_ni_crea_cobros(): void
    {
        DB::table('horas_medicas')->insert(['id' => 1, 'id_profesional' => 99, 'id_mascota' => 11]);
        try {
            app(EscritorioProfesional::class)->recibir_bono(new Request(['id_referencia' => 1, 'valor_atencion' => 25000]));
            $this->fail('Debe rechazar pagos de otro profesional.');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            $this->assertSame(403, $e->getStatusCode());
        }
        $this->assertSame(0, Bono::count());
    }

}
