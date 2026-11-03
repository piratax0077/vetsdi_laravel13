<?php
namespace App\Http\Controllers;

use App\Services\{ExportacionPacientesCentro,PerfilCentroMedico};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth,DB,Schema};

class ExportacionPacientesCentroController extends Controller
{
    public function descargar(Request $r, ExportacionPacientesCentro $exportacion)
    {
        $r->validate(['id_institucion'=>'required|integer|min:1']);
        $centro=app(PerfilCentroMedico::class)->centro($r->duplicate(['id_institucion'=>$r->id_institucion,'tipo_institucion'=>'institucion']));
        $archivo=$exportacion->crear($centro);
        try {
            if(Schema::hasTable('vet_operacion_auditoria')) DB::table('vet_operacion_auditoria')->insert(['id_institucion'=>$centro->id,'id_usuario'=>Auth::id(),'tabla'=>'exportacion_pacientes_atenciones','registro_id'=>$centro->id,'antes'=>null,'despues'=>json_encode(['formato'=>'zip_csv']),'created_at'=>now()]);
            return response()->streamDownload(function() use($archivo){
                try { $entrada=fopen($archivo,'rb'); try {while(!feof($entrada)){echo fread($entrada,1024*1024);}} finally {fclose($entrada);} }
                finally {if(is_file($archivo))unlink($archivo);}
            },'pacientes-atenciones-centro-'.$centro->id.'-'.now()->format('Ymd-His').'.zip',['Cache-Control'=>'private, no-store, max-age=0','Pragma'=>'no-cache','X-Content-Type-Options'=>'nosniff','Content-Type'=>'application/zip']);
        } catch(\Throwable $e){if(is_file($archivo))unlink($archivo);throw $e;}
    }
}
