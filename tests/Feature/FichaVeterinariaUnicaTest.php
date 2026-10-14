<?php

namespace Tests\Feature;

use App\Models\Mascota;
use App\Services\FichaVeterinariaUnica;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{Blade, DB, Schema};
use Tests\TestCase;

class FichaVeterinariaUnicaTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp(); config(['database.default'=>'sqlite','database.connections.sqlite.database'=>':memory:']);DB::purge('sqlite');
        foreach ([
            'mascotas'=>['id_responsable','nombre','especie_id','fecha_nacimiento','sexo'],
            'especies_mascotas'=>['nombre'],'razas_mascotas'=>['nombre'],'tamanos_mascotas'=>['nombre'],
            'profesionales'=>['nombre','apellido_uno','apellido_dos'],'lugares_atencion'=>['nombre'],
            'fichas_atenciones'=>['id_mascota','id_paciente','id_profesional','id_lugar_atencion','peso','hipotesis_diagnostico'],
            'ficha_veterinaria_general'=>['id_fichas_atenciones','datos'],
            'presupuestos_mascota'=>['id_ficha_atencion','tratamiento'],
            'con_consentimientos_pcte'=>['id_fc','id_consent','fecha_cons','confirmacion'],
            'con_consentimientos'=>['nombre'],
            'documentos_laboratorio_mascota'=>['id_mascota','nombre_original','estado'],
            'odontogramas_mascotas'=>['mascota_id','especie','estado','pieza','hallazgo'],
        ] as $tabla=>$campos) Schema::create($tabla,function(Blueprint $t)use($campos){$t->id();foreach($campos as $c)$t->text($c)->nullable();$t->timestamps();});
        DB::table('especies_mascotas')->insert([['id'=>1,'nombre'=>'Felina'],['id'=>2,'nombre'=>'Ave']]);
        DB::table('mascotas')->insert(['id'=>1,'nombre'=>'Mascota Uno','especie_id'=>1,'id_responsable'=>22]);
        DB::table('profesionales')->insert(['id'=>3,'nombre'=>'Profesional','apellido_uno'=>'Uno']);
        DB::table('lugares_atencion')->insert(['id'=>4,'nombre'=>'Clínica']);
        DB::table('fichas_atenciones')->insert([
            ['id'=>1,'id_mascota'=>1,'id_paciente'=>11,'id_profesional'=>3,'id_lugar_atencion'=>4,'peso'=>'4.2','hipotesis_diagnostico'=>'Historial previo al cambio de tutor','created_at'=>'2026-01-01'],
            ['id'=>2,'id_mascota'=>1,'id_paciente'=>22,'id_profesional'=>3,'id_lugar_atencion'=>4,'peso'=>null,'hipotesis_diagnostico'=>'Control reciente','created_at'=>'2026-02-01'],
            ['id'=>3,'id_mascota'=>2,'id_paciente'=>22,'id_profesional'=>3,'id_lugar_atencion'=>4,'peso'=>'18','hipotesis_diagnostico'=>'Otra mascota','created_at'=>'2026-03-01'],
        ]);
        DB::table('ficha_veterinaria_general')->insert(['id_fichas_atenciones'=>1,'datos'=>json_encode(['dieta'=>'Dieta conservada','medicamentos'=>'[{"medicamento":"Receta guardada"}]'])]);
        DB::table('documentos_laboratorio_mascota')->insert([['id_mascota'=>1,'nombre_original'=>'Hemograma mascota uno','estado'=>'revisado'],['id_mascota'=>2,'nombre_original'=>'Examen ajeno','estado'=>'pendiente']]);
        DB::table('odontogramas_mascotas')->insert([
            ['mascota_id'=>1,'especie'=>'felino','estado'=>1,'pieza'=>104,'hallazgo'=>'Hallazgo felino'],
            ['mascota_id'=>1,'especie'=>'canino','estado'=>1,'pieza'=>110,'hallazgo'=>'Especie incorrecta'],
            ['mascota_id'=>2,'especie'=>'felino','estado'=>1,'pieza'=>104,'hallazgo'=>'Ajeno'],
        ]);
    }

    public function test_history_keeps_previous_tutor_records_and_excludes_other_pets(): void
    {
        $datos=app(FichaVeterinariaUnica::class)->datos(Mascota::find(1));
        $this->assertSame([2,1],$datos['fichas']->pluck('id')->all());
        $this->assertSame('4.2',$datos['peso']);
        $this->assertSame('Dieta conservada',$datos['detalles'][1]['dieta']);
        $this->assertCount(1,$datos['odontograma']);
        $this->assertSame('Hallazgo felino',$datos['odontograma']->first()->hallazgo);
        $this->assertCount(1,$datos['documentos']);
        $this->assertSame('Hemograma mascota uno',$datos['documentos']->first()['nombre']);
    }

    public function test_other_species_can_open_record_without_a_canine_or_feline_chart(): void
    {
        $mascota=Mascota::find(1);$mascota->especie_id=2;
        $this->assertCount(0,app(FichaVeterinariaUnica::class)->datos($mascota)['odontograma']);
    }

    public function test_fvu_renders_saved_history_prescription_weight_and_dental_findings(): void
    {
        $html=Blade::render('@include("general.secciones_ficha.fmu_mascota")',['mascota'=>Mascota::find(1)]);
        foreach(['Historial previo al cambio de tutor','Control reciente','4.2 kg','Dieta conservada','Receta guardada','Hallazgo felino','Hemograma mascota uno']as $texto)$this->assertStringContainsString($texto,$html);
        $this->assertStringNotContainsString('Otra mascota',$html);
        $this->assertStringNotContainsString('Especie incorrecta',$html);
    }
}
