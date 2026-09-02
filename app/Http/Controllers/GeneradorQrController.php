<?php

namespace App\Http\Controllers;

use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Profesional;
use App\Models\FichaAtencion;
use App\Models\LugarAtencion;

class GeneradorQrController extends Controller
{
    public static function generar($valor = ''): string
    {
        if (empty($valor)) {
            $valor = config('app.url') . '?tkx=' . random_int(1111, 9999);
        }

        // El sistema usa este SVG dentro de recetas y documentos firmados.
        // BaconQrCode ya es una dependencia del proyecto y es compatible con PHP 8.4.
        $renderer = new ImageRenderer(
            new RendererStyle(150, 1),
            new SvgImageBackEnd()
        );

        return (new Writer($renderer))->writeString((string) $valor);
    }

    public function firmaEdoVeterinaria(Request $request)
    {
        $request->validate([
            'id_ficha_atencion' => ['nullable', 'integer', 'min:0'],
            'id_lugar_atencion' => ['nullable', 'integer', 'min:0'],
        ]);

        $profesional = Profesional::where('id_usuario', Auth::id())->first();
        if (!$profesional) {
            return response()->json(['estado' => 0, 'mensaje' => 'El usuario conectado no tiene un perfil veterinario.'], 422);
        }

        $idFicha = (int) $request->id_ficha_atencion;
        $ficha = $idFicha > 0 ? FichaAtencion::find($idFicha) : null;
        $idLugar = (int) ($ficha->id_lugar_atencion ?? $request->id_lugar_atencion);
        $lugar = $idLugar > 0 ? LugarAtencion::find($idLugar) : null;
        $direccion = $lugar ? $lugar->Direccion()->first() : null;
        $comuna = $direccion ? $direccion->Ciudad()->first() : null;
        $region = $comuna ? $comuna->Region()->first() : null;
        $direccionTexto = $direccion
            ? trim(($direccion->direccion ?? '').' '.($direccion->numero_dir ?? ''))
            : '';
        $datosFormulario = [
            'profesional' => trim($profesional->nombre.' '.$profesional->apellido_uno.' '.$profesional->apellido_dos),
            'rut' => $profesional->rut,
            'region' => $region->nombre ?? '',
            'comuna' => $comuna->nombre ?? '',
            'direccion' => $direccionTexto,
            'establecimiento' => $lugar->nombre ?? '',
        ];

        if ($idFicha < 1) {
            return response()->json(array_merge($datosFormulario, [
                'estado' => 0,
                'qr' => '',
                'token' => '',
                'mensaje' => 'Se cargaron los datos del profesional y del centro, pero la ficha aún no está disponible para firmar.',
            ]));
        }

        $certificado = CertificadoController::certificadoProfesional(
            $profesional->id,
            1,
            1,
            $idFicha
        );

        if (($certificado['estado'] ?? 0) !== 1 || empty($certificado['certificado'])) {
            return response()->json(array_merge($datosFormulario, [
                'estado' => 0,
                'qr' => '',
                'token' => '',
                'mensaje' => 'Se cargaron los datos, pero no fue posible emitir la firma digital SDI.',
            ]));
        }

        $url = CertificadoController::generarUrlProfesional($certificado['certificado']);

        return response()->json(array_merge($datosFormulario, [
            'estado' => 1,
            'qr' => self::generar($url),
            'token' => $certificado['certificado'],
            'url' => $url,
        ]));
    }
}
