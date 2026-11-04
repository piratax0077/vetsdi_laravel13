@php($otrosDatos = $otrosTrabajadores)
<style>
    #modalOtroTrabajador .modal-content>form,#modalOtroContrato .modal-content>form{display:flex;flex-direction:column;min-height:0;overflow:hidden}
</style>
<div id="modalOtroTrabajador" class="modal fade" tabindex="-1" aria-labelledby="otro-trabajador-titulo" aria-hidden="true"><div class="modal-dialog modal-lg modal-dialog-scrollable"><div class="modal-content">
    <div class="modal-header modal-header-purple"><h5 id="otro-trabajador-titulo" class="modal-title">Trabajador</h5><button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">×</button></div>
    <form id="formOtroTrabajador" data-otro-form="guardar"><div class="modal-body">
        @csrf<input type="hidden" name="id"><div class="alert d-none" data-resultado role="status"></div>
        <div class="form-row">
            @foreach(['rut'=>'RUT','nombres'=>'Nombres','apellido_uno'=>'Primer apellido','apellido_dos'=>'Segundo apellido','email'=>'Correo electrónico','telefono'=>'Teléfono','direccion'=>'Dirección','banco'=>'Banco','tipo_cuenta'=>'Tipo de cuenta','numero_cuenta'=>'Número de cuenta'] as $campo=>$label)
            <div class="form-group col-md-6"><label class="floating-label-activo-sm" for="otro-{{ $campo }}">{{ $label }}</label><input id="otro-{{ $campo }}" name="{{ $campo }}" class="form-control form-control-sm" type="{{ $campo === 'email' ? 'email' : 'text' }}" maxlength="{{ ['rut'=>20,'nombres'=>100,'apellido_uno'=>80,'apellido_dos'=>80,'telefono'=>30,'banco'=>100,'tipo_cuenta'=>60,'numero_cuenta'=>50][$campo] ?? 255 }}" @required(in_array($campo,['rut','nombres','apellido_uno']))></div>
            @endforeach
            <div class="form-group col-md-6"><label class="floating-label-activo-sm" for="otro-region">Región</label><select id="otro-region" name="region_id" class="form-control form-control-sm"><option value="">Seleccione región</option>@foreach(($otrosDatos['regiones'] ?? []) as $region)<option value="{{ $region->id }}">{{ $region->nombre }}</option>@endforeach</select></div>
            <div class="form-group col-md-6"><label class="floating-label-activo-sm" for="otro-comuna">Comuna</label><select id="otro-comuna" name="comuna_id" class="form-control form-control-sm" disabled><option value="">Seleccione comuna</option></select></div>
            <div class="col-12"><small class="text-muted">RUT: ingrese el número y su dígito verificador, con o sin puntos.</small></div>
            <div class="form-group col-md-6"><label class="floating-label-activo-sm" for="otro-categoria">Categoría</label><select id="otro-categoria" name="categoria_id" class="form-control form-control-sm" required><option value="">Seleccione</option>@foreach($otrosDatos['categorias']->where('activa',true) as $categoria)<option value="{{ $categoria->id }}">{{ $categoria->nombre }}</option>@endforeach</select><button type="button" class="btn btn-link btn-sm pl-0" data-otro-accion="categorias">Crear o editar categoría</button></div>
            <div class="form-group col-md-6"><label class="floating-label-activo-sm" for="otro-activo">Estado</label><select id="otro-activo" name="activo" class="form-control form-control-sm"><option value="1">Activo</option><option value="0">Desvinculado</option></select></div>
            <div class="form-group col-12"><label class="floating-label-activo-sm" for="otro-lugares">Sucursales</label><select id="otro-lugares" name="lugares[]" class="form-control" multiple required size="{{ min(5,max(2,$otrosDatos['lugares']->count())) }}">@foreach($otrosDatos['lugares'] as $lugar)<option value="{{ $lugar->id }}">{{ $lugar->nombre }}</option>@endforeach</select><small class="text-muted">Puede seleccionar varias sucursales.</small></div>
            <div class="form-group col-12"><label class="floating-label-activo-sm" for="otro-observaciones">Observaciones</label><textarea id="otro-observaciones" name="observaciones" class="form-control form-control-sm" rows="3" maxlength="10000"></textarea></div>
        </div>
    </div><div class="modal-footer"><button type="button" class="btn btn-outline-dark btn-sm" data-dismiss="modal">Cancelar</button><button type="submit" class="btn btn-info btn-sm">Guardar trabajador</button></div></form>
