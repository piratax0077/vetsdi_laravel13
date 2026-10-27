<?php
namespace App\Services;
use App\Models\Peluqueria;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
class PeluqueriaAgenda
{
 public function validar(Peluqueria $perfil,Carbon $inicio,Carbon $fin,$sucursal,?int $personalId=null,bool $agendaTitular=false): void {
  $horario=$sucursal?->agenda?json_decode($sucursal->agenda,true):null;
  if($horario){
   if(isset($horario['tramos'])){
    $cabe=false;
    foreach($horario['tramos'] as $tramo){
     if((int)$tramo['dia']!==$inicio->dayOfWeekIso)continue;
     $abre=$inicio->copy()->setTimeFromTimeString($tramo['inicio']);$cierra=$inicio->copy()->setTimeFromTimeString($tramo['fin']);
     if($inicio->isSameDay($fin)&&$inicio->gte($abre)&&$fin->lte($cierra)){$cabe=true;break;}
    }
    if(!$cabe)$this->error('El procedimiento completo debe caber en un tramo de atención de la sucursal.');
   }else{
   $abre=$inicio->copy()->setTimeFromTimeString($horario['apertura']);$cierra=$inicio->copy()->setTimeFromTimeString($horario['cierre']);
   if(!in_array($inicio->dayOfWeekIso,$horario['dias'],true)||!$inicio->isSameDay($fin)||$inicio->lt($abre)||$fin->gt($cierra))$this->error('La reserva está fuera del horario configurado para esta sucursal.');
   if(!empty($horario['pausa_inicio'])&&$inicio->lt($inicio->copy()->setTimeFromTimeString($horario['pausa_fin']))&&$fin->gt($inicio->copy()->setTimeFromTimeString($horario['pausa_inicio'])))$this->error('La reserva coincide con el descanso de la sucursal.');
   }
   if($inicio->lt(Carbon::now(config('peluqueria.timezone'))->addHours($horario['anticipacion_horas'])))$this->error('La reserva no cumple la anticipación mínima configurada.');
   if($inicio->gt(Carbon::now(config('peluqueria.timezone'))->addDays($horario['anticipacion_dias'])))$this->error('La fecha supera el plazo máximo de reservas.');
  }
  if($sucursal && DB::table('peluqueria_bloqueos')->where('peluqueria_id',$perfil->id)->where('sucursal_id',$sucursal->id)->where('inicio','<',$fin)->where('fin','>',$inicio)->exists())$this->error('La sucursal tiene un bloqueo de agenda en ese horario.');
  $consulta=DB::table('peluqueria_citas')->where('peluqueria_id',$perfil->id)->where('estado','<>','cancelada')->where('inicio','<',$fin)->where('fin','>',$inicio);
  if($sucursal)$consulta->where(fn($q)=>$q->where('sucursal_id',$sucursal->id)->orWhereNull('sucursal_id'));
  $eventos=[[$inicio->timestamp,1],[$fin->timestamp,-1]];
  foreach($consulta->get() as $c){$eventos[]=[Carbon::parse($c->inicio,config('peluqueria.timezone'))->timestamp,1];$eventos[]=[Carbon::parse($c->fin,config('peluqueria.timezone'))->timestamp,-1];}
  usort($eventos,fn($a,$b)=>($a[0]<=>$b[0])?:($a[1]<=>$b[1]));$ocupacion=0;$capacidad=$sucursal?->capacidad??1;
  foreach($eventos as [$instante,$delta]){ $ocupacion+=$delta;if($ocupacion>$capacidad)$this->error('Ya existe una reserva o se alcanzó la capacidad de esta sucursal en ese horario.');}
  if($agendaTitular && DB::table('peluqueria_citas')->where('peluqueria_id',$perfil->id)->whereNull('personal_id')->where('estado','<>','cancelada')->where('inicio','<',$fin)->where('fin','>',$inicio)->exists())$this->error('El profesional ya tiene una reserva en ese horario.');
  if($personalId){
   $personal=DB::table('peluqueria_personal')->where('peluqueria_id',$perfil->id)->where('id',$personalId)->where('activo',true)->first();
   if(!$personal||($personal->sucursal_id&&(int)$personal->sucursal_id!==(int)$sucursal?->id))$this->error('El integrante seleccionado no está disponible en esta sucursal.');
   if(DB::table('peluqueria_citas')->where('peluqueria_id',$perfil->id)->where('personal_id',$personalId)->where('estado','<>','cancelada')->where('inicio','<',$fin)->where('fin','>',$inicio)->exists())$this->error('La persona seleccionada ya tiene una reserva en ese horario.');
  }
 }
 private function error(string $mensaje): never {throw ValidationException::withMessages(['inicio'=>$mensaje]);}
}
