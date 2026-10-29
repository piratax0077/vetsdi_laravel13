<?php

namespace Tests\Feature;

use App\Models\{Mascota, Paciente};
use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class PacientesCentroVistaTest extends TestCase
{
    private function renderizar($mascotas): string
    {
        $fuente=file_get_contents(resource_path('views/app/adm_cm/mis_pacientes.blade.php'));
        $fuente=str_replace('@endsection','',preg_replace('/@(?:extends|section)\([^\n]*\)/','',$fuente));
        return Blade::render($fuente,['mascotas'=>collect($mascotas),'institucion'=>(object)['nombre'=>'Centro de prueba']]);
    }

    public function test_empty_patient_list_renders_one_table_without_unclosed_php(): void
    {
        $html=$this->renderizar([]);
        $this->assertStringContainsString('No hay mascotas ni responsables para el contexto activo.',$html);
        $this->assertSame(1,substr_count($html,'<table '));
        preg_match_all('/<div(?:\s|>)/',$html,$divs);
        $this->assertSame(count($divs[0]),substr_count($html,'</div>'));
    }

    public function test_patient_row_renders_once_and_passes_escaped_tutor_details_to_modal(): void
    {
        $tutor=new Paciente(['nombres'=>'Tutor <script>prueba</script>','apellido_uno'=>'Uno','rut'=>'12345678-5','telefono_uno'=>'912345678','email'=>'tutor@example.test']);
        $tutor->id=30;$tutor->setRelation('Prevision',null);
        $mascota=new Mascota(['nombre'=>'Luna','sexo'=>'F','galeria'=>[]]);$mascota->id=50;
        $mascota->setRelation('Responsable',$tutor);
        foreach(['especieMascota','razaMascota','tamanoMascota'] as $relacion)$mascota->setRelation($relacion,null);
        $html=$this->renderizar([$mascota]);
        $this->assertSame(1,substr_count($html,'class="btn btn-info btn-xxs js-ver-mascota"'));
        $this->assertStringContainsString('data-tutor-email="tutor@example.test"',$html);
        $this->assertStringContainsString('data-tutor-rut="12345678-5"',$html);
        $this->assertStringContainsString('data-foto-respaldo=',$html);
        $this->assertStringContainsString('Tutor &lt;script&gt;prueba&lt;/script&gt;',$html);
        $this->assertStringNotContainsString('Tutor <script>prueba</script>',$html);
        $this->assertSame(1,substr_count($html,'<table '));
    }
}
