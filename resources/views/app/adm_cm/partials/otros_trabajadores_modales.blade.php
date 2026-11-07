@php($otrosDatos = $otrosTrabajadores)
<style>
    #modalOtroTrabajador .modal-content>form,#modalOtroContrato .modal-content>form{display:flex;flex-direction:column;min-height:0;overflow:hidden}
    #modalOtroInfo .otro-info-bloque{margin-bottom:12px;padding:12px 12px 4px;border:1px solid #e6ebf2;border-radius:12px}
    #modalOtroInfo .otro-info-titulo{display:flex;align-items:center;gap:6px;margin:0 0 12px;color:#6f42c1;font-family:'Nunito','Nunito Sans',sans-serif;font-size:12px;font-weight:800;letter-spacing:.04em;text-transform:uppercase}
    #modalOtroInfo .otro-info-titulo i{font-size:13px}
    #modalOtroInfo .otro-info-dato{margin-bottom:10px}
    #modalOtroInfo .otro-info-dato dt{margin:0;color:#272727;font-size:12px;font-weight:700}
    #modalOtroInfo .otro-info-dato dd{margin:0;color:#6b7a90;font-size:13.5px;word-break:break-word}
    #modalOtroInfo .otro-info-item{padding:8px 10px;margin-bottom:8px;border:1px solid #e6ebf2;border-radius:10px;background:#f8fafc}
    #modalOtroMensaje .otro-mensaje-destino{display:flex;align-items:center;gap:12px;margin-bottom:14px;padding:10px 14px;border-radius:12px;background:#f3eefc}
    #modalOtroMensaje .otro-mensaje-destino>i{display:inline-flex;align-items:center;justify-content:center;flex:0 0 auto;width:34px;height:34px;border-radius:50%;background:#6f42c1;color:#fff;font-size:16px}
    #modalOtroMensaje .otro-mensaje-destino-etiqueta{display:block;color:#6b7a90;font-size:11px;font-weight:800;letter-spacing:.04em;text-transform:uppercase}
    #modalOtroMensaje .otro-mensaje-destino strong{color:#272727;font-size:14px;word-break:break-word}
    #modalOtroMensaje .otro-mensaje-bloque{padding:12px 12px 0;border:1px solid #e6ebf2;border-radius:12px}
    #modalOtroTrabajador .modal-body{padding:16px 18px}
    #modalOtroTrabajador .otro-trab-ayuda{margin:0 0 12px;color:#6b7a90;font-size:12.5px;line-height:1.45}
    #modalOtroTrabajador .otro-trab-bloque{margin-bottom:12px;padding:12px 12px 0;border:1px solid #e6ebf2;border-radius:12px}
    #modalOtroTrabajador .otro-trab-bloque-titulo{display:flex;align-items:center;gap:6px;margin:0 0 16px;color:#6f42c1;font-family:'Nunito','Nunito Sans',sans-serif;font-size:12px;font-weight:800;letter-spacing:.3px;text-transform:uppercase}
    #modalOtroTrabajador .otro-trab-bloque-titulo i{font-size:13px}
    #modalOtroTrabajador .otro-trab-obligatorio{margin-left:2px;color:#e5484d}
