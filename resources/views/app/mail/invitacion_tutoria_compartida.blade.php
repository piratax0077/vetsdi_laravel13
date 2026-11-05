{{-- Se envía con CorreoGenerico (blade 'invitacion_tutoria_compartida').
     Asunto: "{tutor} te invitó a ser {co-tutor|cuidador} de {mascota}"
     body: tutor, mascota, rol ('co_tutor' | 'cuidador'), enlace y dias (vigencia, 7 por defecto) --}}
@php
    $datos = $detalle['body'] ?? [];
    $tutor = $datos['tutor'] ?? '';
    $mascota = $datos['mascota'] ?? '';
    $esCuidador = ($datos['rol'] ?? 'co_tutor') === 'cuidador';
    $rolTexto = $esCuidador ? 'cuidador' : 'co-tutor';
    $permisos = $esCuidador
        ? 'ver la ficha de ' . $mascota . ', agendar sus citas y acompañarla al veterinario o de paseo'
        : 'ver y editar la ficha, las vacunas y las citas de ' . $mascota;
    $dias = $datos['dias'] ?? 7;
@endphp
<!DOCTYPE html>
<html lang="es">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>{{ $tutor }} te invitó a ser {{ $rolTexto }} de {{ $mascota }}</title>
</head>

<body>
    <table border="0" width="100%" cellspacing="0" cellpadding="0">
        <tbody>
            <tr>
                <td style="background-color: #ffffff;" align="center" valign="top" bgcolor="#ffffff"><br>
                    <table style="width: 100%; max-width: 600px;" border="0" width="100%" cellspacing="0" cellpadding="0">
                        <tbody>
                            <tr>
                                <td style="text-align: center;">
                                    <img style="width: 170px; margin-bottom: 20px; margin-top: 20px;" src="https://veterchile.cl/images/vet-color-h.svg" alt="{{ config('app.name') }}">
                                </td>
                            </tr>
                            <tr>
                                <td style="background-color: #fff; padding: 0 24px;" align="center">
                                    <p style="font-family: Helvetica, Arial, sans-serif; font-size: 24px; font-weight: 600; color: #6f42c1;">
                                        Hola,
                                    </p>
                                </td>
                            </tr>
                            <tr>
                                <td style="background-color: #fff; padding: 0 24px;" align="center">
                                    <p style="font-family: Helvetica, Arial, sans-serif; font-size: 17px; font-weight: 400; color: #272727; line-height: 1.5;">
                                        <strong>{{ $tutor }}</strong> te invitó a compartir el cuidado de <strong>{{ $mascota }}</strong> en {{ config('app.name') }}.
                                    </p>
                                    <p style="font-family: Helvetica, Arial, sans-serif; font-size: 16px; font-weight: 400; color: #4f5668; line-height: 1.5;">
                                        Como {{ $rolTexto }} podrás {{ $permisos }}.
                                    </p>
                                </td>
                            </tr>
                            <tr>
                                <td align="center">
                                    <table>
                                        <tbody>
                                            <tr>
                                                <td height="20"> </td>
                                            </tr>
                                            <tr>
                                                <td style="background: #6f42c1; padding: 15px 28px; border-radius: 30px; font-family: Helvetica, Arial, sans-serif;" align="center" bgcolor="#6f42c1">
                                                    <a target="_blank" rel="noopener noreferrer" href="{{ $datos['enlace'] ?? '#' }}" style="color: #ffffff; text-decoration: none; font-size: 18px;">Aceptar invitación</a>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td height="20"> </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                            <tr>
                                <td style="background-color: #fff; padding: 0 24px;" align="center">
                                    <p style="font-family: Helvetica, Arial, sans-serif; font-size: 14px; color: #6c757d; line-height: 1.5;">
                                        Esta invitación vence en {{ $dias }} días. Si el botón no funciona, copia y pega esta dirección en tu navegador:
                                    </p>
                                    <p style="font-family: Helvetica, Arial, sans-serif; font-size: 13px; color: #6f42c1; word-break: break-all;">
                                        {{ $datos['enlace'] ?? '' }}
                                    </p>
                                </td>
                            </tr>
                            <tr>
                                <td style="background-color: #fff; padding: 0 24px 24px;" align="center">
                                    <p style="font-family: Helvetica, Arial, sans-serif; font-size: 13px; color: #6c757d; line-height: 1.5;">
                                        Si no conoces a {{ $tutor }} o no esperabas este correo, puedes ignorarlo.
                                    </p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
        </tbody>
    </table>
</body>

</html>
