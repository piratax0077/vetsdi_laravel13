<?php
namespace Tests\Feature;
use Tests\{TestCase,CuentasAisladas};
use App\Models\{User,HoraMedica};
use App\Services\{AccesoAgendaVeterinaria,CuentasService};
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{Auth,DB,Schema};
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
class AgendaRolesTest extends TestCase
{
 use CuentasAisladas;
 protected function setUp(): void {
  parent::setUp();$this->prepararCuentasAisladas();
  Schema::create('horas_medicas',function(Blueprint $t){$t->id();foreach(['id_paciente','id_profesional','id_lugar_atencion','id_mascota','id_estado'] as $campo)$t->integer($campo)->nullable();$t->timestamps();});
  foreach(['asistentes_lugar_atencion'=>['id_asistente','id_lugar_atencion','id_profesional','estado'],'profesionales_lugares_atencion'=>['id_profesional','id_lugar_atencion','id_institucion','estado']] as $tabla=>$campos)Schema::create($tabla,function(Blueprint $t)use($campos){$t->id();foreach($campos as $c)$t->integer($c)->nullable();$t->timestamps();});
  (require database_path('migrations/2026_10_13_180000_create_autorizaciones_asistentes_table.php'))->up();
  foreach([1=>'Tutor',2=>'Otro tutor',3=>'Profesional',4=>'Otro profesional',5=>'Asistente'] as $id=>$nombre){DB::table('users')->insert(['id'=>$id,'name'=>$nombre,'email'=>'agenda'.$id.'@example.test']);app(CuentasService::class)->asignarRol(User::find($id),$id<3?'tutor':($id<5?'profesional':'asistente'));}
  DB::table('pacientes')->insert([['id'=>11,'id_usuario'=>1],['id'=>12,'id_usuario'=>2]]);
  DB::table('profesionales')->insert([['id'=>21,'id_usuario'=>3],['id'=>22,'id_usuario'=>4]]);
  DB::table('asistentes')->insert(['id'=>31,'id_usuario'=>5]);
  DB::table('asistentes_lugar_atencion')->insert(['id_asistente'=>31,'id_lugar_atencion'=>41,'id_profesional'=>21,'estado'=>1]);
  DB::table('profesionales_lugares_atencion')->insert(['id_profesional'=>21,'id_lugar_atencion'=>41,'estado'=>1]);
  DB::table('horas_medicas')->insert(['id'=>1,'id_paciente'=>11,'id_profesional'=>21,'id_lugar_atencion'=>41,'id_estado'=>1]);
 }
 private function usuario(int $id): void {Auth::setUser(User::find($id));}
 private function acceso(string $accion,bool $tutor=false){return app(AccesoAgendaVeterinaria::class)->hora(1,$accion,$tutor);}
 public function test_tutor_solo_confirma_y_anula_sus_horas_pendientes(): void {
  $this->usuario(1);$this->assertSame(1,$this->acceso('confirmar',true)->id);$this->assertSame(1,$this->acceso('cancelar',true)->id);
  $this->usuario(2);$this->expectException(HttpException::class);$this->acceso('confirmar',true);
 }
 public function test_rutas_del_tutor_rechazan_otra_hora_sin_modificarla(): void {
  $this->usuario(2);
  foreach(['confirmarHoraMedica','anularHoraMedica'] as $metodo){try{app(\App\Http\Controllers\AppPacienteController::class)->$metodo(Request::create('/','POST',['id_hora'=>1]));$this->fail();}catch(HttpException $e){$this->assertSame(403,$e->getStatusCode());}}
  $this->assertSame(1,HoraMedica::find(1)->id_estado);
 }
 public function test_profesional_no_puede_ver_confirmar_anular_o_atender_la_agenda_ajena(): void {
  $this->usuario(4);
  foreach(['ver','confirmar','cancelar','atender'] as $accion){try{$this->acceso($accion);$this->fail();}catch(HttpException $e){$this->assertSame(403,$e->getStatusCode());}}
 }
 public function test_profesional_solo_abre_ficha_cuando_la_hora_esta_lista(): void {
  $this->usuario(3);try{$this->acceso('atender');$this->fail();}catch(ValidationException $e){$this->assertArrayHasKey('hora',$e->errors());}
  DB::table('horas_medicas')->where('id',1)->update(['id_estado'=>4]);$this->assertSame(1,$this->acceso('atender')->id);
 }
 public function test_asistente_necesita_permiso_explicito_y_no_puede_atender(): void {
  $this->usuario(5);try{$this->acceso('confirmar');$this->fail();}catch(HttpException $e){$this->assertSame(403,$e->getStatusCode());}
  DB::table('autorizaciones_asistentes')->insert(['id_asistente'=>31,'id_lugar_atencion'=>41,'id_profesional'=>21,'id_usuario_autoriza'=>3,'permisos'=>json_encode(['permiso_ver_agenda'=>true,'permiso_confirmar_hora'=>true,'permiso_anular_hora'=>true])]);
  $this->assertSame(1,$this->acceso('confirmar')->id);
  DB::table('horas_medicas')->where('id',1)->update(['id_estado'=>4]);
  foreach(['confirmar','cancelar','espera','atender'] as $accion){try{$this->acceso($accion);$this->fail();}catch(HttpException $e){$this->assertSame(403,$e->getStatusCode());}}
 }
 public function test_una_hora_en_atencion_o_cerrada_no_retrocede_por_confirmar_o_anular(): void {
  $this->usuario(1);
  foreach([3,4,5,6,8] as $estado){DB::table('horas_medicas')->where('id',1)->update(['id_estado'=>$estado]);foreach(['confirmar','cancelar'] as $accion){try{$this->acceso($accion,true);$this->fail();}catch(ValidationException $e){$this->assertArrayHasKey('hora',$e->errors());}}}
 }
 public function test_reserva_exige_tutor_mascota_y_lugar_autorizados_y_fecha_futura(): void {
  $this->usuario(1);DB::table('mascotas')->insert(['id'=>1,'nombre'=>'Mascota ajena','id_responsable'=>12]);
  $base=['fecha_consulta'=>now()->addDay()->format('Y-m-d H:i:s'),'id_lugar_atencion'=>41];
  app(AccesoAgendaVeterinaria::class)->reserva(Request::create('/','POST',$base),21,11);$this->addToAssertionCount(1);
  try{app(AccesoAgendaVeterinaria::class)->reserva(Request::create('/','POST',$base+['id_mascota'=>1]),21,11);$this->fail();}catch(HttpException $e){$this->assertSame(403,$e->getStatusCode());}
  try{app(AccesoAgendaVeterinaria::class)->reserva(Request::create('/','POST',$base),21,12);$this->fail();}catch(HttpException $e){$this->assertSame(403,$e->getStatusCode());}
  try{app(AccesoAgendaVeterinaria::class)->reserva(Request::create('/','POST',array_replace($base,['fecha_consulta'=>now()->subDay()->format('Y-m-d H:i:s')])),21,11);$this->fail();}catch(ValidationException $e){$this->assertArrayHasKey('fecha_consulta',$e->errors());}
  $this->usuario(3);
  try{app(AccesoAgendaVeterinaria::class)->reserva(Request::create('/','POST',array_replace($base,['id_lugar_atencion'=>99])),21,11);$this->fail();}catch(HttpException $e){$this->assertSame(422,$e->getStatusCode());}
 }
 public function test_un_cambio_concurrente_no_retrocede_la_hora_a_confirmada(): void {
  $this->usuario(3);$hora=$this->acceso('confirmar');$hora->id_estado=2;
  DB::table('horas_medicas')->where('id',1)->update(['id_estado'=>4]);
  try{app(AccesoAgendaVeterinaria::class)->guardarCambio($hora);$this->fail();}catch(ValidationException $e){$this->assertArrayHasKey('hora',$e->errors());}
  $this->assertSame(4,HoraMedica::find(1)->id_estado);
 }
 public function test_no_se_recepciona_pago_de_una_hora_cancelada(): void {
  $this->usuario(3);DB::table('horas_medicas')->where('id',1)->update(['id_estado'=>3]);
  $this->expectException(ValidationException::class);
  app(\App\Services\RecepcionPagoVeterinario::class)->recibir(Request::create('/','POST'),1);
 }
 public function test_las_rutas_de_ficha_alternativas_tambien_bloquean_al_asistente(): void {
  $this->usuario(5);
  foreach(['index','index_sdi','index_hospital_amb'] as $metodo){try{app(\App\Http\Controllers\ficha_atencionController::class)->$metodo(Request::create('/','GET',['id_hora_realizar'=>1]));$this->fail();}catch(HttpException $e){$this->assertSame(403,$e->getStatusCode());}}
 }
}
