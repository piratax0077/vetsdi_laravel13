<?php
namespace App\Http\Middleware;
use App\Services\ArchivoPdfVeterinario;
use Closure;
use Illuminate\Http\Request;
class ArchivarPdfVeterinario
{
    public function handle(Request $request,Closure $next)
    {
        $response=$next($request);
        if(!$request->routeIs('vet.pdf.*') && $response instanceof \Symfony\Component\HttpFoundation\Response) {
            $archivo=app(ArchivoPdfVeterinario::class);
            $documento=$archivo->registrar($request,$response);
            if(!$documento && $response->getStatusCode()===200 && $response instanceof \Symfony\Component\HttpFoundation\BinaryFileResponse && str_contains((string)$response->headers->get('Content-Type'),'pdf')) {
                $documento=$archivo->registrar($request,response(file_get_contents($response->getFile()->getPathname()),200,$response->headers->all()));
            }
            if(!$documento && $response instanceof \Illuminate\Http\JsonResponse && $response->isSuccessful()) {
                $encontrados=[];
                $buscar=function($datos)use(&$buscar,$request,$archivo,&$encontrados){
                    if(!is_array($datos))return;
                    foreach($datos as $clave=>$valor) {
                        if(is_array($valor)){$buscar($valor);continue;}
                        if(!in_array($clave,['ruta','pdf_url','ruta_publica','url_pdf'],true) || !is_string($valor))continue;
                        $partes=parse_url($valor);if(!$partes)continue;
                        if(isset($partes['host']) && !in_array($partes['host'],[$request->getHost(),parse_url(config('app.url'),PHP_URL_HOST)],true))continue;
                        $path=rawurldecode($partes['path'] ?? '');
                        if(!preg_match('#^/(storage/pdf|reportes)/[^\x00]+\.pdf$#i',$path) || str_contains($path,'..'))continue;
                        $ruta=realpath(public_path(ltrim($path,'/')));if(!$ruta || filesize($ruta)>25*1024*1024)continue;
                        $raiz=realpath(public_path(str_starts_with($path,'/storage/pdf/')?'storage/pdf':'reportes'));
                        if(!$raiz || !str_starts_with($ruta,$raiz.DIRECTORY_SEPARATOR))continue;
                        $doc=$archivo->registrar($request,response(file_get_contents($ruta),200,['Content-Type'=>'application/pdf','Content-Disposition'=>'inline; filename="'.basename($ruta).'"']));
                        if($doc)$encontrados[$doc->id]=$doc;
                    }
                };
                $datos=$response->getData(true);
                $buscar($datos);
                $documento=reset($encontrados) ?: null;
                if($documento && is_array($datos) && !array_is_list($datos))$response->setData($datos+['archivo_pdf_url'=>route('vet.pdf.detalle',['documento'=>$documento->id])]);
            }
            if($documento)$response->headers->set('X-Documento-Veterinario',$documento->id);
        }
        return $response;
    }
}
