<?php
namespace Tests\Feature;
use Tests\TestCase;
use App\Models\{User,ProfesionalMiEquipo};
use App\Http\Controllers\ProfesionalMiEquipoController;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth,DB,Schema};
use Illuminate\Validation\ValidationException;
class EquiposQuirurgicosTest extends TestCase
{
 protected function setUp(): void {
  parent::setUp();config(['database.default'=>'sqlite','database.connections.sqlite.database'=>':memory:']);DB::purge('sqlite');
  foreach(['users'=>['name'],'profesionales'=>['id_usuario','nombre','apellido_uno','apellido_dos','id_tipo_especialidad','id_sub_tipo_especialidad','rut','email','telefono_uno'],'profesional_mi_equipo'=>['id_profesional','nombre','descripcion','estado'],'profesional_mi_equipo_profesionales'=>['id_profesional_mi_equipo','id_profesional','id_tipo_especialidad','id_sub_tipo_especialidad','posicion','estado'],'tipos_especialidad'=>['nombre'],'sub_tipo_especialidad'=>['nombre']] as $tabla=>$campos)Schema::create($tabla,function(Blueprint $t)use($campos){$t->id();foreach($campos as $c)$t->text($c)->nullable();$t->timestamps();});
  DB::table('users')->insert(['id'=>1,'name'=>'Usuario']);Auth::setUser(User::find(1));
  foreach([1,2,3,4] as $id)DB::table('profesionales')->insert(['id'=>$id,'id_usuario'=>$id,'nombre'=>'Profesional '.$id,'apellido_uno'=>'Apellido','apellido_dos'=>'Segundo']);
 }
 private function req(array $data): Request{return Request::create('/','POST',$data);}
 private function controller(): ProfesionalMiEquipoController{return app(ProfesionalMiEquipoController::class);}
 private function miembros(): array{return array_map(fn($id,$posicion)=>['profesional_id'=>$id,'posicion'=>$posicion],[1,2,3,4],['CIRUJANO','AYUDANTE','ARSENALERO','ANESTESISTA']);}
 private function crear(): int {$r=$this->controller()->registroMiEquipoProfesionales($this->req(['nombre'=>'Equipo','descripcion'=>'Completo','id_profesional'=>1,'profesionales'=>$this->miembros()]));$this->assertSame(4,$r['cant-prof-reg']);return $r['result-equipo']->last_id;}
 public function test_all_members_without_specialty_are_saved_and_returned(): void {
  $id=$this->crear();$result=$this->controller()->verDetalleEquipoProfesional($this->req(['id_profesional_mi_equipo'=>$id]));
  $this->assertCount(4,$result['registros']);$this->assertSame('ANESTESISTA',$result['registros'][3]->posicion);
  $this->assertCount(4,ProfesionalMiEquipo::find($id)->ProfesionalMiEquipoProfesionales);
 }
 public function test_invalid_edit_preserves_existing_members(): void {
  $id=$this->crear();$miembros=$this->miembros();$miembros[2]['profesional_id']=999;
  try{$this->controller()->modificarMiEquipoProfesionales($this->req(['id_equipo_modificar'=>$id,'profesionales'=>$miembros]));$this->fail();}catch(ValidationException $e){$this->assertCount(4,DB::table('profesional_mi_equipo_profesionales')->where('id_profesional_mi_equipo',$id)->get());}
 }
 public function test_edit_replaces_the_complete_list_without_duplicates(): void {
  $id=$this->crear();$miembros=$this->miembros();array_pop($miembros);
  $this->controller()->modificarMiEquipoProfesionales($this->req(['id_equipo_modificar'=>$id,'profesionales'=>$miembros]));
  $this->assertSame(3,DB::table('profesional_mi_equipo_profesionales')->count());
 }
 public function test_missing_professional_does_not_remove_other_members_from_detail(): void {
  $id=$this->crear();DB::table('profesionales')->where('id',2)->delete();
  $r=$this->controller()->verDetalleEquipoProfesional($this->req(['id_profesional_mi_equipo'=>$id]));$this->assertCount(4,$r['registros']);$this->assertNull($r['registros'][1]->Profesional);$this->assertNotNull($r['registros'][3]->Profesional);
 }
 public function test_cannot_read_or_modify_another_professionals_team(): void {
  $id=DB::table('profesional_mi_equipo')->insertGetId(['id_profesional'=>2,'nombre'=>'Ajeno','estado'=>1]);
  $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);$this->controller()->verDetalleEquipoProfesional($this->req(['id_profesional_mi_equipo'=>$id]));
 }
}
