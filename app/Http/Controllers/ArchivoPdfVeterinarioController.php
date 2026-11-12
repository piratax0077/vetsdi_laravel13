<?php
namespace App\Http\Controllers;

use App\Models\{DocumentoFcPaciente,DocumentoPdfVeterinario,Mascota,Paciente};
use App\Services\ArchivoPdfVeterinario;
use App\Services\DocumentoVeterinarioEmitido;
use App\Mail\CorreoGenerico;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB,Mail,Schema,Storage,URL};

class ArchivoPdfVeterinarioController extends Controller
{
    public function index(Request $r,ArchivoPdfVeterinario $archivo)
    {
        $r->validate(['id_mascota'=>'required_without:id_institucion|nullable|integer|min:1','id_institucion'=>'nullable|integer|min:1']);
        if($r->filled('id_mascota'))abort_unless($archivo->puedeVerMascota((int)$r->id_mascota),403);
        else abort_unless($archivo->puedeVerCentro((int)$r->id_institucion),403);
        [$documentos,$historicos]=$this->listados($r);
        return view('app.veterinaria.documentos.index',compact('documentos','historicos'));
    }

    /** Lista los PDF archivados y los documentos clínicos de una mascota o centro; la usan Documentos veterinarios y Mis documentos del tutor. */
    public function listados(Request $r): array
    {
        $documentos=DocumentoPdfVeterinario::query()->when($r->filled('id_mascota'),fn($q)=>$q->where('id_mascota',$r->id_mascota),fn($q)=>$q->where('id_institucion',$r->id_institucion))->latest()->paginate(20)->withQueryString();
        $historicos=collect();
        if($r->filled('id_mascota') && Schema::hasTable('documentos_laboratorio_mascota')) {
            foreach(DB::table('documentos_laboratorio_mascota')->where('id_mascota',$r->id_mascota)->where('mime','application/pdf')->latest('id')->limit(100)->get() as $d) {
                $historicos->push(['tipo'=>'laboratorio','id'=>$d->id,'titulo'=>$d->nombre_original,'fecha'=>$d->created_at ?? null]);
            }
        }
        if($r->filled('id_mascota') && Schema::hasTable('fichas_atenciones')) {
            if ($usuario = auth()->id()) {
                $historicos = $historicos->concat(DocumentoFcPaciente::indicacionesMascota((int) $r->id_mascota, $usuario)
                    ->where('documento', 'like', '%.pdf')->latest('id')->limit(100)->get()->map(fn ($documento) => [
                        'tipo' => 'indicacion',
                        'id' => $documento->id,
                        'titulo' => $documento->observacion ?: $documento->documento,
                        'fecha' => $documento->fecha_envio,
                    ]));
            }
            foreach($this->fuentes() as $tipo=>[$tabla,$campo,$titulo]) {
                if(!Schema::hasTable($tabla) || !Schema::hasColumn($tabla,$campo))continue;
                foreach(DB::table($tabla)->whereIn($campo,DB::table('fichas_atenciones')->where('id_mascota',$r->id_mascota)->select('id'))->orderByDesc('id')->limit(100)->get() as $d) {
                    $historicos->push(['tipo'=>$tipo,'id'=>$d->id,'titulo'=>$titulo,'fecha'=>$d->created_at ?? null]);
                }
            }
        }
        return [$documentos,$historicos];
    }

    private function fuentes(): array
    {
        return ['informe'=>['informes_medicos','id_ficha_atencion','Informe veterinario'],
            'reposo'=>['certificados_reposos','id_ficha_atencion','Certificado veterinario'],
            'interconsulta'=>['interconsultas','id_ficha_atencion_soli','Interconsulta veterinaria'],
            'uso'=>['usos_personales','id_ficha_atencion','Documento veterinario'],
            'receta'=>['recomendacion','atencion','Receta veterinaria'],
            'orden'=>['examenes_ppf','id_ficha_atencion','Orden de exámenes'],
            'examen'=>['examen_especialidad','id_ficha_atencion','Resultado de examen']];
    }

