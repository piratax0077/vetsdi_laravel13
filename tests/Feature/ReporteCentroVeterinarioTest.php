<?php

namespace Tests\Feature;

use App\Http\Controllers\ReporteCentroVeterinarioController;
use App\Models\User;
use App\Services\ReporteCentroVeterinario;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth,Blade,DB,Schema};
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ReporteCentroVeterinarioTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();config(['database.default'=>'sqlite','database.connections.sqlite.database'=>':memory:']);DB::purge('sqlite');
        foreach([
            'users'=>['name','email'], 'instituciones'=>['nombre','id_usuario','id_responsable','id_lugar_atencion'],
            'admin_inst_serv'=>['id_admin'], 'contrato_dependiente'=>['id_empleado','tipo_empleado','estado','id_institucion'],
            'lugares_atencion'=>['nombre'],'sucursal'=>['id_institucion','id_lugar_atencion','estado'],
            'horas_medicas'=>['id_lugar_atencion','id_mascota','fecha_consulta','id_estado','comentarios_cancelacion'],
            'fichas_atenciones'=>['id_lugar_atencion','id_mascota','finalizada'],
            'bonos'=>['id_referencia','id_tipo_bono','devuelto','pago_confirmado_en','a_pagar','fecha_atencion'],
            'vet_reservas_servicios'=>['id_institucion','id_mascota','inicio','estado','precio'],
            'mascotas_lugares_atencion'=>['id_mascota','id_lugar_atencion'],
            'vet_pacientes_establecimiento'=>['id_institucion','id_mascota'],
            'detalles_receta'=>['id_ficha'],'certificados_reposos'=>['id_ficha_atencion'],
            'con_consentimientos_pcte'=>['id_fc'],'examen_especialidad'=>['id_ficha_atencion'],
            'vet_internaciones'=>['id_institucion','estado'],'vet_plazas'=>['id_institucion','activa'],
            'vet_obligaciones'=>['id_institucion','estado','monto','vencimiento'],'vet_convocatorias'=>['id_institucion','estado'],
        ] as $tabla=>$campos)Schema::create($tabla,function(Blueprint $t)use($campos){$t->id();foreach($campos as $c)$t->text($c)->nullable();$t->timestamps();});
        DB::table('users')->insert(['id'=>1,'name'=>'Admin','email'=>'admin@example.test']);Auth::setUser(User::find(1));
        DB::table('instituciones')->insert([['id'=>10,'nombre'=>'Centro prueba','id_usuario'=>1,'id_lugar_atencion'=>100],['id'=>20,'nombre'=>'Ajeno','id_usuario'=>2,'id_lugar_atencion'=>200]]);
        DB::table('lugares_atencion')->insert([['id'=>100,'nombre'=>'Matriz'],['id'=>101,'nombre'=>'Sucursal'],['id'=>200,'nombre'=>'Ajena']]);
        DB::table('sucursal')->insert(['id_institucion'=>10,'id_lugar_atencion'=>101,'estado'=>1]);
        $this->travelTo(\Carbon\Carbon::parse('2026-10-31 12:00:00','America/Santiago'));
    }

    private function req(array $p=[]): Request{return Request::create('/Administrador/Reporte_estadisticas','GET',$p+['id_institucion'=>10,'mes'=>10,'anio'=>2026]);}
    private function reporte(array $p=[]): array{return app(ReporteCentroVeterinario::class)->datos($this->req($p));}
    private function hora(int $id,int $mascota,int $estado,string $fecha='2026-10-05',int $lugar=100,?string $comentario=null): void
    {DB::table('horas_medicas')->insert(['id'=>$id,'id_mascota'=>$mascota,'id_estado'=>$estado,'fecha_consulta'=>$fecha,'id_lugar_atencion'=>$lugar,'comentarios_cancelacion'=>$comentario]);}
    private function ficha(int $id,int $mascota,string $fecha='2026-10-05 10:00:00',int $lugar=100): void
    {DB::table('fichas_atenciones')->insert(['id'=>$id,'id_mascota'=>$mascota,'finalizada'=>1,'created_at'=>$fecha,'id_lugar_atencion'=>$lugar]);}
    private function bono(int $hora,int $monto,?string $pago,int $devuelto=0,int $tipo=1): void
    {DB::table('bonos')->insert(['id_referencia'=>$hora,'a_pagar'=>$monto,'pago_confirmado_en'=>$pago,'devuelto'=>$devuelto,'id_tipo_bono'=>$tipo,'fecha_atencion'=>'2026-10-05']);}

    public function test_pdf_del_reporte_se_genera_y_queda_en_archivo_del_centro(): void
    {
        \Illuminate\Support\Facades\Storage::fake('local');
        (require database_path('migrations/2026_11_01_000006_archivo_pdf_veterinario.php'))->up();
        $pdf=app(ReporteCentroVeterinarioController::class)->index($this->req(['formato'=>'pdf']),app(ReporteCentroVeterinario::class));
        $this->assertStringStartsWith('%PDF-',$pdf->getContent());
        $doc=\App\Models\DocumentoPdfVeterinario::firstOrFail();
        $this->assertEquals(10,$doc->id_institucion);$this->assertNull($doc->id_mascota);
        \Illuminate\Support\Facades\Storage::disk('local')->assertExists($doc->ruta);
    }

    public function test_revenue_requires_confirmed_payment_and_excludes_refunds_foreign_centers_and_other_bono_types(): void
    {
        $this->hora(1,11,6);$this->hora(2,12,1,'2026-10-31',101);$this->hora(3,13,6,'2026-10-05',200);
        $this->bono(1,10000,'2026-10-05 10:00:00');$this->bono(2,15000,'2026-10-31 10:00:00');
        $this->bono(1,20000,null);$this->bono(1,90000,'2026-10-05 10:00:00',1);
        $this->bono(3,50000,'2026-10-05 10:00:00');$this->bono(1,70000,'2026-10-05 10:00:00',0,2);
        DB::table('vet_reservas_servicios')->insert(['id_institucion'=>10,'id_mascota'=>11,'estado'=>'completada','inicio'=>'2026-10-05 10:00:00','precio'=>90000]);
        $d=$this->reporte();$this->assertSame(25000,$d['ingresoTotal']);$this->assertSame(15000,$d['recaudadoHoy']);$this->assertSame(20000,$d['cobrosPendientes']);
        $d=$this->reporte(['lugar'=>100]);$this->assertSame(10000,$d['ingresoTotal']);$this->assertSame(0,$d['recaudadoHoy']);
    }

    public function test_attendance_separates_pending_cancellations_without_origin_and_explicit_absences(): void
    {
        foreach([6,7,3,3,15,1,14] as $i=>$estado)$this->hora($i+1,11+$i,$estado,'2026-10-05',100,$i===2?'Cancelada por tutor':null);
        DB::table('vet_reservas_servicios')->insert([
            ['id_institucion'=>10,'estado'=>'completada','inicio'=>'2026-10-06 10:00:00'],
            ['id_institucion'=>10,'estado'=>'cancelada','inicio'=>'2026-10-06 10:00:00'],
            ['id_institucion'=>10,'estado'=>'no_asiste','inicio'=>'2026-10-06 10:00:00'],
            ['id_institucion'=>20,'estado'=>'completada','inicio'=>'2026-10-06 10:00:00'],
        ]);
        $d=$this->reporte();$this->assertSame(2,$d['asistieron']);$this->assertSame(2,$d['noAsistieron']);
        $this->assertSame(1,$d['canceladasPaciente']);$this->assertSame(1,$d['canceladasLugar']);$this->assertSame(2,$d['canceladasSinOrigen']);$this->assertSame(1,$d['pendientesAgenda']);$this->assertSame(9,$d['totalAgenda']);$this->assertSame(4,$d['totalEvaluadas']);
    }

    public function test_month_series_is_contiguous_on_day_31_and_counts_completed_clinical_and_service_work(): void
    {
        $this->ficha(1,11,'2026-10-05 10:00:00');$this->ficha(2,12,'2026-09-20 10:00:00');$this->ficha(3,13,'2026-10-05 10:00:00',200);
        DB::table('vet_reservas_servicios')->insert(['id_institucion'=>10,'estado'=>'completada','inicio'=>'2026-10-20 10:00:00']);
        $d=$this->reporte();$this->assertSame(2,$d['atencionesRealizadas']);
        $this->assertSame(['2026-05','2026-06','2026-07','2026-08','2026-09','2026-10'],$d['atencionesMes']->pluck('periodo')->all());
        $this->assertSame([0,0,0,0,1,2],$d['atencionesMes']->pluck('total')->all());
        $enero=$this->reporte(['mes'=>1]);$this->assertSame('2025-08',$enero['atencionesMes']->first()['periodo']);
        $this->assertSame(2025,$this->reporte(['mes'=>'actual','anio'=>2025])['anio']);
    }

    public function test_new_patients_are_deduplicated_and_existing_patients_are_not_new_again(): void
    {
        $this->hora(1,11,6,'2026-09-15');$this->hora(2,11,6);$this->hora(3,12,6);$this->ficha(1,12);
        DB::table('mascotas_lugares_atencion')->insert(['id_mascota'=>13,'id_lugar_atencion'=>100,'created_at'=>'2026-10-01 00:00:00']);
        DB::table('vet_pacientes_establecimiento')->insert([['id_institucion'=>10,'id_mascota'=>12,'created_at'=>'2026-10-05 10:00:00'],['id_institucion'=>10,'id_mascota'=>14,'created_at'=>'2026-10-01 00:00:00'],['id_institucion'=>20,'id_mascota'=>15,'created_at'=>'2026-10-01 00:00:00']]);
        $this->assertSame(3,$this->reporte()['pacientesNuevos']);$this->assertSame(2,$this->reporte(['lugar'=>100])['pacientesNuevos']);
    }

    public function test_documents_use_issue_period_and_only_veterinary_files_from_selected_scope(): void
    {
        $this->ficha(1,11,'2026-09-01 10:00:00');$this->ficha(2,12,'2026-10-01 10:00:00',200);
        foreach([1,1,2] as $ficha)DB::table('detalles_receta')->insert(['id_ficha'=>$ficha,'created_at'=>'2026-10-01 00:00:00']);
        DB::table('certificados_reposos')->insert(['id_ficha_atencion'=>1,'created_at'=>'2026-10-31 23:59:59']);
        DB::table('examen_especialidad')->insert(['id_ficha_atencion'=>1,'created_at'=>'2026-11-01 00:00:00']);
        $d=$this->reporte();$this->assertSame(1,$d['documentos']['Recetas veterinarias']);$this->assertSame(1,$d['documentos']['Certificados veterinarios']);$this->assertSame(0,$d['documentos']['Órdenes de examen']);
    }

    public function test_branch_filter_excludes_unattributable_services_and_does_not_mislabel_hospital_totals(): void
    {
        $this->ficha(1,11);$this->ficha(2,12,'2026-10-01 10:00:00',101);
        DB::table('vet_reservas_servicios')->insert(['id_institucion'=>10,'estado'=>'completada','inicio'=>'2026-10-01 10:00:00']);
        DB::table('vet_internaciones')->insert(['id_institucion'=>10,'estado'=>'hospitalizada']);
        $this->assertSame(3,$this->reporte()['atencionesRealizadas']);$d=$this->reporte(['lugar'=>100]);
        $this->assertSame(1,$d['atencionesRealizadas']);$this->assertNull($d['resumenVeterinario']['Mascotas hospitalizadas']);$this->assertFalse($d['incluyeServicios']);
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);$this->reporte(['lugar'=>200]);
    }

    public function test_foreign_institution_cannot_be_exported(): void
    {
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);$this->reporte(['id_institucion'=>20,'formato'=>'csv']);
    }

    public function test_missing_source_is_not_reported_as_zero_and_view_renders_explicit_unavailability(): void
    {
        Schema::drop('bonos');$d=$this->reporte();$this->assertNull($d['ingresoTotal']);$this->assertContains('Pagos de consultas',$d['fuentesNoDisponibles']);
        $fuente=file_get_contents(resource_path('views/app/adm_cm/reporte_estadisticas.blade.php'));
        $fuente=str_replace('@endsection','',preg_replace('/@(?:extends|section)\([^\n]*\)/','',$fuente));
        $html=Blade::render($fuente,$d);$this->assertStringContainsString('No disponible',$html);$this->assertStringContainsString('name="anio"',$html);
        $this->assertStringNotContainsString('serieConvenioJson',$html);
    }

    public function test_csv_export_contains_filtered_metrics_six_months_and_escapes_formulas(): void
    {
        DB::table('instituciones')->where('id',10)->update(['nombre'=>'=FORMULA']);$this->hora(1,11,6);$this->bono(1,12000,'2026-10-05 10:00:00');
        $response=app(ReporteCentroVeterinarioController::class)->index($this->req(['formato'=>'csv']),app(ReporteCentroVeterinario::class));
        ob_start();$response->sendContent();$csv=ob_get_clean();
        $this->assertStringStartsWith("\xEF\xBB\xBF",$csv);$this->assertStringContainsString("'=FORMULA",$csv);$this->assertStringContainsString('12000',$csv);$this->assertStringContainsString('Atenciones 2026-05',$csv);$this->assertStringContainsString('2026-10.csv',$response->headers->get('Content-Disposition'));
    }

    public function test_invalid_period_is_rejected(): void
    {
        $this->expectException(ValidationException::class);$this->reporte(['mes'=>13]);
    }

    public function test_active_branch_is_preserved_and_all_branches_can_be_selected_explicitly(): void
    {
        $this->ficha(1,11);$this->ficha(2,12,'2026-10-01 10:00:00',101);
        $d=$this->reporte(['contexto'=>'admin:10:101']);$this->assertSame(101,$d['idLugar']);$this->assertSame(1,$d['atencionesRealizadas']);
        $d=$this->reporte(['contexto'=>'admin:10:101','lugar'=>'todos']);$this->assertNull($d['idLugar']);$this->assertSame(2,$d['atencionesRealizadas']);
    }
}
