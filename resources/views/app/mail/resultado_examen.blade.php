<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Exámenes</title>
</head>

<body style="background-color: #f2f2f2; margin-top: 0px;">
    <table border="0" width="100%" cellspacing="0" cellpadding="0" bgcolor="#F0F0F0">
        <tbody>
            <tr>
                <td style="background-color: #f2f2f2;" align="center" valign="top">
                    <table style="width: 100%; max-width: 600px;" border="0" width="100%" cellspacing="0" cellpadding="0">
                        <tr style="background-color: #fff;">
                            <td style="text-align: center;">
                                <img style=" margin-bottom: 5px; margin-top: 0px; max-width: 100%;" src="https://veterchile.cl/images/email/medichile-email.png" alt="Veterchile">
                            </td>
                        </tr>
                        <tr>
                            <td style="background-color: #fff; padding-top: 15px;" align="center">
                                <p style="font-family: Helvetica, Arial, sans-serif; font-size: 20px; line-height: 20px; color: #6f42c1; text-align: center; font-weight:600;">
                                    Estimado/a paciente: {{ $detalle['body']['nombre_cliente'] }}
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <td style="background-color: #fff; padding-right: 20px; padding-left: 20px;  text-align: justify;">
                                <p style="font-family: Helvetica, Arial, sans-serif; font-size: 16px; line-height: 26px; color: #424242;">
                                    {!! $detalle['body']['mensaje'] !!}
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <td align="center" style="background-color: #fff; padding-bottom: 20px;">
                                <table style="margin-top:40px;">
                                    <tbody>
                                        <tr>
                                            <td style="background-color: rgb(111,66,193); background: -moz-linear-gradient(81deg, rgba(111,66,193,1) 0%, rgba(49,190,190,1) 100%); background: -webkit-linear-gradient(81deg, rgba(111,66,193,1) 0%, rgba(49,190,190,1) 100%); background: linear-gradient(81deg, rgba(111,66,193,1) 0%, rgba(49,190,190,1) 100%); padding: 20px 20px; -webkit-border-radius: 30px;font-family: Helvetica, Arial, sans-serif;" align="center" bgcolor="#6f42c1">
                                                <a target="_blank" href="{{ $detalle['body']['url_documento'] }}" style="color: #ffffff; text-decoration: none; font-size: 18px; font-weight: 600;">DESCARGAR EXAMEN</a>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <td style="margin-top:50px; background-color: #fff;">
                                <img style=" margin-bottom: 5px; margin-top: 50px; max-width: 100%;" src="https://veterchile.cl/images/email/pie-email.png" alt="Veterchile">
                            </td>
                        </tr>
                        <tr>
                            <td style="text-align: center; background-color: #fff;" align="center">
                                <table border="0" width="95%" cellspacing="0" cellpadding="0" align="center">
                                    <tbody>
                                        <tr>
                                            <td style="font-family: Helvetica, Arial, sans-serif;" align="center"
                                                valign="top">
                                                <p style="text-align: center; color: #999999; font-size: 10px; font-weight: normal; line-height: 15px;">
                                                    Este correo electrónico fue enviado por  <a style="color: #000;" href="https://veterchile.cl/">Veterchile</a> <br>
                                                    Veterchile <b>Todos los Derechos Reservados. ©2023</b>
                                                </p>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </tbody>
    </table>
</body>

</html>