</div></div></div>

<div id="modalOtroCategoria" class="modal fade" tabindex="-1" aria-labelledby="otro-categoria-titulo" aria-hidden="true"><div class="modal-dialog"><div class="modal-content">
    <div class="modal-header modal-header-purple"><h5 id="otro-categoria-titulo" class="modal-title">Categorías de trabajadores</h5><button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">×</button></div>
    <form id="formOtroCategoria" data-otro-form="categoria"><div class="modal-body">@csrf<div class="alert d-none" data-resultado role="status"></div>
        <div class="form-group"><label class="floating-label-activo-sm" for="otra-categoria-id">Categoría a editar</label><select id="otra-categoria-id" name="id" class="form-control form-control-sm"><option value="">Crear nueva categoría</option>@foreach($otrosDatos['categorias'] as $c)<option value="{{ $c->id }}">{{ $c->nombre }}</option>@endforeach</select></div>
        <div class="form-group"><label class="floating-label-activo-sm" for="otra-categoria-nombre">Nombre</label><input id="otra-categoria-nombre" name="nombre" class="form-control form-control-sm" required maxlength="100"></div>
        <div class="form-group"><label class="floating-label-activo-sm" for="otra-categoria-activa">Estado</label><select id="otra-categoria-activa" name="activa" class="form-control form-control-sm"><option value="1">Activa</option><option value="0">Inactiva</option></select></div>
    </div><div class="modal-footer"><button type="button" class="btn btn-outline-dark btn-sm" data-dismiss="modal">Cancelar</button><button type="submit" class="btn btn-info btn-sm">Guardar categoría</button></div></form>
</div></div></div>

<div id="modalOtroInfo" class="modal fade" tabindex="-1" aria-labelledby="otro-info-titulo" aria-hidden="true"><div class="modal-dialog modal-lg modal-dialog-scrollable"><div class="modal-content">
    <div class="modal-header modal-header-purple"><h5 id="otro-info-titulo" class="modal-title">Información del trabajador</h5><button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">×</button></div>
    <div class="modal-body"><dl class="row" id="otro-info-datos"></dl><h6>Contratos</h6><div id="otro-info-contratos"></div><h6 class="mt-3">Mensajes recientes</h6><div id="otro-info-mensajes"></div></div>
    <div class="modal-footer"><button type="button" class="btn btn-outline-dark btn-sm" data-dismiss="modal">Cerrar</button></div>
</div></div></div>

