<?php

namespace Tests\Feature;

use App\Http\Controllers\OdontologiaVeterinariaController;
use App\Models\{FichaAtencion, Mascota, PresupuestosDental, Profesional, User};
use App\Services\OdontologiaVeterinaria;
use App\Support\DenticionVeterinaria;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, DB, Schema};
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class OdontologiaVeterinariaTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        foreach ([
            'especies_mascotas' => ['nombre'],
            'mascotas' => ['id_responsable','especie_id','especie','otra_especie','nombre'],
            'profesionales' => ['id_usuario','nombre','apellido_uno','apellido_dos'],
            'fichas_atenciones' => ['id_mascota','id_paciente','id_profesional','id_lugar_atencion','finalizada','hipotesis_diagnostico','diagnostico_ce10','cronico','confidencial','ges'],
            'horas_medicas' => ['id_ficha_atencion','id_mascota','id_paciente','id_profesional','id_lugar_atencion','id_estado'],
            'ficha_veterinaria_general' => ['id_fichas_atenciones','id_paciente','id_profesional','datos','estado'],
            'bonos' => ['id_referencia','id_tipo_bono','estado_consulta','devuelto','glosa'],
            'presupuestos_dental' => ['id_paciente','id_profesional','id_ficha_atencion','id_lugar_atencion','datos_piezas_dentales','estado','aprobado','fecha_control','fecha','boca','observaciones','valor_total','valor_abonado'],
            'pagos_presupuesto_dental' => ['id_paciente','id_profesional','id_lugar_atencion','id_ficha_atencion','id_presupuesto','id_metodo_pago','metodo_pago','fecha_pago','total','estado','observaciones'],
            'tarifas_uco_odontologia_veterinaria' => ['id_profesional','id_lugar_atencion','codigo','categoria','nombre','uco','valor_uco','activo','es_base'],
        ] as $tabla => $campos) {
            Schema::create($tabla, function (Blueprint $table) use ($campos) {
                $table->id(); foreach ($campos as $campo) $table->text($campo)->nullable(); $table->timestamps();
            });
        }
        (require database_path('migrations/2026_08_02_180000_create_veterinary_dental_records.php'))->up();
        (require database_path('migrations/2026_10_13_200000_complete_veterinary_dental_workflow.php'))->up();
        DB::table('especies_mascotas')->insert([['id'=>55,'nombre'=>'Felina'],['id'=>2,'nombre'=>'Canina']]);
        DB::table('mascotas')->insert([['id'=>1,'id_responsable'=>9,'especie_id'=>55,'nombre'=>'Felina de prueba'],['id'=>2,'id_responsable'=>9,'especie_id'=>2,'nombre'=>'Canino de prueba']]);
        DB::table('profesionales')->insert(['id'=>10,'id_usuario'=>7,'nombre'=>'Profesional']);
        DB::table('fichas_atenciones')->insert([['id'=>100,'id_mascota'=>1,'id_paciente'=>9,'id_profesional'=>10,'id_lugar_atencion'=>20],['id'=>101,'id_mascota'=>2,'id_paciente'=>9,'id_profesional'=>10,'id_lugar_atencion'=>20]]);
        $user=new User();$user->id=7;Auth::setUser($user);
    }

    private function servicio(): OdontologiaVeterinaria {return app(OdontologiaVeterinaria::class);}
    private function datos(array $extra=[]): array
    {
        return array_replace(['mascota_id'=>1,'ficha_atencion_id'=>100,'lugar_atencion_id'=>20,'paciente_id'=>9,'pieza'=>104,'hallazgo'=>'Fractura','tratamiento'=>'Extracción','valor'=>10000,'referencia_operacion'=>(string)Str::uuid()],$extra);
    }

    public function test_species_and_teeth_are_not_inferred_from_a_hardcoded_id(): void
    {
        $this->assertSame('felino',DenticionVeterinaria::especie(Mascota::find(1)));
        $this->assertSame('canino',DenticionVeterinaria::especie(Mascota::find(2)));
        $this->assertCount(30,DenticionVeterinaria::piezas('felino'));
        $this->assertCount(42,DenticionVeterinaria::piezas('canino'));
        $this->assertNotContains(111,DenticionVeterinaria::piezas('canino'));
        $this->expectException(ValidationException::class);
        $this->servicio()->guardarPieza($this->datos(['pieza'=>105]),10);
    }

    public function test_canine_piece_cannot_be_saved_to_a_feline_or_foreign_record(): void
    {
        $this->expectException(HttpException::class);
        $this->servicio()->guardarPieza($this->datos(['mascota_id'=>2]),10);
    }

    public function test_budget_and_tooth_are_saved_together_without_duplicate_retries(): void
    {
        $datos=$this->datos();$p=$this->servicio()->agregarTratamiento($datos,10);
        $this->servicio()->agregarTratamiento($datos,10);
        $this->assertSame(1,PresupuestosDental::count());
        $this->assertCount(1,$this->servicio()->items($p));
        $this->assertSame(1,DB::table('odontogramas_mascotas')->where('estado',1)->count());
        $this->assertEquals(10000,$p->valor_total);
        $this->assertEquals(1,$p->id_mascota);
    }

    public function test_two_pets_with_the_same_responsible_have_separate_budgets(): void
    {
        $this->servicio()->agregarTratamiento($this->datos(),10);
        $p=$this->servicio()->agregarTratamiento($this->datos(['mascota_id'=>2,'ficha_atencion_id'=>101,'pieza'=>105]),10);
        $this->assertSame(2,PresupuestosDental::count());
        $this->assertEquals(2,$p->id_mascota);
        $this->assertSame('canino',$this->servicio()->items($p)[0]['especie']);
    }

    public function test_payments_are_idempotent_and_do_not_approve_or_finish_work(): void
    {
        $p=$this->servicio()->agregarTratamiento($this->datos(),10);
        $datos=['monto'=>10000,'metodo_pago'=>'Efectivo','referencia_operacion'=>(string)Str::uuid()];
        $this->servicio()->abonar($p,$datos,10);$p=$this->servicio()->abonar($p,$datos,10);
        $resumen=$this->servicio()->resumen($p);
        $this->assertSame(1,DB::table('pagos_presupuesto_dental')->count());
        $this->assertSame(0,$resumen['saldo']);
        $this->assertSame('Pagado',$resumen['estado_pago']);
        $this->assertFalse($resumen['aprobado']);
        $this->assertSame(1,$resumen['pendientes']);
    }

    public function test_overpayments_and_cancelling_paid_work_are_rejected(): void
    {
        $p=$this->servicio()->agregarTratamiento($this->datos(),10);
        $this->servicio()->abonar($p,['monto'=>8000,'metodo_pago'=>'Débito','referencia_operacion'=>(string)Str::uuid()],10);
        try { $this->servicio()->abonar($p,['monto'=>3000,'metodo_pago'=>'Débito','referencia_operacion'=>(string)Str::uuid()],10);$this->fail('Debe rechazar el sobrepago.'); }
        catch(ValidationException $error) {$this->assertArrayHasKey('monto',$error->errors());}
        $this->assertSame(1,DB::table('pagos_presupuesto_dental')->count());
        $this->expectException(ValidationException::class);
        $this->servicio()->actualizarTrabajo($p,$this->servicio()->items($p)[0]['id'],'cancelado',10);
    }

    public function test_work_status_persists_independently_of_payment(): void
    {
        $p=$this->servicio()->agregarTratamiento($this->datos(),10);$id=$this->servicio()->items($p)[0]['id'];
        foreach(['en_proceso','terminado','pendiente','cancelado'] as $estado) {
            $p=$this->servicio()->actualizarTrabajo($p,$id,$estado,10);
            $this->assertSame($estado,$this->servicio()->items($p)[0]['estado_trabajo']);
        }
        $this->assertEquals(0,$p->valor_total);
        $this->assertSame(0,$this->servicio()->resumen($p)['pendientes']);
    }

    public function test_final_chart_is_saved_by_species_and_old_snapshots_do_not_change(): void
    {
        $this->servicio()->guardarPieza($this->datos(),10);
        $felino=$this->servicio()->guardarEnFicha(FichaAtencion::find(100));
        $canino=$this->servicio()->guardarEnFicha(FichaAtencion::find(101));
        $this->assertCount(30,$felino['piezas']);$this->assertCount(42,$canino['piezas']);
        $antes=DB::table('odontogramas_fichas_mascotas')->where('ficha_atencion_id',100)->value('odontograma');
        $this->servicio()->guardarPieza($this->datos(['hallazgo'=>'Pieza ausente']),10);
        $this->assertSame($antes,DB::table('odontogramas_fichas_mascotas')->where('ficha_atencion_id',100)->value('odontograma'));
    }

    public function test_budget_failure_rolls_back_the_tooth_change(): void
    {
        DB::statement("CREATE TRIGGER reject_budget BEFORE INSERT ON presupuestos_dental BEGIN SELECT RAISE(ABORT,'budget failure'); END");
        try {$this->servicio()->agregarTratamiento($this->datos(),10);$this->fail('Debe fallar toda la operación.');}
        catch(\Illuminate\Database\QueryException $error){$this->assertStringContainsString('budget failure',$error->getMessage());}
        $this->assertSame(0,DB::table('odontogramas_mascotas')->count());
    }

    public function test_actual_endpoints_validate_faces_save_periodontal_data_and_use_local_uco_price(): void
    {
        $control=app(OdontologiaVeterinariaController::class);
        $response=$control->guardarPieza(Request::create('/','POST',$this->datos(['caras'=>['Vestibular','Mesial']])),$this->servicio());
        $this->assertSame(1,$response->getData()->estado);
        $control->guardarPeriodonto(Request::create('/','POST',$this->datos(['profundidad'=>3,'recesion'=>1,'sangrado'=>true])),$this->servicio());
        $this->assertEquals(3,DB::table('periodontogramas_mascotas')->value('profundidad'));
        DB::table('tarifas_uco_odontologia_veterinaria')->insert([
            ['id'=>1,'id_profesional'=>null,'id_lugar_atencion'=>null,'codigo'=>'EXT','categoria'=>'Cirugía','nombre'=>'Extracción','uco'=>2,'valor_uco'=>5000,'activo'=>1],
            ['id'=>2,'id_profesional'=>10,'id_lugar_atencion'=>null,'codigo'=>'EXT','categoria'=>'Cirugía','nombre'=>'Extracción','uco'=>2,'valor_uco'=>6000,'activo'=>1],
            ['id'=>3,'id_profesional'=>10,'id_lugar_atencion'=>20,'codigo'=>'EXT','categoria'=>'Cirugía','nombre'=>'Extracción','uco'=>2,'valor_uco'=>7000,'activo'=>1],
        ]);
        $response=$control->crearPresupuesto(Request::create('/','POST',$this->datos(['tarifa_uco_id'=>3,'valor'=>1])),$this->servicio());
        $this->assertSame(14000,$response->getData()->presupuesto->total);
    }

    public function test_final_clinical_save_persists_the_complete_chart_and_preserves_budgets_and_payments(): void
    {
        DB::table('horas_medicas')->insert(['id'=>1,'id_ficha_atencion'=>100,'id_mascota'=>1,'id_paciente'=>9,'id_profesional'=>10,'id_lugar_atencion'=>20,'id_estado'=>5]);
        $p=$this->servicio()->agregarTratamiento($this->datos(),10);
        $this->servicio()->abonar($p,['monto'=>5000,'metodo_pago'=>'Efectivo','referencia_operacion'=>(string)Str::uuid()],10);
        app(\App\Services\GuardarFichaVeterinaria::class)->guardar(Request::create('/','POST',['hora_medica'=>1,'descripcion_hipotesis'=>'Fractura dental','anestesia_dental'=>'Registro de monitorización']),10,'odontologia');
        $snapshot=DB::table('odontogramas_fichas_mascotas')->where('ficha_atencion_id',100)->first();
        $this->assertSame('felino',$snapshot->especie);
        $this->assertCount(30,json_decode($snapshot->odontograma,true));
        $this->assertSame(1,PresupuestosDental::count());
        $this->assertSame(1,DB::table('pagos_presupuesto_dental')->count());
        $datos=json_decode(DB::table('ficha_veterinaria_general')->value('datos'),true);
        $this->assertSame('Registro de monitorización',$datos['anestesia_dental']);
        $this->assertSame('felino',$datos['odontograma_veterinario']['especie']);
    }

    public function test_budget_pdf_is_generated_and_state_does_not_mix_pets(): void
    {
        $p=$this->servicio()->agregarTratamiento($this->datos(),10);
        $this->servicio()->agregarTratamiento($this->datos(['mascota_id'=>2,'ficha_atencion_id'=>101]),10);
        $controller=app(OdontologiaVeterinariaController::class);
        $response=$controller->estado(Request::create('/','GET',['mascota_id'=>1,'ficha_atencion_id'=>100]),$this->servicio());
        $this->assertCount(1,$response->getData()->presupuestos);
        $pdf=$controller->pdf($p,$this->servicio());
        $this->assertSame('application/pdf',$pdf->headers->get('Content-Type'));
        $this->assertStringStartsWith('%PDF',$pdf->getContent());
    }
}
