<!DOCTYPE html>
<html lang="es">

    <head>
        <meta charset="UTF-8">
        <title>{{ $titulo }}</title>
        <link rel="stylesheet" href="{{ asset('css/pdf.css') }}">
    </head>
    <div class="texto-vertical-2">Este documento lo puedes validar en www.med-sdi.cl - Cód. Indetificador {{ $cuerpo['array_ficha_atencion']['token'] }}</div>

    @include('PDF.header')
    @include('PDF.footer')

    <main>
        {!! $cuerpo['texto_consentimiento'] !!}

        @if(!empty($cuerpo['firma_tutor']))
            <table style="width:100%; margin-top:28px; border-collapse:collapse; page-break-inside:avoid;">
                <tr>
                    <td style="width:72%; padding:14px; border:1px solid #168c83; vertical-align:top;">
                        <strong style="color:#168c83;">Firmado y autorizado electrónicamente por el tutor</strong><br><br>
                        <strong>Nombre:</strong> {{ $cuerpo['firma_tutor']['nombre'] }}<br>
                        <strong>RUT:</strong> {{ $cuerpo['firma_tutor']['rut'] ?: 'Sin registro' }}<br>
                        <strong>Fecha:</strong> {{ $cuerpo['firma_tutor']['fecha'] }}<br>
                        <small>La firma y autenticidad de este consentimiento pueden validarse mediante el código QR.</small>
                    </td>
                    <td style="width:28%; padding:10px; border:1px solid #168c83; text-align:center;">
                        @if(!empty($cuerpo['firma_tutor']['qr']))
                            <img src="data:image/svg+xml;base64,{{ base64_encode($cuerpo['firma_tutor']['qr']) }}" style="width:105px; height:105px;" alt="QR de validación">
                        @endif
                    </td>
                </tr>
            </table>
        @else
            <div style="margin-top:28px; padding:12px; color:#7a5b00; background:#fff8dc; border:1px solid #e3cc75;">
                <strong>Pendiente de firma y autorización del tutor.</strong>
                Este documento se encuentra disponible en el escritorio del tutor.
            </div>
        @endif
    </main>
</html>

