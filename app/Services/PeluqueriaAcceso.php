<?php
namespace App\Services;
use App\Models\{Peluqueria,User};
use Illuminate\Support\Facades\{Auth,DB,Schema};
class PeluqueriaAcceso
{
 public function opciones(?User $usuario=null) {
  $usuario=$usuario??Auth::user();if(!$usuario)return collect();
  $propios=Peluqueria::where('id_usuario',$usuario->id)->whereExists(fn($q)=>$q->selectRaw('1')->from('usuario_roles')->whereColumn('usuario_roles.id_usuario','peluquerias.id_usuario')->whereIn('tipo',['peluqueria','centro_estetico'])->where('estado',1))->get();
  if(!Schema::hasTable('peluqueria_personal'))return $propios;
  $compartidos=Peluqueria::whereIn('id',DB::table('peluqueria_personal')->where('usuario_id',$usuario->id)->where('activo',true)->select('peluqueria_id'))
   ->whereExists(fn($q)=>$q->selectRaw('1')->from('usuario_roles')->whereColumn('usuario_roles.id_usuario','peluquerias.id_usuario')->whereIn('tipo',['peluqueria','centro_estetico'])->where('estado',1))->get();
  return $propios->concat($compartidos)->unique('id')->values();
 }
 public function tieneMembresia(User $usuario): bool {
  return Schema::hasTable('peluqueria_personal') && Schema::hasTable('peluquerias') && $this->opciones($usuario)->contains(fn($p)=>(int)$p->id_usuario!==(int)$usuario->id);
 }
 public function tieneMembresiaRol(User $usuario,string $tipo): bool {
  if(!$this->tieneMembresia($usuario))return false;
  if($tipo==='centro_estetico')return true;
  return DB::table('peluqueria_personal')->where('usuario_id',$usuario->id)->where('activo',true)->whereIn('peluqueria_id',$this->opciones($usuario)->pluck('id'))->get()->contains(fn($m)=>in_array('atender',json_decode($m->permisos,true)??[],true));
 }
 public function rutaBase(): string {
  return str_starts_with(request()->route()?->getName()??'','centro_estetico.')||session('rol_activo')==='centro_estetico'?'centro_estetico':'peluqueria';
 }
 public function perfil(): Peluqueria {
  if($this->rutaBase()==='centro_estetico')abort_unless(DB::table('usuario_roles')->where('id_usuario',Auth::id())->where('tipo','centro_estetico')->where('estado',1)->exists()||$this->tieneMembresiaRol(Auth::user(),'centro_estetico'),403,'No tienes un rol activo de centro estético.');
  $opciones=$this->opciones();$id=session('peluqueria_contexto');
  $perfil=$id?$opciones->firstWhere('id',(int)$id):$opciones->first();abort_unless($perfil,403,'No tienes acceso activo a esta peluquería. Vuelve a seleccionar tu perfil.');return $perfil;
 }
 public function propietario(Peluqueria $perfil): bool {return (int)$perfil->id_usuario===(int)Auth::id();}
 public function miembro(Peluqueria $perfil) {
  if($this->propietario($perfil)||!Schema::hasTable('peluqueria_personal'))return null;
  return DB::table('peluqueria_personal')->where('peluqueria_id',$perfil->id)->where('usuario_id',Auth::id())->where('activo',true)->first();
 }
 public function permisos(Peluqueria $perfil): array {
  if($this->propietario($perfil))$permisos=array_fill_keys(array_merge(array_keys(config('peluqueria.permisos')),['personal']),true);
  else{$m=$this->miembro($perfil);$lista=$m?json_decode($m->permisos,true):[];$permisos=array_fill_keys(array_intersect($lista??[],array_keys(config('peluqueria.permisos'))),true);}
  if($this->rutaBase()==='centro_estetico'||($this->propietario($perfil)&&!DB::table('usuario_roles')->where('id_usuario',Auth::id())->where('tipo','peluqueria')->where('estado',1)->exists()))unset($permisos['atender']);
  elseif(session('rol_activo')==='peluqueria')foreach(['personal','sucursales','negocio'] as $administrativo)unset($permisos[$administrativo]);
  return $permisos;
 }
 public function exigir(Peluqueria $perfil,string $permiso): void {abort_unless($this->permisos($perfil)[$permiso]??false,403,'No tienes permiso para esta operación.');}
 public function sucursales(Peluqueria $perfil) {
  if(!Schema::hasTable('peluqueria_sucursales'))return collect();
  $m=$this->miembro($perfil);
  return DB::table('peluqueria_sucursales')->where('peluqueria_id',$perfil->id)->when($m?->sucursal_id,fn($q)=>$q->where('id',$m->sucursal_id))->orderBy('nombre')->get();
 }
 public function sucursal(Peluqueria $perfil,$id=null,bool $activa=true) {
  $lista=$this->sucursales($perfil);$s=$id?$lista->firstWhere('id',(int)$id):($lista->firstWhere('activo',1)??(!$activa?$lista->first():null));
  if(!$s && !$id && $lista->isEmpty()){abort_if($this->miembro($perfil)?->sucursal_id,403);return null;}
  abort_unless($s&&(!$activa||$s->activo),403,'Sucursal no disponible para tu cuenta.');return $s;
 }
 public function filtrarCitas($query,Peluqueria $perfil,string $columna='sucursal_id') {
  $m=$this->miembro($perfil);if($m?->sucursal_id)$query->where($columna,$m->sucursal_id);return $query;
 }
 public function exigirCita(Peluqueria $perfil,$cita): void {
  abort_unless($cita,404);$m=$this->miembro($perfil);
  if(session('rol_activo')==='peluqueria')abort_unless($this->propietario($perfil)?!$cita->personal_id:($m&&(int)$m->id===(int)$cita->personal_id),403,'La atención pertenece a otro profesional de estética.');abort_if($m?->sucursal_id && (int)$m->sucursal_id!==(int)$cita->sucursal_id,403);
 }
}
