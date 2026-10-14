<?php

namespace App\Http\Controllers;

use App\Mail\CorreoGenerico;
use App\Models\{CertificadoReposo, FichaAtencion, InformeMedico, Interconsulta, Paciente, Profesional, UsoPersonal};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, Mail};
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class DocumentoVeterinarioCorreoController extends Controller
{
    public function reposo(Request $request) { return $this->enviar($request, 'reposo'); }
    public function informe(Request $request) { return $this->enviar($request, 'informe'); }
    public function interconsulta(Request $request) { return $this->enviar($request, 'interconsulta'); }
    public function usoPersonal(Request $request) { return $this->enviar($request, 'uso'); }

    private function enviar(Request $request, string $tipo)
    {
        abort_unless(Auth::check(), 401);
        $profesional = Profesional::where('id_usuario', Auth::id())->firstOrFail();
        $fichaCampo = 'id_ficha_atencion';
        if ($tipo === 'interconsulta') {
            $request->validate(['id_interconsulta' => 'required|integer|min:1']);
            $documento = Interconsulta::findOrFail($request->id_interconsulta);
            $fichaCampo = 'id_ficha_atencion_soli';
            $metodo = 'pdf_interconsulta';
        } elseif ($tipo === 'uso') {
            $request->validate(['id_uso_personal' => 'required|integer|min:1']);
            $documento = UsoPersonal::findOrFail($request->id_uso_personal);
            $metodo = 'pdf_uso_personal';
        } elseif ($tipo === 'informe' && $request->filled('id_informe_medico')) {
            $request->validate(['id_informe_medico' => 'required|integer|min:1']);
            $documento = InformeMedico::findOrFail($request->id_informe_medico);
            $metodo = 'pdf_informe_medico';
        } else {
            $request->validate(['id_ficha_atencion' => 'required|integer|min:1', 'tipo_informe' => 'nullable|integer|min:1']);
            $modelo = $tipo === 'reposo' ? CertificadoReposo::class : InformeMedico::class;
            $consulta = $modelo::where('id_ficha_atencion', $request->id_ficha_atencion);
            if ($tipo === 'informe') $consulta->where('id_tipo_informe', $request->input('tipo_informe', 1));
            $documento = $consulta->firstOrFail();
            $metodo = $tipo === 'reposo' ? 'pdf_certificado_reposo' : 'pdf_informe_medico';
        }
        $ficha = FichaAtencion::findOrFail($documento->{$fichaCampo});
        abort_unless((int) $ficha->id_profesional === (int) $profesional->id && $ficha->id_mascota, 403);
        $tutor = Paciente::findOrFail($ficha->id_paciente);
        if (!filter_var($tutor->email, FILTER_VALIDATE_EMAIL)) {
            throw ValidationException::withMessages(['email' => 'El tutor no tiene un correo válido registrado.']);
        }
        $pdfRequest = Request::create('/', 'GET', [
            'id_ficha_atencion' => $ficha->id,
            'id_interconsulta' => $documento->id,
            'id_tipo_informe' => $documento->id_tipo_informe ?? 1,
        ]);
        try {
            $pdf = app(ficha_atencionController::class)->{$metodo}($pdfRequest);
            if (!$pdf instanceof Response || $pdf->getStatusCode() !== 200 || !str_starts_with((string) $pdf->getContent(), '%PDF-')) {
                throw new \RuntimeException('No se generó un PDF válido.');
            }
            $correo = new CorreoGenerico([
                'asunto' => 'Documento de atención veterinaria',
                'blade' => 'plantilla-correo-electronico',
                'body' => ['titulo' => 'Atención veterinaria', 'contenido' => 'Se adjunta el documento emitido por su profesional veterinario.'],
            ]);
            $correo->attachData($pdf->getContent(), 'documento-veterinario.pdf', ['mime' => 'application/pdf']);
            Mail::to($tutor->email)->send($correo);
            return response()->json(['estado' => 1, 'msj' => 'Documento enviado al correo del tutor.']);
        } catch (\Throwable $e) {
            report($e);
            return response()->json(['estado' => 0, 'msj' => 'No se pudo generar o enviar el documento. Inténtelo nuevamente.'], 500);
        }
    }
}
