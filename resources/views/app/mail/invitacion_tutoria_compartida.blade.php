{{-- Se envía con CorreoGenerico (blade 'invitacion_tutoria_compartida').
     Asunto: "{tutor} te invitó a ser {co-tutor|cuidador} de {mascota}"
     body: tutor, mascota, rol ('co_tutor' | 'cuidador'), enlace, dias (vigencia, 7 por defecto)
     y mascota_foto (opcional: URL pública en JPG o PNG; si no viene, no se muestra el círculo) --}}
@php
    $datos = $detalle['body'] ?? [];
    $tutor = $datos['tutor'] ?? '';
    $mascota = $datos['mascota'] ?? '';
    $esCuidador = ($datos['rol'] ?? 'co_tutor') === 'cuidador';
    $rolTexto = $esCuidador ? 'cuidador' : 'co-tutor';
    $rolEtiqueta = $esCuidador ? 'Cuidador' : 'Co-tutor';
    $permisos = $esCuidador
        ? ['Ver la ficha de ' . $mascota, 'Agendar y ver sus citas', 'Llevarla al veterinario o de paseo']
        : ['Ver y editar la ficha de ' . $mascota, 'Revisar y registrar sus vacunas', 'Agendar y ver sus citas'];
    $enlace = $datos['enlace'] ?? '#';
    $dias = $datos['dias'] ?? 7;
    $foto = $datos['mascota_foto'] ?? '';
    $logo = 'https://veterchile.cl/images/logo_pais_vertical.png';
    $fuente = "'Segoe UI', Helvetica, Arial, sans-serif";
@endphp
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office" lang="es">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="x-apple-disable-message-reformatting" />
    <meta name="format-detection" content="telephone=no, date=no, address=no, email=no" />
    <meta name="color-scheme" content="light" />
    <meta name="supported-color-schemes" content="light" />
    <title>{{ $tutor }} te invitó a ser {{ $rolTexto }} de {{ $mascota }}</title>
    <!--[if mso]>
    <noscript>
        <xml>
            <o:OfficeDocumentSettings>
                <o:PixelsPerInch>96</o:PixelsPerInch>
            </o:OfficeDocumentSettings>
        </xml>
    </noscript>
    <style>
        table {border-collapse:collapse;}
        td, p, a, span {font-family:Arial, sans-serif !important;}
    </style>
    <![endif]-->
    <style>
        body, table, td, a { -webkit-text-size-adjust:100%; -ms-text-size-adjust:100%; }
        table, td { mso-table-lspace:0pt; mso-table-rspace:0pt; }
        img { -ms-interpolation-mode:bicubic; border:0; line-height:100%; outline:none; text-decoration:none; }
        body { margin:0 !important; padding:0 !important; width:100% !important; height:100% !important; }
        td { word-break:break-word; }

        a[x-apple-data-detectors] {
            color:inherit !important; text-decoration:none !important; font-size:inherit !important;
            font-family:inherit !important; font-weight:inherit !important; line-height:inherit !important;
        }
        u + #body a { color:inherit; text-decoration:none; font-size:inherit; font-family:inherit; font-weight:inherit; line-height:inherit; }

        @media screen and (max-width: 600px) {
            .contenedor-correo { width:100% !important; max-width:100% !important; border-radius:0 !important; }
            .fondo-pad { padding:0 !important; }
            .px-pad { padding-left:18px !important; padding-right:18px !important; }
            .titulo { font-size:22px !important; line-height:29px !important; }
            .tarjeta-pad { padding:24px 20px !important; }
            .tarjeta-texto { font-size:17px !important; line-height:25px !important; }
            .boton-celda { width:100% !important; }
            .boton-enlace { display:block !important; }
        }
    </style>
</head>