</style>
<div id="modalOtroTrabajador" class="modal fade" tabindex="-1" aria-labelledby="otro-trabajador-titulo" aria-hidden="true"><div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered"><div class="modal-content">
    <div class="modal-header modal-header-purple"><h5 id="otro-trabajador-titulo" class="modal-title">Trabajador</h5><button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">×</button></div>
    <form id="formOtroTrabajador" data-otro-form="guardar"><div class="modal-body">
        @csrf<input type="hidden" name="id"><div class="alert d-none" data-resultado role="status"></div>
        @php($bancosChile=['Banco de Chile','BancoEstado','Banco Santander Chile','Banco de Crédito e Inversiones (BCI)','Scotiabank Chile','Itaú Chile','Banco Falabella','Banco Ripley','Banco Security','Banco Bice','Banco Consorcio','Banco Internacional','BTG Pactual Chile','Coopeuch'])
        @php($tiposCuenta=['Cuenta corriente','Cuenta vista','Cuenta de ahorro','Cuenta RUT'])
        <p class="otro-trab-ayuda">Complete los datos del trabajador. Los campos con <span class="otro-trab-obligatorio">*</span> son obligatorios.</p>

        <div class="otro-trab-bloque">
            <h6 class="otro-trab-bloque-titulo"><i class="feather icon-user"></i>Datos personales</h6>
            <div class="form-row">
                <div class="col-md-6"><div class="form-group fill"><label class="floating-label-activo-sm" for="otro-rut">RUT<span class="otro-trab-obligatorio">*</span></label><input id="otro-rut" name="rut" class="form-control form-control-sm" type="text" maxlength="20" required></div></div>
                <div class="col-md-6"><div class="form-group fill"><label class="floating-label-activo-sm" for="otro-nombres">Nombres<span class="otro-trab-obligatorio">*</span></label><input id="otro-nombres" name="nombres" class="form-control form-control-sm" type="text" maxlength="100" required></div></div>
                <div class="col-md-6"><div class="form-group fill"><label class="floating-label-activo-sm" for="otro-apellido_uno">Primer apellido<span class="otro-trab-obligatorio">*</span></label><input id="otro-apellido_uno" name="apellido_uno" class="form-control form-control-sm" type="text" maxlength="80" required></div></div>
                <div class="col-md-6"><div class="form-group fill"><label class="floating-label-activo-sm" for="otro-apellido_dos">Segundo apellido</label><input id="otro-apellido_dos" name="apellido_dos" class="form-control form-control-sm" type="text" maxlength="80"></div></div>
            </div>
        </div>

        <div class="otro-trab-bloque">
            <h6 class="otro-trab-bloque-titulo"><i class="feather icon-phone"></i>Contacto</h6>
            <div class="form-row">
                <div class="col-md-6"><div class="form-group fill"><label class="floating-label-activo-sm" for="otro-email">Correo electrónico</label><input id="otro-email" name="email" class="form-control form-control-sm" type="email" maxlength="255"></div></div>
                <div class="col-md-6"><div class="form-group fill"><label class="floating-label-activo-sm" for="otro-telefono">Teléfono</label><input id="otro-telefono" name="telefono" class="form-control form-control-sm" type="tel" maxlength="12" placeholder="+569"></div></div>
            </div>
        </div>

        <div class="otro-trab-bloque">
            <h6 class="otro-trab-bloque-titulo"><i class="feather icon-map-pin"></i>Residencia</h6>
            <div class="form-row">
                <div class="col-md-12"><div class="form-group fill"><label class="floating-label-activo-sm" for="otro-direccion">Dirección</label><input id="otro-direccion" name="direccion" class="form-control form-control-sm" type="text" maxlength="255"></div></div>
                <div class="col-md-6"><div class="form-group fill"><label class="floating-label-activo-sm" for="otro-region">Región</label><select id="otro-region" name="region_id" class="form-control form-control-sm"><option value="">Seleccione región</option>@foreach(($otrosDatos['regiones'] ?? []) as $region)<option value="{{ $region->id }}">{{ $region->nombre }}</option>@endforeach</select></div></div>
                <div class="col-md-6"><div class="form-group fill"><label class="floating-label-activo-sm" for="otro-comuna">Comuna</label><select id="otro-comuna" name="comuna_id" class="form-control form-control-sm" disabled><option value="">Seleccione comuna</option></select></div></div>
            </div>
        </div>

        <div class="otro-trab-bloque">
            <h6 class="otro-trab-bloque-titulo"><i class="feather icon-credit-card"></i>Datos bancarios</h6>
            <div class="form-row">
                <div class="col-md-6"><div class="form-group fill"><label class="floating-label-activo-sm" for="otro-banco">Banco</label><select id="otro-banco" name="banco" class="form-control form-control-sm"><option value="">Seleccione banco</option>@foreach($bancosChile as $banco)<option value="{{ $banco }}">{{ $banco }}</option>@endforeach</select></div></div>
                <div class="col-md-6"><div class="form-group fill"><label class="floating-label-activo-sm" for="otro-tipo_cuenta">Tipo de cuenta</label><select id="otro-tipo_cuenta" name="tipo_cuenta" class="form-control form-control-sm"><option value="">Seleccione tipo de cuenta</option>@foreach($tiposCuenta as $tipo)<option value="{{ $tipo }}">{{ $tipo }}</option>@endforeach</select></div></div>
                <div class="col-md-12"><div class="form-group fill"><label class="floating-label-activo-sm" for="otro-numero_cuenta">Número de cuenta</label><input id="otro-numero_cuenta" name="numero_cuenta" class="form-control form-control-sm" type="text" maxlength="50"></div></div>
            </div>
        </div>

        <div class="otro-trab-bloque">
            <h6 class="otro-trab-bloque-titulo"><i class="feather icon-briefcase"></i>Lugar y cargo</h6>
            <div class="form-row">
                <div class="col-md-12"><div class="form-group fill"><label class="floating-label-activo-sm" for="otro-lugares">Lugar de atención<span class="otro-trab-obligatorio">*</span></label><select id="otro-lugares" name="lugares[]" class="form-control form-control-sm" required><option value="">Seleccione lugar de atención</option>@foreach($otrosDatos['lugares'] as $lugar)<option value="{{ $lugar->id }}">{{ $lugar->nombre }}</option>@endforeach</select></div></div>
                <div class="col-md-6"><div class="form-group fill"><label class="floating-label-activo-sm" for="otro-categoria">Categoría<span class="otro-trab-obligatorio">*</span></label><select id="otro-categoria" name="categoria_id" class="form-control form-control-sm" required><option value="">Seleccione</option>@foreach($otrosDatos['categorias']->where('activa',true) as $categoria)<option value="{{ $categoria->id }}">{{ $categoria->nombre }}</option>@endforeach<option value="otro">Otro</option></select><button type="button" class="btn btn-link btn-sm pl-0" data-otro-accion="categorias">Crear o editar categoría</button></div></div>
                <div class="col-md-6"><div class="form-group fill"><label class="floating-label-activo-sm" for="otro-activo">Estado</label><select id="otro-activo" name="activo" class="form-control form-control-sm"><option value="1">Activo</option><option value="0">Desvinculado</option></select></div></div>
                <div class="col-md-12 d-none" id="otro-cargo-caja"><div class="form-group fill"><label class="floating-label-activo-sm" for="otro-cargo">Cargo<span class="otro-trab-obligatorio">*</span></label><input id="otro-cargo" name="cargo_otro" class="form-control form-control-sm" type="text" maxlength="100" placeholder="Escriba el cargo"></div></div>
            </div>
        </div>

        <div class="otro-trab-bloque">
            <h6 class="otro-trab-bloque-titulo"><i class="feather icon-message-square"></i>Observaciones</h6>
            <div class="form-group fill"><label class="floating-label-activo-sm" for="otro-observaciones">Observaciones</label><textarea id="otro-observaciones" name="observaciones" class="form-control form-control-sm" rows="3" maxlength="10000"></textarea></div>
        </div>
    </div><div class="modal-footer"><button type="button" class="btn btn-danger btn-sm" data-dismiss="modal">Cancelar</button><button type="submit" class="btn btn-info btn-sm"><i class="feather icon-save mr-1"></i>Guardar trabajador</button></div></form>
