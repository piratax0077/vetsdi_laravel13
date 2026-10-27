<div class="modal fade" id="peluqueria-estado-modal" tabindex="-1" role="dialog" aria-labelledby="peluqueria-estado-titulo" aria-hidden="true">
<div class="modal-dialog modal-dialog-centered modal-lg" role="document"><div class="modal-content">
<div class="modal-header pt-3 pb-3 modal-header-purple"><h6 class="text-white f-16 mb-0 mt-0" id="peluqueria-estado-titulo">Confirmar información</h6></div>
<form id="peluqueria-estado-form" method="post">@csrf
<div class="modal-body">
<div id="peluqueria-estado-error" class="alert alert-danger" role="alert" hidden></div>
<p id="peluqueria-modal-instruccion" class="mb-3" role="status"></p>
<div class="row reserva-hora-datos">
<div class="col-12 mb-3">
<div class="reserva-hora-cabecera"><h6 class="titulo-item"><i class="feather icon-user mr-2"></i>Información del tutor</h6></div>
<dl class="ficha-previa-datos reserva-hora-filas">
<div class="ficha-previa-dato reserva-hora-dato-mitad reserva-hora-dato-angosto reserva-hora-dato-fijo"><dt>RUT</dt><dd data-reserva-dato="rut"></dd></div>
<div class="ficha-previa-dato reserva-hora-dato-mitad reserva-hora-dato-amplio"><dt>Nombre</dt><dd data-reserva-dato="tutor"></dd></div>
<div class="ficha-previa-dato reserva-hora-dato-mitad"><dt>Correo electrónico</dt><dd data-reserva-dato="email"></dd></div>
<div class="ficha-previa-dato reserva-hora-dato-mitad"><dt>Teléfono</dt><dd data-reserva-dato="telefono"></dd></div>
</dl></div>
<div class="col-12 mb-2">
<div class="reserva-hora-cabecera"><h6 class="titulo-item"><i class="fas fa-paw mr-2"></i>Información de la mascota</h6></div>
<dl class="ficha-previa-datos reserva-hora-dos-columnas">
@foreach(['mascota'=>'Nombre','especie'=>'Especie','servicio'=>'Procedimiento','inicio'=>'Fecha y hora'] as $campo=>$label)
<div class="ficha-previa-dato"><dt>{{ $label }}</dt><dd data-reserva-dato="{{ $campo }}"></dd></div>
@endforeach
</dl></div></div>
<div id="peluqueria-resumen-costos" class="border rounded p-3 mb-3"><div id="peluqueria-detalle-costos" class="small mb-2"></div><strong>Total a pagar <span class="float-right text-info" id="peluqueria-total-reserva"></span></strong></div>
<fieldset id="peluqueria-confirmacion" hidden disabled><div class="form-group"><label class="floating-label-activo-sm" for="peluqueria-via-confirmacion">Vía de confirmación</label><select id="peluqueria-via-confirmacion" name="via_confirmacion" class="form-control form-control-sm" required><option value="">Seleccione</option>@foreach(['Presencial','Teléfono','WhatsApp','Correo electrónico'] as $via)<option @selected($via==='Presencial')>{{ $via }}</option>@endforeach</select></div></fieldset>
<fieldset id="peluqueria-pago" hidden disabled><h6>Recepción de pago de atención</h6><p>Registra el pago recibido por esta reserva.</p><div class="form-row">
<div class="col-md-6 form-group"><label for="peluqueria-medio-pago">Medio de pago</label><select id="peluqueria-medio-pago" name="medio_pago" class="form-control form-control-sm" required><option value="">Seleccione</option>@foreach(['Efectivo','Transferencia','Tarjeta de débito','Tarjeta de crédito'] as $medio)<option @selected($medio==='Efectivo')>{{ $medio }}</option>@endforeach</select></div>
<div class="col-md-6 form-group"><label for="peluqueria-monto">Monto ($ CLP)</label><input id="peluqueria-monto" name="monto_pagado" class="form-control form-control-sm" type="number" readonly required></div>
</div><div class="form-group"><label for="peluqueria-referencia">Referencia o comprobante (opcional)</label><input id="peluqueria-referencia" name="referencia_pago" class="form-control form-control-sm" maxlength="100"></div></fieldset>
<p id="peluqueria-pago-registrado" class="text-success" hidden></p>
</div>
<div class="modal-footer">
@foreach(['cancelada'=>'Anular hora','confirmada'=>'Confirmar hora','pagada'=>'Recepcionar pago','en_espera'=>'Esperando','en_atencion'=>'Atender'] as $estado=>$label)
<button type="submit" name="estado" value="{{ $estado }}" @if($estado==='cancelada') formnovalidate @endif data-reserva-accion="{{ $estado }}" class="btn {{ $estado==='cancelada'?'btn-danger':($estado==='confirmada'?'btn-success':'btn-info') }}" hidden><i class="feather {{ $estado==='cancelada'?'icon-x':'icon-check' }} mr-1" aria-hidden="true"></i>{{ $label }}</button>
@endforeach
<a id="peluqueria-ver-ficha" class="btn btn-info" hidden>Ver atención</a><button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="feather icon-x mr-1" aria-hidden="true"></i>Cerrar</button>
</div></form></div></div></div>
