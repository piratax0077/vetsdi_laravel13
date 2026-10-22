<?php

namespace Tests\Feature;

use App\Models\HoraMedica;
use App\Models\FichaAtencion;
use App\Models\FichaVeterinariaGeneral;
use App\Services\GuardarFichaVeterinaria;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class GuardarFichaVeterinariaTest extends TestCase
{
    public function test_finalizar_conserva_la_ficha_y_la_incluye_en_historial_sin_duplicarla(): void
    {
        $servicio = app(GuardarFichaVeterinaria::class);
        $ficha = $servicio->guardar(new Request(['hora_medica'=>1, 'descripcion_hipotesis'=>'Control', 'motivo'=>'Revisión', 'cerrarsession'=>2]), 3, 'general');
        $this->assertEquals(0, $ficha->finalizada);
        $this->assertEquals(5, HoraMedica::find(1)->id_estado);
        $final = $servicio->guardar(new Request(['hora_medica'=>1, 'descripcion_hipotesis'=>'Diagnóstico definitivo', 'indicaciones'=>'Control en siete días', 'cerrarsession'=>1]), 3, 'general');
        $this->assertEquals($ficha->id, $final->id);
        $this->assertSame('Revisión', $final->motivo);
        $this->assertEquals(6, HoraMedica::find(1)->id_estado);
        $this->assertNotEmpty(HoraMedica::find(1)->fecha_realizacion_consulta);
        $this->assertEquals($final->id, FichaAtencion::where('id_mascota',4)->where('id_profesional',3)->where('finalizada',1)->sole()->id);
        $this->assertSame('Control en siete días', $this->datos()['indicaciones']);
    }

    public function test_uploaded_image_is_persisted_and_published_for_each_specialty(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');
        $original = public_path();
        $temporal = storage_path('framework/testing/clinica-'.uniqid());
        $this->app->usePublicPath($temporal);
        try {
            $disk = \Illuminate\Support\Facades\Storage::disk('public');
            $contenido = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+aX1sAAAAASUVORK5CYII=');
            $disk->put('imagenes/temp/prueba.png', $contenido);
            foreach (['general','cirugia','odontologia'] as $especialidad) {
                app(GuardarFichaVeterinaria::class)->guardar(new Request(['hora_medica'=>1,'descripcion_hipotesis'=>'Control','input_lista_imagenes'=>json_encode([['','foto.png','prueba.png','php']])]),3,$especialidad);
                $imagen = array_slice($this->datos()['imagenes_atencion'],-1)[0];
                $this->assertSame('png',$imagen['extension']);
                $this->assertSame($contenido,file_get_contents(public_path(ltrim($imagen['url'],'/'))));
                $disk->assertExists('imagenes/temp/prueba.png');
            }
            $this->assertCount(3,$this->datos()['imagenes_atencion']);
        } finally {
            $this->app->usePublicPath($original);
            \Illuminate\Support\Facades\File::deleteDirectory($temporal);
        }
    }

    public function test_missing_attachment_prevents_any_clinical_write(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');
        try {
            app(GuardarFichaVeterinaria::class)->guardar(new Request(['hora_medica'=>1,'descripcion_hipotesis'=>'Control','input_lista_imagenes'=>'[["","foto.png","ausente.png","png"]]']),3,'general');
            $this->fail('Debe rechazar el archivo ausente.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('input_lista_imagenes',$e->errors());
        }
        $this->assertSame(0,FichaAtencion::count());
        $this->assertEquals(5,HoraMedica::find(1)->id_estado);
    }

    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        foreach ([
            'horas_medicas' => ['id_paciente', 'id_profesional', 'id_mascota', 'id_ficha_atencion', 'id_estado', 'id_lugar_atencion', 'fecha_realizacion_consulta'],
            'fichas_atenciones' => ['id_paciente', 'id_profesional', 'id_mascota', 'id_lugar_atencion', 'motivo', 'antecedentes', 'examen_fisico', 'hipotesis_diagnostico', 'diagnostico_ce10', 'indicaciones', 'peso', 'temperatura', 'pulso', 'cronico', 'confidencial', 'ges', 'finalizada'],
            'ficha_veterinaria_general' => ['id_fichas_atenciones', 'id_paciente', 'id_profesional', 'datos', 'estado'],
            'control_post_quirurgico' => ['id_ficha_atencion', 'id_profesional', 'id_paciente', 'eg_cpq_cg', 'hoc_cpa_cg', 'masas_cpq_cg', 'obs_egp_cpq_cg', 'estado'],
            'ficha_cir_general_adulto' => ['id_ficha_atencion','id_profesional','id_paciente','est_grl','ex_seg','cir_grl_urgencia','obs_est_grl','rec_tto_cg','tipo_proc_cg','plan_proc_cg','otro','otro1','tto_med_cg','pr_cg','plan_cirugia','estado'],
            'bonos' => ['id_referencia', 'id_tipo_bono', 'estado_consulta', 'devuelto', 'glosa'],
            'profesionales' => ['id_usuario'],
            'especies_mascotas' => ['nombre'],
            'mascotas' => ['id_responsable','especie_id','especie','otra_especie'],
            'presupuestos_dental' => ['id_ficha_atencion'],
            'pagos_presupuesto_dental' => ['id_presupuesto'],
        ] as $tabla => $campos) {
            Schema::create($tabla, function (Blueprint $table) use ($campos) {
                $table->id();
                foreach ($campos as $campo) $table->text($campo)->nullable();
                $table->timestamps();
            });
        }
        $hora = new HoraMedica();
        $hora->forceFill(['id' => 1, 'id_paciente' => 2, 'id_profesional' => 3, 'id_mascota' => 4, 'id_lugar_atencion' => 5, 'id_estado' => 5])->save();
        DB::table('profesionales')->insert(['id' => 3, 'id_usuario' => 7]);
        DB::table('especies_mascotas')->insert(['id'=>55,'nombre'=>'Felina']);
        DB::table('mascotas')->insert(['id'=>4,'id_responsable'=>2,'especie_id'=>55]);
        (require database_path('migrations/2026_08_02_180000_create_veterinary_dental_records.php'))->up();
        (require database_path('migrations/2026_10_13_200000_complete_veterinary_dental_workflow.php'))->up();
    }

    public function test_the_three_specialties_save_with_only_diagnosis(): void
    {
        foreach (['general', 'cirugia', 'odontologia'] as $especialidad) {
            $ficha = app(GuardarFichaVeterinaria::class)->guardar(Request::create('/', 'POST', [
                'hora_medica' => 1, 'descripcion_hipotesis' => 'Control veterinario',
            ]), 3, $especialidad);
            $this->assertSame('Control veterinario', $ficha->fresh()->hipotesis_diagnostico);
            $this->assertEquals(4, $ficha->fresh()->id_mascota);
            $this->assertEquals(6, HoraMedica::find(1)->id_estado);
            $this->assertSame($especialidad, $this->datos()['especialidad_veterinaria']);
        }
        $this->assertSame(1, FichaAtencion::count());
        $this->assertSame(1, FichaVeterinariaGeneral::count());
    }

    public function test_opening_a_consultation_reuses_its_draft_and_never_another_pets_record(): void
    {
        DB::table('fichas_atenciones')->insert(['id'=>90,'id_paciente'=>2,'id_profesional'=>3,'id_mascota'=>99,'id_lugar_atencion'=>5]);
        $servicio=app(\App\Services\AbrirFichaVeterinaria::class);
        $primera=$servicio->abrir(1,3);
        $segunda=$servicio->abrir(1,3);
        $this->assertSame($primera->id,$segunda->id);
        $this->assertNotEquals(90,$primera->id);
        $this->assertEquals(4,$primera->id_mascota);
        $this->assertEquals($primera->id,HoraMedica::find(1)->id_ficha_atencion);
        $this->assertSame(2,FichaAtencion::count());
    }

    public function test_opening_rejects_a_wrongly_linked_record_without_modifying_it(): void
    {
        DB::table('fichas_atenciones')->insert(['id'=>90,'id_paciente'=>2,'id_profesional'=>3,'id_mascota'=>99,'id_lugar_atencion'=>5]);
        DB::table('horas_medicas')->where('id',1)->update(['id_ficha_atencion'=>90]);
        try {app(\App\Services\AbrirFichaVeterinaria::class)->abrir(1,3);$this->fail();}
        catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {$this->assertSame(403,$e->getStatusCode());}
        $this->assertEquals(99,FichaAtencion::find(90)->id_mascota);
    }

    public function test_a_previous_tutors_appointment_cannot_save_clinical_changes(): void
    {
        DB::table('mascotas')->where('id',4)->update(['id_responsable'=>88]);
        try {app(GuardarFichaVeterinaria::class)->guardar(new Request(['hora_medica'=>1,'descripcion_hipotesis'=>'No permitido']),3,'general');$this->fail();}
        catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {$this->assertSame(403,$e->getStatusCode());}
        $this->assertSame(0,FichaAtencion::count());
        $this->assertSame(0,FichaVeterinariaGeneral::count());
    }

    public function test_partial_edit_preserves_chronic_and_confidential_flags(): void
    {
        $service=app(GuardarFichaVeterinaria::class);
        $ficha=$service->guardar(new Request(['hora_medica'=>1,'descripcion_hipotesis'=>'Control','enf_cronico'=>1,'confidencial'=>1]),3,'general');
        $service->guardar(new Request(['hora_medica'=>1,'descripcion_hipotesis'=>'Editado']),3,'general');
        $this->assertEquals(1,$ficha->fresh()->cronico);$this->assertEquals(1,$ficha->fresh()->confidencial);
        $service->guardar(new Request(['hora_medica'=>1,'descripcion_hipotesis'=>'Editado','enf_cronico'=>0,'confidencial'=>0]),3,'general');
        $this->assertEquals(0,$ficha->fresh()->cronico);$this->assertEquals(0,$ficha->fresh()->confidencial);
    }

    public function test_all_supplied_data_survives_save_and_edit(): void
    {
        foreach (['general', 'cirugia', 'odontologia'] as $especialidad) {
            $campos = [
                'hora_medica' => 1, 'descripcion_hipotesis' => 'Gingivitis', 'motivo' => 'Control',
                'temperatura' => '38.5', 'peso' => '4.2', 'obs_clinicas' => 'Seguimiento',
                'sistema_digestivo' => 'Normal', 'eg_cpq_cg' => 'Buen estado',
                'dieta' => 'Húmeda', 'vacunas' => 'Al día', 'vive_con_animales' => '0',
                'examen_oral' => ['encías' => 'Inflamadas'],
                'medicamentos' => '[{"medicamento":"Tratamiento de prueba"}]',
                'examenes' => '[{"nombre_examen":"Hemograma"}]',
            ];
            $ficha = app(GuardarFichaVeterinaria::class)->guardar(Request::create('/', 'POST', $campos), 3, $especialidad);
            foreach ($campos as $campo => $valor) $this->assertSame($valor, $this->datos()[$campo]);
            $this->assertSame('38.5', $ficha->fresh()->temperatura);
            $this->assertSame('4.2', $ficha->fresh()->peso);
            app(GuardarFichaVeterinaria::class)->guardar(Request::create('/', 'POST', [
                'hora_medica' => 1, 'descripcion_hipotesis' => 'Diagnóstico editado',
            ]), 3, $especialidad);
            $this->assertSame($campos['examen_oral'], $this->datos()['examen_oral']);
            $this->assertSame($campos['medicamentos'], $this->datos()['medicamentos']);
        }
        $this->assertSame(1, DB::table('control_post_quirurgico')->count());
    }

    public function test_partial_surgery_save_preserves_plan_and_explicit_uncheck_clears_it(): void
    {
        $servicio=app(GuardarFichaVeterinaria::class);
        $servicio->guardar(new Request([
            'hora_medica'=>1,'descripcion_hipotesis'=>'Control quirúrgico',
            'est_grl'=>'Estado registrado','tto_med_cg'=>1,'pr_cg'=>1,'plan_cirugia'=>1,
        ]),3,'cirugia');
        $servicio->guardar(new Request([
            'hora_medica'=>1,'descripcion_hipotesis'=>'Control quirúrgico',
            'obs_est_grl'=>'Nueva observación',
        ]),3,'cirugia');
        $registro=DB::table('ficha_cir_general_adulto')->sole();
        foreach(['tto_med_cg','pr_cg','plan_cirugia'] as $campo) $this->assertEquals(1,$registro->{$campo});
        $this->assertSame('Estado registrado',$registro->est_grl);
        $servicio->guardar(new Request([
            'hora_medica'=>1,'descripcion_hipotesis'=>'Control quirúrgico','plan_cirugia'=>0,
        ]),3,'cirugia');
        $registro=DB::table('ficha_cir_general_adulto')->sole();
        $this->assertEquals(0,$registro->plan_cirugia);
        $this->assertEquals(1,$registro->tto_med_cg);
        $this->assertEquals(1,$this->datos()['tto_med_cg']);
        $this->assertEquals(0,$this->datos()['plan_cirugia']);
    }

    public function test_missing_or_whitespace_diagnosis_does_not_save(): void
    {
        foreach (['general', 'cirugia', 'odontologia'] as $especialidad) {
            try {
                app(GuardarFichaVeterinaria::class)->guardar(Request::create('/', 'POST', [
                    'hora_medica' => 1, 'descripcion_hipotesis' => '   ', 'motivo' => 'Motivo suficiente antes',
                ]), 3, $especialidad);
                $this->fail('Debe exigir diagnóstico.');
            } catch (ValidationException $error) {
                $this->assertArrayHasKey('descripcion_hipotesis', $error->errors());
            }
        }
        $this->assertSame(0, FichaAtencion::count());
        $this->assertEquals(5, HoraMedica::find(1)->id_estado);
    }

    public function test_failure_in_the_detail_rolls_back_the_entire_save(): void
    {
        DB::statement("CREATE TRIGGER reject_detail BEFORE INSERT ON ficha_veterinaria_general BEGIN SELECT RAISE(ABORT, 'test failure'); END");
        try {
            app(GuardarFichaVeterinaria::class)->guardar(Request::create('/', 'POST', [
                'hora_medica' => 1, 'descripcion_hipotesis' => 'Diagnóstico válido',
            ]), 3, 'general');
            $this->fail('Debe fallar la operación completa.');
        } catch (\Illuminate\Database\QueryException $error) {
            $this->assertStringContainsString('test failure', $error->getMessage());
        }
        $this->assertSame(0, FichaAtencion::count());
        $this->assertEquals(5, HoraMedica::find(1)->id_estado);
        $this->assertNull(HoraMedica::find(1)->id_ficha_atencion);
    }

    public function test_cannot_save_another_professionals_appointment(): void
    {
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        app(GuardarFichaVeterinaria::class)->guardar(Request::create('/', 'POST', [
            'hora_medica' => 1, 'descripcion_hipotesis' => 'Control',
        ]), 99, 'general');
    }

    public function test_physical_exam_values_are_not_lost_to_empty_duplicate_vital_signs(): void
    {
        $ficha = app(GuardarFichaVeterinaria::class)->guardar(Request::create('/', 'POST', [
            'hora_medica' => 1, 'descripcion_hipotesis' => 'Control',
            'temperatura' => '', 'temperatura_examen_fisico' => '38.4',
            'pulso' => '', 'pulso_examen_fisico' => 'Regular',
        ]), 3, 'general');
        $this->assertSame('38.4', $ficha->fresh()->temperatura);
        $this->assertSame('Regular', $ficha->fresh()->pulso);
        $this->assertSame('38.4', $this->datos()['temperatura_examen_fisico']);
    }

    public function test_existing_images_and_odontology_values_are_preserved_on_edit(): void
    {
        app(GuardarFichaVeterinaria::class)->guardar(Request::create('/', 'POST', [
            'hora_medica' => 1, 'descripcion_hipotesis' => 'Control', 'cerrarsession' => '2',
        ]), 3, 'odontologia');
        $detalle = FichaVeterinariaGeneral::first();
        $datos = $this->datos();
        $datos['imagenes_atencion'] = [['url' => 'imagen_existente.png']];
        $detalle->datos = json_encode($datos);
        $detalle->save();
        app(GuardarFichaVeterinaria::class)->guardar(Request::create('/', 'POST', [
            'hora_medica' => 1, 'descripcion_hipotesis' => 'Control editado', 'cerrarsession' => '2',
            'examen_oral' => 'Encías sanas',
        ]), 3, 'odontologia');
        $this->assertSame($datos['imagenes_atencion'], $this->datos()['imagenes_atencion']);
        $this->assertSame('Encías sanas', $this->datos()['examen_oral']);
        $this->assertEquals(5, HoraMedica::find(1)->id_estado);
    }

    private function datos(): array
    {
        return json_decode(FichaVeterinariaGeneral::first()->datos, true);
    }

    public function test_all_three_existing_controller_entry_points_use_the_veterinary_save(): void
    {
        foreach ([
            [\App\Http\Controllers\ficha_atencionController::class, 'store_vet_general', 'general'],
            [\App\Http\Controllers\ficha_atencionController::class, 'store_cg', 'cirugia'],
            [\App\Http\Controllers\DentalController::class, 'registrar_ficha_atencion_dental', 'odontologia'],
        ] as [$controller, $method, $especialidad]) {
            $request = Request::create('/', 'POST', [
                'hora_medica' => 1, 'descripcion_hipotesis' => 'Control',
            ], [], [], ['HTTP_ACCEPT' => 'application/json']);
            $request->setUserResolver(fn () => (object) ['id' => 7]);
            $response = app($controller)->{$method}($request);
            $this->assertSame(200, $response->getStatusCode());
            $this->assertSame('ok', $response->getData()->estado);
            $this->assertSame($especialidad, $this->datos()['especialidad_veterinaria']);
        }
    }
}
