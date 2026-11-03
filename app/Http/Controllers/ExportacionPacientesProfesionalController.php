<?php
namespace App\Http\Controllers;

use App\Models\Profesional;
use App\Services\ExportacionPacientesProfesional;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth,Log};

class ExportacionPacientesProfesionalController extends Controller
{
    public function descargar(Request $r, ExportacionPacientesProfesional $exportacion)
    {
        $r->validate(['id_profesional'=>'prohibited','id_usuario'=>'prohibited','id_institucion'=>'prohibited']);
        $profesional=Profesional::where('id_usuario',Auth::id())->first();
        abort_unless($profesional,403,'Debe ingresar con un perfil profesional.');
        $archivo=$exportacion->crear($profesional);
        try {
            Log::info('Exportación de pacientes del profesional',['id_usuario'=>Auth::id(),'id_profesional'=>$profesional->id,'formato'=>'zip_csv']);
            return response()->streamDownload(function() use($archivo){
                try {$entrada=fopen($archivo,'rb');try{while(!feof($entrada))echo fread($entrada,1024*1024);}finally{fclose($entrada);}}
                finally{if(is_file($archivo))unlink($archivo);}
            },'mis-pacientes-atenciones-'.now()->format('Ymd-His').'.zip',['Cache-Control'=>'private, no-store, max-age=0','Pragma'=>'no-cache','X-Content-Type-Options'=>'nosniff','Content-Type'=>'application/zip']);
        } catch(\Throwable $e){if(is_file($archivo))unlink($archivo);throw $e;}
    }
}
