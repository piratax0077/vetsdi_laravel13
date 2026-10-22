<?php

namespace App\Http\Controllers;

use App\Models\FichaAtencion;
use App\Services\EnviarDocumentoMascota;
use Illuminate\Http\Request;

class DocumentoEmitidoMascotaController extends Controller
{
    public function ver(Request $request, string $tipo)
    {
        abort_unless(in_array($tipo, ['receta', 'examenes'], true), 404);
        $ficha = $this->ficha($request, $tipo);
        return app(EnviarDocumentoMascota::class)->pdf($ficha, $tipo);
    }

    public function enviar(Request $request, string $tipo)
    {
        abort_unless(in_array($tipo, ['receta', 'examenes'], true), 404);
        return response()->json(app(EnviarDocumentoMascota::class)->enviar($this->ficha($request, $tipo), $tipo));
    }

    private function ficha(Request $request, string $tipo): FichaAtencion
    {
        $ficha = FichaAtencion::findOrFail($request->id_ficha_atencion);
        abort_unless($tipo === 'receta' ? $ficha->RecetasEmitidas()->exists() : $ficha->OrdenesExamenes()->exists(), 422, 'Primero debe emitir el documento.');
        return $ficha;
    }
}
