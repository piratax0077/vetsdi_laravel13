<div class="modal fade" id="peluqueria-reserva-modal" tabindex="-1" role="dialog" aria-labelledby="peluqueria-reserva-titulo" aria-hidden="true"><div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" role="document"><div class="modal-content">
<div class="modal-header pt-3 pb-2 modal-header-purple"><h5 class="modal-title text-white" id="peluqueria-reserva-titulo"><i class="feather icon-calendar mr-2" aria-hidden="true"></i>Agendar hora</h5><button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">×</button></div>
<div class="modal-body"><div id="peluqueria-reserva-error" class="alert alert-danger" role="alert" hidden></div>
@if($procedimientos->isEmpty())
<p>Primero configura los procedimientos, su duración y precio.</p><a class="btn btn-info" href="{{ route(($peluqueriaRutaBase??'peluqueria').'.configuracion') }}">Configurar procedimientos</a>
@else
<form id="peluqueria-reserva-form" action="{{ route(($peluqueriaRutaBase??'peluqueria').'.agenda') }}" method="post">@csrf
<input type="hidden" name="sucursal_id" value="{{ $sucursal->id??'' }}">
<input type="hidden" name="agenda_personal" value="{{ $agendaPersonal }}">
<div class="form-row mb-3" id="reserva-buscador-tutor">
<div class="col-md-9"><div class="form-group"><label class="floating-label-activo-sm" for="reserva-rut">RUT del tutor</label><input id="reserva-rut" name="rut" class="form-control form-control-sm" maxlength="12" placeholder="12.345.678-5" required data-url="{{ route(($peluqueriaRutaBase??'peluqueria').'.tutores.buscar') }}"></div></div>
<div class="col-md-3"><button type="button" id="reserva-buscar-tutor" class="btn btn-info btn-sm btn-block"><i class="feather icon-search mr-1"></i>Buscar</button></div>
</div>
<div class="d-flex justify-content-between align-items-center mb-3"><span id="reserva-tutor-encontrado" class="text-muted" role="status"></span><button type="button" id="reserva-cambiar-tutor" class="btn btn-link btn-sm" hidden>Cambiar tutor</button></div>
<fieldset id="reserva-datos" hidden disabled>
<label class="sr-only" for="reserva-inicio">Fecha y hora de la reserva</label><input class="form-control form-control-sm mb-3" type="hidden" id="reserva-inicio" name="inicio">
<dl class="row mb-3"><dt class="col-sm-3">Lugar de atención</dt><dd class="col-sm-9 mb-1">{{ $sucursal->nombre??$perfil->nombre }}@if(!empty($sucursal->direccion))<br><small class="text-muted">{{ $sucursal->direccion }}</small>@endif</dd><dt class="col-sm-3">Profesional</dt><dd class="col-sm-9 mb-0">{{ $profesionalAgenda['nombre']??$perfil->nombre }}</dd></dl>
<p id="reserva-fecha-seleccionada" class="text-muted small mb-4" role="status"></p>
<input type="hidden" name="mascota_sistema_id" id="reserva-mascota-sistema">
<div class="form-row">
    <div class="col-12" id="reserva-cliente-existente"><div class="form-group">
        <label class="floating-label-activo-sm" for="reserva-cliente">Mascota</label>
        <select id="reserva-cliente" class="form-control form-control-sm" name="cliente_id" required>
            <option value="">Seleccione una mascota</option>

        </select>
    </div></div>
</div>
<input type="hidden" name="nuevo_cliente" value="0"><div id="reserva-opcion-mascota-nueva" data-permitido="{{ ($peluqueriaPermisos['editar_clientes']??false)?'1':'0' }}" class="custom-control custom-switch mb-3" @if(!($peluqueriaPermisos['editar_clientes']??false)) hidden @endif><input class="custom-control-input" type="checkbox" id="reserva-nuevo" name="nuevo_cliente" value="1" ><label class="custom-control-label" for="reserva-nuevo">Inscribir una mascota nueva</label></div>
<div id="reserva-cliente-nuevo" hidden><div class="form-row">
@foreach(['tutor'=>'Nombre del tutor','telefono'=>'Teléfono','email'=>'Correo (opcional)','mascota'=>'Nombre de la mascota','especie'=>'Especie','raza'=>'Raza (opcional)'] as $campo=>$etiqueta)<div class="col-md-6"><div class="form-group"><label class="floating-label-activo-sm" for="reserva-{{ $campo }}">{{ $etiqueta }}</label><input class="form-control form-control-sm" id="reserva-{{ $campo }}" name="{{ $campo }}" type="{{ $campo==='email'?'email':'text' }}" maxlength="{{ $campo==='telefono'?30:(in_array($campo,['mascota','raza'])?100:($campo==='especie'?60:255)) }}" data-obligatorio="{{ in_array($campo,['email','raza'])?'0':'1' }}"></div></div>@endforeach
</div></div>
<button type="button" id="reserva-fijar-nueva-mascota" class="btn btn-info btn-sm mb-3" hidden>Continuar con esta mascota</button>
<p id="reserva-mascota-fijada" class="border rounded p-3 mb-3" hidden></p>
<fieldset id="reserva-paso-procedimientos" hidden disabled>
<div class="form-row">    <div class="col-12"><div class="form-group">
        <label class="floating-label-activo-sm" for="procedimiento-reserva">Procedimiento</label>
        <select class="form-control form-control-sm" id="procedimiento-reserva">
            <option value="">Seleccione</option>
            @foreach($procedimientos as $procedimiento)
            <option value="{{ $procedimiento->id }}" data-precio="{{ $procedimiento->precio }}" data-duracion="{{ $procedimiento->duracion }}">{{ $procedimiento->nombre }} — ${{ number_format($procedimiento->precio,0,',','.') }}</option>
            @endforeach
        </select>
    </div></div>
</div>
<div id="reserva-procedimientos-adicionales" class="mb-3" aria-label="Procedimientos seleccionados"></div>
<div class="border rounded p-3 mb-3" aria-live="polite"><span>Total a pagar</span><strong class="float-right text-info" id="reserva-total">$0</strong><div id="reserva-detalle-costos" class="small text-muted mt-2"></div></div>
<div class="form-group"><label class="floating-label-activo-sm" for="reserva-observaciones">Observaciones</label><textarea class="form-control caja-texto form-control-sm" id="reserva-observaciones" name="observaciones" maxlength="3000" rows="1" onfocus="this.rows=3" onblur="this.rows=1;"></textarea></div>
</fieldset>
</fieldset>
<div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button> <button id="reserva-guardar" type="submit" class="btn btn-info" hidden>Reservar hora</button></div>
</form>@endif</div></div></div></div>