</div></div></div>
<div id="modalOtroCategoria" class="modal fade" tabindex="-1" aria-labelledby="otro-categoria-titulo" aria-hidden="true"><div class="modal-dialog modal-dialog-centered"><div class="modal-content">
    <div class="modal-header modal-header-purple"><h5 id="otro-categoria-titulo" class="modal-title">Categorías de trabajadores</h5><button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">×</button></div>
    <form id="formOtroCategoria" data-otro-form="categoria"><div class="modal-body">@csrf<div class="alert d-none" data-resultado role="status"></div>
        <div class="form-group"><label class="floating-label-activo-sm" for="otra-categoria-id">Categoría a editar</label><select id="otra-categoria-id" name="id" class="form-control form-control-sm"><option value="">Crear nueva categoría</option>@foreach($otrosDatos['categorias'] as $c)<option value="{{ $c->id }}">{{ $c->nombre }}</option>@endforeach</select></div>
        <div class="form-group"><label class="floating-label-activo-sm" for="otra-categoria-nombre">Nombre</label><input id="otra-categoria-nombre" name="nombre" class="form-control form-control-sm" required maxlength="100"></div>
        <div class="form-group"><label class="floating-label-activo-sm" for="otra-categoria-activa">Estado</label><select id="otra-categoria-activa" name="activa" class="form-control form-control-sm"><option value="1">Activa</option><option value="0">Inactiva</option></select></div>
    </div><div class="modal-footer"><button type="button" class="btn btn-outline-dark btn-sm" data-dismiss="modal">Cancelar</button><button type="submit" class="btn btn-info btn-sm">Guardar categoría</button></div></form>
