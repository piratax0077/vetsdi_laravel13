<?php
namespace App\Services;

use App\Models\{DocumentoPdfVeterinario,FichaAtencion,Mascota,Paciente,Profesional};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth,DB,Schema,Storage};
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class ArchivoPdfVeterinario
{
    public function puedeVerMascota(int $id): bool
    {
        if(!Auth::check())return false;
        $mascota=Mascota::find($id);if(!$mascota)return false;
        if(Paciente::where('id_usuario',Auth::id())->whereKey($mascota->id_responsable)->exists())return true;
        $profesional=Schema::hasTable('profesionales')?Profesional::where('id_usuario',Auth::id())->first():null;
        if($profesional && Schema::hasTable('fichas_atenciones') && FichaAtencion::where('id_mascota',$id)->where('id_profesional',$profesional->id)->exists())return true;
        return Schema::hasTable('vet_internaciones') && Schema::hasTable('vet_internacion_profesionales') &&
            DB::table('vet_internaciones as i')->join('vet_internacion_profesionales as p','p.internacion_id','=','i.id')->where('i.id_mascota',$id)->where('p.id_usuario',Auth::id())->exists();
    }

    public function puedeVerCentro(int $id): bool
    {
        return Auth::check() && app(HospitalizacionAcceso::class)->administrados(Auth::user())->contains('id',$id);
    }

    public function autorizar(DocumentoPdfVeterinario $doc): void
    {
        abort_unless(($doc->id_mascota && $this->puedeVerMascota((int)$doc->id_mascota)) || ($doc->id_institucion && $this->puedeVerCentro((int)$doc->id_institucion)),403);
    }

    private function institucionLugar(?int $lugar): ?int
    {
        if(!$lugar)return null;
        if(Schema::hasTable('sucursal')) {
            $ids=DB::table('sucursal')->where('id_lugar_atencion',$lugar)->pluck('id_institucion')->filter()->unique();
            if($ids->count()===1)return (int)$ids->first();
        }
        if(Schema::hasTable('instituciones')) {
            $ids=DB::table('instituciones')->where('id_lugar_atencion',(string)$lugar)->pluck('id');
            if($ids->count()===1)return (int)$ids->first();
        }
        return null;
    }

    public function contexto(Request $r): array
    {
        $datos=$r->attributes->get('archivo_pdf_contexto',[]);
        if($datos)return $datos;
        foreach($r->route()?->parameters() ?? [] as $valor) {
            if($valor instanceof \Illuminate\Database\Eloquent\Model) {
                foreach(['id_mascota','id_institucion','id_ficha_atencion'] as $campo)if($valor->{$campo})$datos[$campo]=(int)$valor->{$campo};
            }
        }
        foreach(['id_ficha_atencion','id_ficha','id_fc'] as $campo)if($r->filled($campo))$datos['id_ficha_atencion']=(int)$r->input($campo);
        foreach(['id_examen_especialidad'=>['examen_especialidad','id_ficha_atencion'],'id_informe_medico'=>['informes_medicos','id_ficha_atencion'],'id_interconsulta'=>['interconsultas','id_ficha_atencion_soli'],'id_uso_personal'=>['usos_personales','id_ficha_atencion'],'id_receta'=>['recomendacion','atencion'],'hora_medica'=>['horas_medicas','id_ficha_atencion'],'id_hora_medica'=>['horas_medicas','id_ficha_atencion'],'id_consentimiento'=>['con_consentimientos_pcte','id_fc'],'id_presupuesto'=>['presupuestos_dental','id_ficha_atencion']] as $campo=>[$tabla,$ficha]) {
            if($r->filled($campo) && Schema::hasTable($tabla) && Schema::hasColumn($tabla,$ficha)) {
                $datos['id_ficha_atencion']=(int)DB::table($tabla)->where('id',$r->input($campo))->value($ficha);
            }
        }
        if(!empty($datos['id_ficha_atencion']) && Schema::hasTable('fichas_atenciones')) {
            $ficha=FichaAtencion::find($datos['id_ficha_atencion']);
            if(!$ficha)return [];
            $datos['id_mascota']=$ficha->id_mascota;$datos['id_institucion']=$ficha->id_institucion ?: $this->institucionLugar($ficha->id_lugar_atencion);
        }
        foreach(['id_mascota','mascota_id','mascota'] as $campo)if(empty($datos['id_mascota']) && is_numeric($r->input($campo) ?? $r->route($campo)))$datos['id_mascota']=(int)($r->input($campo) ?? $r->route($campo));
        if($r->filled('internacion') && Schema::hasTable('vet_internaciones')) {
            $i=DB::table('vet_internaciones')->find($r->internacion);if($i){$datos['id_mascota']=$i->id_mascota;$datos['id_institucion']=$i->id_institucion;}
        }
        if(empty($datos['id_mascota']) && empty($datos['id_institucion']) && $r->filled('id_institucion'))$datos['id_institucion']=(int)$r->id_institucion;
        if(empty($datos['id_mascota']) && empty($datos['id_institucion']) && $r->filled('id_lugar_atencion'))$datos['id_institucion']=$this->institucionLugar((int)$r->id_lugar_atencion);
        if(empty($datos['id_mascota']) && empty($datos['id_institucion']) && Auth::check() && $r->routeIs('adm_cm.*','adm_hospital.*','asistentecm.*','bodega.*')) {
            $centros=app(HospitalizacionAcceso::class)->administrados(Auth::user());
            $seleccion=$r->hasSession() ? $r->session()->get('admin_center_context') : null;
            if(preg_match('/^admin:(\d+):/',(string)$seleccion,$m) && $centros->contains('id',(int)$m[1]))$datos['id_institucion']=(int)$m[1];
            elseif($centros->count()===1)$datos['id_institucion']=$centros->first()->id;
        }
        return $datos;
    }

    public function registrar(Request $r,Response $response,?array $contexto=null): ?DocumentoPdfVeterinario
    {
        $bytes=$response->getContent();
        if(!Auth::check() || !is_string($bytes) || !str_starts_with($bytes,'%PDF-') || !Schema::hasTable('vet_documentos_pdf'))return null;
        $datos=$contexto ?? $this->contexto($r);
        if(empty($datos['id_mascota']) && empty($datos['id_institucion']))return null;
        if(!empty($datos['id_mascota'])) {if(!$this->puedeVerMascota((int)$datos['id_mascota']) && (empty($datos['id_institucion']) || !$this->puedeVerCentro((int)$datos['id_institucion'])))return null;}
        elseif(!$this->puedeVerCentro((int)$datos['id_institucion']))return null;
        $origen=substr($r->route()?->getName() ?? 'documento-veterinario',0,180);
        $huella=hash('sha256',json_encode($datos).$origen.$bytes);
        if($existe=DocumentoPdfVeterinario::where('huella',$huella)->first())return $existe;
        $nombre='documento-veterinario.pdf';
        if(preg_match('/filename="?([^";]+)/i',$response->headers->get('Content-Disposition',''),$m))$nombre=basename($m[1]);
        $id=(string)Str::uuid();$ruta='documentos-pdf/'.$id.'.pdf';
        if(!Storage::disk('local')->put($ruta,$bytes))throw new \RuntimeException('No fue posible archivar el PDF.');
        try{return DocumentoPdfVeterinario::create(['id'=>$id,'id_mascota'=>$datos['id_mascota'] ?? null,'id_institucion'=>$datos['id_institucion'] ?? null,'id_ficha_atencion'=>$datos['id_ficha_atencion'] ?? null,'id_usuario'=>Auth::id(),'nombre'=>mb_substr($nombre,0,200),'origen'=>$origen,'ruta'=>$ruta,'huella'=>$huella]);}
        catch(\Throwable $e){Storage::disk('local')->delete($ruta);throw $e;}
    }
}