<div id="modalOtroContrato" class="modal fade" tabindex="-1" aria-labelledby="otro-contrato-titulo" aria-hidden="true"><div class="modal-dialog modal-lg modal-dialog-scrollable"><div class="modal-content">
    <div class="modal-header modal-header-purple"><h5 id="otro-contrato-titulo" class="modal-title">Contrato del trabajador</h5><button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">×</button></div>
    <form id="formOtroContrato" data-otro-form="contrato"><div class="modal-body">@csrf<input type="hidden" name="id"><div class="alert d-none" data-resultado role="status"></div><div class="form-row">
        <div class="form-group col-md-6"><label class="floating-label-activo-sm" for="otro-contrato-trabajador">Trabajador</label><select id="otro-contrato-trabajador" name="trabajador_id" class="form-control form-control-sm" required><option value="">Seleccione</option>@foreach($otrosDatos['trabajadores'] as $t)<option value="{{ $t->id }}">{{ $t->nombre_completo }} · {{ rut_formatear($t->rut) ?? $t->rut }}{{ !$t->activo ? ' (desvinculado)' : '' }}</option>@endforeach</select></div>
        <div class="form-group col-md-6"><label class="floating-label-activo-sm" for="otro-contrato-tipo">Tipo de contrato</label><select id="otro-contrato-tipo" name="tipo" class="form-control form-control-sm" required>@foreach(\App\Services\TrabajadoresCentroVeterinario::TIPOS_CONTRATO as $key=>$label)<option value="{{ $key }}">{{ $label }}</option>@endforeach</select></div>
        @foreach(['fecha_inicio'=>'Fecha de inicio','fecha_fin'=>'Fecha de fin (plazo fijo)'] as $campo=>$label)<div class="form-group col-md-6"><label class="floating-label-activo-sm" for="otro-contrato-{{ $campo }}">{{ $label }}</label><input id="otro-contrato-{{ $campo }}" type="date" name="{{ $campo }}" class="form-control form-control-sm" @required($campo === 'fecha_inicio')></div>@endforeach
        <div class="form-group col-md-6"><label class="floating-label-activo-sm" for="otro-contrato-horas">Horas semanales</label><input id="otro-contrato-horas" name="horas_semanales" type="number" min="0" max="168" step="0.01" class="form-control form-control-sm"></div>
        <div class="form-group col-md-6"><label class="floating-label-activo-sm" for="otro-contrato-remuneracion">Remuneración mensual CLP</label><input id="otro-contrato-remuneracion" name="remuneracion" type="number" min="0" max="1000000000" step="1" class="form-control form-control-sm" required></div>
        <div class="form-group col-12"><label class="floating-label-activo-sm" for="otro-contrato-observaciones">Observaciones</label><textarea id="otro-contrato-observaciones" name="observaciones" class="form-control form-control-sm" maxlength="10000"></textarea></div>
    </div></div><div class="modal-footer"><button type="button" class="btn btn-outline-dark btn-sm" data-dismiss="modal">Cancelar</button><button type="submit" class="btn btn-info btn-sm">Guardar contrato</button></div></form>
</div></div></div>

<div id="modalOtroTermino" class="modal fade" tabindex="-1" aria-labelledby="otro-termino-titulo" aria-hidden="true"><div class="modal-dialog"><div class="modal-content">
    <div class="modal-header modal-header-purple"><h5 id="otro-termino-titulo" class="modal-title">Finalizar contrato</h5><button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">×</button></div>
    <form id="formOtroTermino" data-otro-form="terminar"><div class="modal-body">@csrf<input name="id" type="hidden"><div class="alert d-none" data-resultado role="status"></div><p>Se conservará el historial. Si el contrato aún no comienza, quedará anulado.</p><label class="floating-label-activo-sm" for="otro-fecha-termino">Fecha de término</label><input id="otro-fecha-termino" name="fecha_termino" type="date" required class="form-control form-control-sm" max="{{ now(config('operacion_veterinaria.timezone','America/Santiago'))->toDateString() }}"></div><div class="modal-footer"><button type="button" class="btn btn-outline-dark btn-sm" data-dismiss="modal">Cancelar</button><button type="submit" class="btn btn-danger btn-sm">Finalizar contrato</button></div></form>
</div></div></div>

<div id="modalOtroMensaje" class="modal fade" tabindex="-1" aria-labelledby="otro-mensaje-titulo" aria-hidden="true"><div class="modal-dialog"><div class="modal-content">
    <div class="modal-header modal-header-purple"><h5 id="otro-mensaje-titulo" class="modal-title">Mensaje por correo</h5><button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">×</button></div>
    <form id="formOtroMensaje" data-otro-form="mensaje"><div class="modal-body">@csrf<input type="hidden" name="trabajador_id"><input type="hidden" name="token"><div class="alert d-none" data-resultado role="status"></div>
        <p>Destinatario: <strong id="otro-mensaje-destinatario"></strong></p><div class="form-group"><label class="floating-label-activo-sm" for="otro-mensaje-asunto">Asunto</label><input id="otro-mensaje-asunto" name="asunto" class="form-control form-control-sm" required maxlength="200"></div>
        <div class="form-group"><label class="floating-label-activo-sm" for="otro-mensaje-texto">Mensaje</label><textarea id="otro-mensaje-texto" name="mensaje" class="form-control form-control-sm" required maxlength="20000" rows="5"></textarea></div>
    </div><div class="modal-footer"><button type="button" class="btn btn-outline-dark btn-sm" data-dismiss="modal">Cancelar</button><button type="submit" class="btn btn-info btn-sm">Enviar mensaje</button></div></form>
</div></div></div>