</div></div></div>

<div id="modalOtroInfo" class="modal fade" tabindex="-1" aria-labelledby="otro-info-titulo" aria-hidden="true"><div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered"><div class="modal-content">
    <div class="modal-header modal-header-purple"><h5 id="otro-info-titulo" class="modal-title">Información del trabajador</h5><button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">×</button></div>
    <div class="modal-body"><div id="otro-info-datos"></div><div class="otro-info-bloque"><h6 class="otro-info-titulo"><i class="feather icon-file-text"></i>Contratos</h6><div id="otro-info-contratos"></div></div><div class="otro-info-bloque"><h6 class="otro-info-titulo"><i class="feather icon-mail"></i>Mensajes recientes</h6><div id="otro-info-mensajes"></div></div></div>
    <div class="modal-footer"><button type="button" class="btn btn-outline-dark btn-sm" data-dismiss="modal">Cerrar</button></div>
</div></div></div>

<div id="modalOtroContrato" class="modal fade" tabindex="-1" aria-labelledby="otro-contrato-titulo" aria-hidden="true"><div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered"><div class="modal-content">
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

<div id="modalOtroTermino" class="modal fade" tabindex="-1" aria-labelledby="otro-termino-titulo" aria-hidden="true"><div class="modal-dialog modal-dialog-centered"><div class="modal-content">
    <div class="modal-header modal-header-purple"><h5 id="otro-termino-titulo" class="modal-title">Finalizar contrato</h5><button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">×</button></div>
    <form id="formOtroTermino" data-otro-form="terminar"><div class="modal-body">@csrf<input name="id" type="hidden"><div class="alert d-none" data-resultado role="status"></div><p>Se conservará el historial. Si el contrato aún no comienza, quedará anulado.</p><label class="floating-label-activo-sm" for="otro-fecha-termino">Fecha de término</label><input id="otro-fecha-termino" name="fecha_termino" type="date" required class="form-control form-control-sm" max="{{ now(config('operacion_veterinaria.timezone','America/Santiago'))->toDateString() }}"></div><div class="modal-footer"><button type="button" class="btn btn-outline-dark btn-sm" data-dismiss="modal">Cancelar</button><button type="submit" class="btn btn-danger btn-sm">Finalizar contrato</button></div></form>
</div></div></div>

<div id="modalOtroMensaje" class="modal fade" tabindex="-1" aria-labelledby="otro-mensaje-titulo" aria-hidden="true"><div class="modal-dialog modal-dialog-centered"><div class="modal-content">
    <div class="modal-header modal-header-purple"><h5 id="otro-mensaje-titulo" class="modal-title"><i class="feather icon-mail mr-1"></i>Mensaje por correo</h5><button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">×</button></div>
    <form id="formOtroMensaje" data-otro-form="mensaje"><div class="modal-body">@csrf<input type="hidden" name="trabajador_id"><input type="hidden" name="token"><div class="alert d-none" data-resultado role="status"></div>
        <div class="otro-mensaje-destino"><i class="feather icon-user"></i><div><span class="otro-mensaje-destino-etiqueta">Para</span><strong id="otro-mensaje-destinatario"></strong></div></div>
        <div class="otro-mensaje-bloque">
            <div class="form-group"><label class="floating-label-activo-sm" for="otro-mensaje-asunto">Asunto</label><input id="otro-mensaje-asunto" name="asunto" class="form-control form-control-sm" required maxlength="200"></div>
            <div class="form-group"><label class="floating-label-activo-sm" for="otro-mensaje-texto">Mensaje</label><textarea id="otro-mensaje-texto" name="mensaje" class="form-control form-control-sm" required maxlength="20000" rows="6"></textarea></div>
        </div>
    </div><div class="modal-footer"><button type="button" class="btn btn-danger btn-sm" data-dismiss="modal">Cancelar</button><button type="submit" class="btn btn-info btn-sm"><i class="feather icon-send mr-1"></i>Enviar mensaje</button></div></form>
</div></div></div>
