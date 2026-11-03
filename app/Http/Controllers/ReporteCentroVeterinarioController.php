<?php

namespace App\Http\Controllers;

use App\Services\ReporteCentroVeterinario;
use Illuminate\Http\Request;

class ReporteCentroVeterinarioController extends Controller
{
    public function index(Request $request,ReporteCentroVeterinario $reporte)
    {
        $datos=$reporte->datos($request);
        if($request->input('formato')==='pdf') {
            $request->attributes->set('archivo_pdf_contexto',['id_institucion'=>$datos['institucion']->id]);
            $pdf=\Barryvdh\DomPDF\Facade\Pdf::loadView('app.veterinaria.documentos.reporte_centro_pdf',$datos)->setPaper('a4')->stream('reporte-centro-'.$datos['institucion']->id.'.pdf');
            app(\App\Services\ArchivoPdfVeterinario::class)->registrar($request,$pdf);
            return $pdf;
        }
        return $request->input('formato')==='csv' ? $reporte->csv($datos) : view('app.adm_cm.reporte_estadisticas',$datos);
    }
}