<body id="body" style="margin:0; padding:0; background-color:#f4f1fa;">

    {{-- Texto que se ve en la bandeja antes de abrir el correo --}}
    <div style="display:none; max-height:0; overflow:hidden; mso-hide:all; font-size:1px; line-height:1px; color:#f4f1fa; opacity:0;">
        {{ $tutor }} quiere compartir contigo el cuidado de {{ $mascota }} en Veterchile.
        &nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;
    </div>

    <table width="100%" cellspacing="0" cellpadding="0" border="0" role="presentation" bgcolor="#f4f1fa" style="background-color:#f4f1fa;">
        <tr>
            <td class="fondo-pad" align="center" style="padding:24px 15px;">

                <!--[if mso]>
                <table width="620" cellspacing="0" cellpadding="0" border="0" align="center" role="presentation"><tr><td>
                <![endif]-->

                <table width="100%" cellspacing="0" cellpadding="0" border="0" role="presentation" class="contenedor-correo" bgcolor="#ffffff"
                    style="width:100%; max-width:620px; border-radius:28px; border:1px solid #e5ddf5; background-color:#ffffff; font-family:{{ $fuente }};">

                    {{-- Logo --}}
                    <tr>
                        <td class="px-pad" align="center" style="padding:24px 32px 0 32px;">
                            <img src="{{ $logo }}" width="130" height="65" alt="Veterchile"
                                style="width:130px; max-width:130px; height:auto; display:block; margin:0 auto;">
                        </td>
                    </tr>

                    {{-- Título --}}
                    <tr>
                        <td class="px-pad" align="center" style="padding:6px 32px 18px 32px;">
                            <p class="titulo" style="margin:0; font-family:{{ $fuente }}; font-size:27px; line-height:34px; font-weight:700; color:#6f42c1;">
                                Te invitaron a cuidar de {{ $mascota }}
                            </p>
                        </td>
                    </tr>

                    {{-- Tarjeta principal --}}
                    <tr>
                        <td class="px-pad" style="padding:0 28px;">
                            <table width="100%" cellspacing="0" cellpadding="0" border="0" role="presentation" bgcolor="#6f42c1"
                                style="border-radius:24px; background-color:#6f42c1;">
                                <tr>
                                    <td class="tarjeta-pad" align="center" style="padding:30px 32px;">

                                        @if($foto)
                                            <table border="0" cellspacing="0" cellpadding="0" role="presentation" align="center" style="margin:0 auto 12px auto;">
                                                <tr>
                                                    <td width="60" height="60" align="center" valign="middle" bgcolor="#ffffff"
                                                        style="width:60px; height:60px; background-color:#ffffff; border-radius:30px;">
                                                        <img src="{{ $foto }}" width="60" height="60" alt="{{ $mascota }}"
                                                            style="width:60px; height:60px; border-radius:30px; display:block; margin:0 auto; object-fit:cover;">
                                                    </td>
                                                </tr>
                                            </table>
                                        @endif

                                        <p class="tarjeta-texto" style="margin:0 0 14px 0; font-family:{{ $fuente }}; font-size:18px; line-height:26px; font-weight:400; color:#ffffff;">
                                            Hola, <strong style="font-weight:700; color:#ffffff;">{{ $tutor }}</strong> te invitó a compartir el cuidado de <strong style="font-weight:700; color:#ffffff;">{{ $mascota }}</strong>
                                        </p>

                                        {{-- Rol --}}
                                        <table border="0" cellspacing="0" cellpadding="0" role="presentation" align="center" style="margin:0 auto;">
                                            <tr>
                                                <td bgcolor="#31bebe" style="padding:5px 16px; background-color:#31bebe; border-radius:999px; font-family:{{ $fuente }}; font-size:13px; line-height:18px; font-weight:700; color:#ffffff; letter-spacing:.4px;">
                                                    {{ $rolEtiqueta }}
                                                </td>
                                            </tr>
                                        </table>

                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Permisos --}}
                    <tr>
                        <td class="px-pad" style="padding:22px 28px 0 28px;">
                            <table width="100%" cellspacing="0" cellpadding="0" border="0" role="presentation" bgcolor="#f9f7fd"
                                style="border-radius:18px; background-color:#f9f7fd;">
                                <tr>
                                    <td style="padding:18px 22px;">
                                        <p style="margin:0 0 10px 0; font-family:{{ $fuente }}; font-size:15px; line-height:22px; font-weight:700; color:#272727;">
                                            Como {{ $rolTexto }} podrás:
                                        </p>
                                        <table cellspacing="0" cellpadding="0" border="0" role="presentation">
                                            @foreach($permisos as $permiso)
                                                <tr>
                                                    <td valign="top" style="padding:3px 10px 3px 0; font-family:{{ $fuente }}; font-size:15px; line-height:22px; font-weight:700; color:#31bebe;">&#10003;</td>
                                                    <td style="padding:3px 0; font-family:{{ $fuente }}; font-size:15px; line-height:22px; color:#4a3f63;">{{ $permiso }}</td>
                                                </tr>
                                            @endforeach
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Botón (Outlook de escritorio usa la versión VML) --}}
                    <tr>
                        <td class="px-pad" align="center" style="padding:26px 32px 8px 32px;">
                            <!--[if mso]>
                            <v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="{{ $enlace }}"
                                style="height:50px; v-text-anchor:middle; width:260px;" arcsize="50%" stroke="f" fillcolor="#6f42c1">
                                <w:anchorlock/>
                                <center style="color:#ffffff; font-family:Arial, sans-serif; font-size:17px; font-weight:bold;">Aceptar invitación</center>
                            </v:roundrect>
                            <![endif]-->
                            <!--[if !mso]><!-->
                            <table border="0" cellspacing="0" cellpadding="0" role="presentation" align="center" style="margin:0 auto;">
                                <tr>
                                    <td class="boton-celda" align="center" bgcolor="#6f42c1" style="border-radius:30px; background-color:#6f42c1;">
                                        <a class="boton-enlace" href="{{ $enlace }}" target="_blank" rel="noopener noreferrer"
                                            style="display:inline-block; padding:15px 34px; font-family:{{ $fuente }}; font-size:17px; line-height:20px; font-weight:700; color:#ffffff; text-decoration:none; border-radius:30px;">
                                            Aceptar invitación
                                        </a>
                                    </td>
                                </tr>
                            </table>
                            <!--<![endif]-->
                        </td>
                    </tr>

                    {{-- Vigencia y enlace --}}
                    <tr>
                        <td class="px-pad" align="center" style="padding:14px 32px 0 32px;">
                            <p style="margin:0 0 8px 0; font-family:{{ $fuente }}; font-size:14px; line-height:21px; color:#64748b;">
                                Esta invitación vence en <strong style="color:#6f42c1;">{{ $dias }} {{ $dias == 1 ? 'día' : 'días' }}</strong>.
                            </p>
                            <p style="margin:0 0 6px 0; font-family:{{ $fuente }}; font-size:13px; line-height:19px; color:#64748b;">
                                Si el botón no funciona, copia y pega esta dirección en tu navegador:
                            </p>
                            <p style="margin:0; font-family:{{ $fuente }}; font-size:13px; line-height:19px; color:#6f42c1; word-break:break-all;">
                                <a href="{{ $enlace }}" target="_blank" rel="noopener noreferrer" style="color:#6f42c1; text-decoration:underline;">{{ $enlace }}</a>
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td class="px-pad" align="center" style="padding:18px 32px 26px 32px;">
                            <p style="margin:0; font-family:{{ $fuente }}; font-size:13px; line-height:19px; color:#94a3b8;">
                                Si no conoces a {{ $tutor }} o no esperabas este correo, puedes ignorarlo.
                            </p>
                        </td>
                    </tr>

                    {{-- Pie --}}
                    <tr>
                        <td align="center" bgcolor="#f9f7fd" style="padding:16px; background-color:#f9f7fd; border-radius:0 0 28px 28px;">
                            <p style="margin:0; font-family:{{ $fuente }}; font-size:11px; line-height:16px; color:#64748b;">
                                Este correo fue enviado por <strong style="color:#6f42c1;">Veterchile</strong><br>
                                &copy; {{ date('Y') }} &middot; Todos los derechos reservados
                            </p>
                        </td>
                    </tr>

                </table>

                <!--[if mso]>
                </td></tr></table>
                <![endif]-->

            </td>
        </tr>
    </table>

</body>

</html>
