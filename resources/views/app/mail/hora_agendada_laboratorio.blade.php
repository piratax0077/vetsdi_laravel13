<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Hora Agendada</title>
</head>

<body>
    <table border="0" width="100%" cellspacing="0" cellpadding="0">
        <tbody>
            <tr>
                <td style="background-color: #ffffff;" align="center" valign="top" bgcolor="#ffffff"><br>
                    <table style="width: 100%px; max-width: 600px;" border="0" width="100%" cellspacing="0" cellpadding="0">
                        <tbody>
                           <!-- <tr>
                                <td style="height: 11px; background-color: rgb(111,66,193); background: -moz-linear-gradient(81deg, rgba(111,66,193,1) 0%, rgba(49,190,190,1) 100%); background: -webkit-linear-gradient(81deg, rgba(111,66,193,1) 0%, rgba(49,190,190,1) 100%); background: linear-gradient(81deg, rgba(111,66,193,1) 0%, rgba(49,190,190,1) 100%);">
                                </td>
                            </tr>-->
                            <tr>
                                <td style="text-align: center;">
                                    <img style="width: 170px; margin-bottom: 20px; margin-top: 20px;" src="https://veterchile.cl/images/vet-color-h.svg" alt="Veterchile">
                                </td>
                            </tr>
                            <tr>
                                <td style="background-color: #fff; padding: 0px 24px 0px 24px;" align="center">
                                    <p style="font-family: Helvetica, Arial, sans-serif; font-size: 22px; font-weight: 600; color: #6f42c1;">Estimado/a Paciente: <br><br>{{ $detalle['body']['nombre_paciente'] }}</p>
                                </td>
                            </tr>
                            <tr>
                                <td style="background-color: rgb(111,66,193); background: -moz-linear-gradient(81deg, rgba(111,66,193,1) 0%, rgba(49,190,190,1) 100%); background: -webkit-linear-gradient(81deg, rgba(111,66,193,1) 0%, rgba(49,190,190,1) 100%); background: linear-gradient(81deg, rgba(111,66,193,1) 0%, rgba(49,190,190,1) 100%); padding: 0px 24px 0px 24px; border-radius:20px;" align="center">
                                    <p style="font-family: Helvetica, Arial, sans-serif; font-size: 20px; font-weight: 500; color: #ffffff;">Su hora ha sido agendada con éxito</p>
                                </td>
                            </tr>
                            <tr>
                                <td align="center" style="font-family: Helvetica, Arial, sans-serif; font-size: 1.2rem; color: #6f42c1; line-height: 10px;">
                                    <span style="display: inline-block; margin-top: 30px;">
                                        <img style="width: 2.5rem;" src="https://veterchile.cl/images/email/calendario_1.png" alt="Día">
                                        <p style="margin-top:5px"><b>{{ $detalle['body']['fecha'] }}</b></p>
                                    </span>
                                    <br>
                                    <span style="display: inline-block; margin-top: 30px;">
                                        <img style="width: 2.5rem;" src="https://veterchile.cl/images/email/reloj_1.png" alt="Hora">
                                        <p style="margin-top:5px"><b>{{ $detalle['body']['hora'] }}</b></p>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td height="20"> </td>
                            </tr>
                            <tr>
                            <td style="background-color: rgb(111,66,193); background: -moz-linear-gradient(81deg, rgba(111,66,193,1) 0%, rgba(49,190,190,1) 100%); background: -webkit-linear-gradient(81deg, rgba(111,66,193,1) 0%, rgba(49,190,190,1) 100%); background: linear-gradient(81deg, rgba(111,66,193,1) 0%, rgba(49,190,190,1) 100%); padding: 0px 24px 0px 24px; border-top-right-radius:20px; border-top-left-radius:20px; margin-top:7px;" align="center">
                                    <p style="font-family: Helvetica, Arial, sans-serif; font-size: 14px; font-weight: 600; color: #ffffff;">INFORMACIÓN SOBRE TU CITA</p>
                                </td>
                            </td>
                            </tr>
                            </tr>
                            <tr>
                                <td style="background-color: #f1eafa; padding: 0px 11px 0px 0px; border-bottom-right-radius:20px; border-bottom-left-radius:20px;" align="center">
                                    <p style="font-family: Helvetica, Arial, sans-serif; font-size: 14px; line-height: 25px; text-align: left; color: #424242; margin-left: 20px;">
                                        <b style="color:##6f42c1!important;">Procedimiento:</b> <br>{{ $detalle['body']['procedimiento'] }}<br>
                                        <b style="color:##6f42c1!important;">Lugar de Atención:</b><br> {{ $detalle['body']['lugar_atencion'] }}<br>
                                        <b style="color:##6f42c1!important;">Dirección:</b><br> {{ $detalle['body']['direccion'] }}
                                    </p>
                                </td>
                            </tr>
                            <tr>
                                <td height="20"> </td>
                            </tr>
                            <tr>
                                <td>
                                    <p style="font-family: Helvetica, Arial, sans-serif; font-size: 20px; line-height: 25px; text-align: center; color: #6f42c1; margin-top:7px;"><b>¡RECUERDE CONFIRMAR SU HORA PREVIAMENTE Y LLEGAR 15 MINUTOS ANTES A SU CITA!</b></p>
                                   <!-- <p style="font-family: Helvetica, Arial, sans-serif; font-size: 20px; line-height: 25px; text-align: center; color: #6f42c1;"><b>¡RECUERDE LLEGAR 15 MINUTOS ANTES A SU CITA!</b></p>-->
                                </td>
                            </tr>
                            <tr>
                                <td height="20"> </td>
                            </tr>
                           <!-- <tr>
                                <td inline-block style="text-align: center;">
                                    <img style="width: 130px; margin-bottom: 5px; margin-top: 50px;" src="https://veterchile.cl/images/vet-color-h.svg" alt="Veterchile">
                                </td>
                            </tr>-->
                            <tr>
                                <td style="height: 6px; background-color: rgb(111,66,193); background: -moz-linear-gradient(81deg, rgba(111,66,193,1) 0%, rgba(49,190,190,1) 100%); background: -webkit-linear-gradient(81deg, rgba(111,66,193,1) 0%, rgba(49,190,190,1) 100%); background: linear-gradient(81deg, rgba(111,66,193,1) 0%, rgba(49,190,190,1) 100%);">
                                </td>
                            </tr>
                            <tr>
                                <td style="text-align: center;" align="center">
                                    <table border="0" width="95%" cellspacing="0" cellpadding="0" align="center">
                                        <tbody>
                                            <tr>
                                                <td style="font-family: Helvetica, Arial, sans-serif;" align="center" valign="top">
                                                    <p style="text-align: center; color: #999999; font-size: 12px; font-weight: normal; line-height: 20px;">Este correo electrónico fue enviado por <a style="color: #000;" href="https://veterchile.cl/">Veterchile</a> <br> <b>Veterchile  &copy; <script>document.write(new Date().getFullYear())</script> </b></p>
                                                </td>
                                                <td width="30"> </td>
                                                <td width="16"> </td>
                                            </tr>
                                        </tbody>
                                    </table>
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
