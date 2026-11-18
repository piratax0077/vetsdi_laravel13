<?php
namespace App\Http\Controllers;

use App\Models\{Paciente, Profesional};
use App\Services\ExportacionPacientesProfesional;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth,Log};

class ExportacionPacientesProfesionalController extends Controller
{
    public function descargar(Request $r, ExportacionPacientesProfesional $exportacion)
    {
        $r->validate(['id_profesional'=>'prohibited','id_usuario'=>'prohibited','id_institucion'=>'prohibited','rut_tutor'=>['required_if:alcance,tutor','nullable','string','max:20','regex:/^[0-9.\-]+[0-9kK]$/D'],'alcance'=>'nullable|in:todos,tutor']);
        $profesional=Profesional::where('id_usuario',Auth::id())->first();
        abort_unless($profesional,403,'Debe ingresar con un perfil profesional.');
        $tutor = null;
        if ($r->input('alcance') === 'tutor') {
            $rut = strtoupper(str_replace(['.', '-'], '', $r->input('rut_tutor')));
            $ids = Paciente::whereRaw("UPPER(REPLACE(REPLACE(rut, '.', ''), '-', '')) = ?", [$rut])->limit(2)->pluck('id');
            if ($ids->count() !== 1) {
                throw \Illuminate\Validation\ValidationException::withMessages(['rut_tutor' => 'No se encontraron atenciones propias para ese RUT.']);
            }
            $tutor = (int) $ids->first();
        }
        $archivo=$exportacion->crear($profesional, $tutor);
        try {
            Log::info('Exportación de pacientes del profesional',['id_usuario'=>Auth::id(),'id_profesional'=>$profesional->id,'formato'=>'zip_csv']);
            return response()->streamDownload(function() use($archivo){
                try {$entrada=fopen($archivo,'rb');try{while(!feof($entrada))echo fread($entrada,1024*1024);}finally{fclose($entrada);}}
                finally{if(is_file($archivo))unlink($archivo);}
            },'mis-pacientes-atenciones-'.now()->format('Ymd-His').'.zip',['Cache-Control'=>'private, no-store, max-age=0','Pragma'=>'no-cache','X-Content-Type-Options'=>'nosniff','Content-Type'=>'application/zip']);
        } catch(\Throwable $e){if(is_file($archivo))unlink($archivo);throw $e;}
    }
}