    public function historico(Request $r,ArchivoPdfVeterinario $archivo,string $tipo,int $origen)
    {
        if ($tipo === 'indicacion') {
            $documento = DocumentoFcPaciente::where('otro', DocumentoVeterinarioEmitido::INDICACION)->findOrFail($origen);
            return app(FirmaDocumentoVeterinarioController::class)->archivo($r, $documento, app(DocumentoVeterinarioEmitido::class));
        }
        if($tipo==='laboratorio') {
            $documento=\App\Models\DocumentoLaboratorioMascota::findOrFail($origen);
            abort_unless($archivo->puedeVerMascota((int)$documento->id_mascota),403);
            abort_unless($documento->mime==='application/pdf',422);
            $pdf=app(DocumentoLaboratorioMascotaController::class)->ver($documento);
            $doc=$archivo->registrar($r,response(file_get_contents($pdf->getFile()->getPathname()),200,['Content-Disposition'=>'inline; filename="'.basename($documento->nombre_original).'"']),['id_mascota'=>(int)$documento->id_mascota]);
            abort_unless($doc,422,'No fue posible preparar el PDF.');
            return redirect()->route('vet.pdf.detalle',['documento'=>$doc->id]);
        }
        abort_unless(isset($this->fuentes()[$tipo]),404);
        [$tabla,$campo]=$this->fuentes()[$tipo];
        $documento=DB::table($tabla)->find($origen);abort_unless($documento,404);
        $ficha=\App\Models\FichaAtencion::findOrFail($documento->{$campo});
        abort_unless($ficha->id_mascota && $archivo->puedeVerMascota((int)$ficha->id_mascota),403);
        $datos=['id_ficha_atencion'=>$ficha->id,'id_informe_medico'=>$tipo==='informe'?$origen:null,'id_tipo_informe'=>$documento->id_tipo_informe ?? 1,'id_interconsulta'=>$origen];
        // Los generadores antiguos conservan sus propias comprobaciones de identidad.
        $metodo=['informe'=>'pdf_informe_medico','reposo'=>'pdf_certificado_reposo','interconsulta'=>'pdf_interconsulta','uso'=>'pdf_uso_personal','orden'=>'pdf_orden_examenes'];
        $pdf=match($tipo) {
            'receta'=>app(RecomendacionController::class)->verPDF(new Request($datos)),
            'examen'=>ExamenEspecialidadController::generarPDF($origen),
            default=>app(ficha_atencionController::class)->{$metodo[$tipo]}(new Request($datos)),
        };
        $doc=$archivo->registrar($r,$pdf,$archivo->contexto(new Request(['id_ficha_atencion'=>$ficha->id])));
        abort_unless($doc,422,'No fue posible preparar el PDF.');
        return redirect()->route('vet.pdf.detalle',['documento'=>$doc->id]);
    }

    public function detalle(DocumentoPdfVeterinario $documento,ArchivoPdfVeterinario $archivo)
    {
        $archivo->autorizar($documento);
        $mascota=$documento->id_mascota?Mascota::findOrFail($documento->id_mascota):null;
        $tutor=$mascota?Paciente::find($mascota->id_responsable):null;
        return view('app.veterinaria.documentos.detalle',compact('documento','mascota','tutor'));
    }

    public function ver(DocumentoPdfVeterinario $documento,ArchivoPdfVeterinario $archivo)
    {
        $archivo->autorizar($documento);
        abort_unless(Storage::disk('local')->exists($documento->ruta),404);
        return Storage::disk('local')->response($documento->ruta,$documento->nombre,['Content-Type'=>'application/pdf','Cache-Control'=>'private, no-store']);
    }

    public function preparar(DocumentoPdfVeterinario $documento,ArchivoPdfVeterinario $archivo)
    {
        $archivo->autorizar($documento);
        abort_unless($documento->id_mascota,422,'Los reportes internos del centro no se comparten con tutores.');
        $mascota=Mascota::findOrFail($documento->id_mascota);
        $tutor=Paciente::findOrFail($mascota->id_responsable);
        $url=URL::temporarySignedRoute('vet.pdf.compartido',now()->addHours(48),['documento'=>$documento->id,'tutor'=>$tutor->id]);
        $telefono=preg_replace('/\D/','',(string)($tutor->telefono_uno ?: $tutor->telefono_dos));
        if(strlen($telefono)===9)$telefono='56'.$telefono;
        return response()->json(['url'=>$url,'whatsapp'=>'https://wa.me/'.$telefono.'?'.http_build_query(['text'=>'Documento de '.$mascota->nombre.': '.$documento->nombre.' '.$url]),'email'=>$tutor->email,'vence_en_horas'=>48]);
    }

    public function compartido(Request $r,DocumentoPdfVeterinario $documento)
    {
        abort_unless($r->hasValidSignature() && $documento->id_mascota,403);
        $mascota=Mascota::findOrFail($documento->id_mascota);
        abort_unless((int)$mascota->id_responsable===(int)$r->query('tutor'),403);
        abort_unless(Storage::disk('local')->exists($documento->ruta),404);
        return Storage::disk('local')->response($documento->ruta,$documento->nombre,['Content-Type'=>'application/pdf','Cache-Control'=>'private, no-store','Referrer-Policy'=>'no-referrer']);
    }

    public function enviar(DocumentoPdfVeterinario $documento,ArchivoPdfVeterinario $archivo)
    {
        $archivo->autorizar($documento);
        abort_unless($documento->id_mascota,422);
        $mascota=Mascota::findOrFail($documento->id_mascota);$tutor=Paciente::findOrFail($mascota->id_responsable);
        if(!filter_var($tutor->email,FILTER_VALIDATE_EMAIL))throw \Illuminate\Validation\ValidationException::withMessages(['email'=>'El tutor no tiene un correo válido registrado.']);
        abort_if(in_array(config('mail.default'),['log','array'],true) && !app()->environment('testing'),503,'El envío por correo no está configurado.');
        abort_unless(Storage::disk('local')->exists($documento->ruta),404);
        $correo=new CorreoGenerico(['asunto'=>'Documento veterinario de '.$mascota->nombre,'blade'=>'plantilla-correo-electronico','body'=>['titulo'=>'Documento veterinario','contenido'=>'Se adjunta '.$documento->nombre.' de su mascota '.$mascota->nombre.'.']]);
        $correo->attachData(Storage::disk('local')->get($documento->ruta),$documento->nombre,['mime'=>'application/pdf']);
        try{Mail::to($tutor->email)->send($correo);}catch(\Throwable $e){report($e);return back()->withErrors(['envio'=>'No se pudo enviar el correo. El PDF permanece guardado para reintentar.']);}
        return back()->with('success','PDF enviado al correo del tutor.');
    }
}
