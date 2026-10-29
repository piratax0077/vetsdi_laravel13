<div class="card"><div class="card-header encabezado-card"><h5 class="encabezado-card-titulo"><i class="feather icon-credit-card mr-2"></i>{{ $cuenta ? 'Cuenta institucional · '.$cuenta->numero_cuenta : 'Agregar cuenta institucional' }}</h5></div><div class="card-body">
    <form method="post" action="{{ route($cuenta ? 'adm_cm.editar_cuenta_bancaria_institucion' : 'adm_cm.agregar_cuenta_bancaria_institucion') }}" data-centro-form="cuenta" data-recargar="1">
        @csrf
        <input type="hidden" name="id_institucion" value="{{ $institucion->id }}">
        <input type="hidden" name="tipo_institucion" value="{{ $tipo_institucion }}">
        @if($cuenta)<input type="hidden" name="id" value="{{ $cuenta->id }}">@endif
        <div class="alert d-none" data-resultado role="status"></div>
        <div class="form-row">
            @foreach(['rut_titular'=>'RUT titular','nombre_titular'=>'Nombre titular','numero_cuenta'=>'Número de cuenta','email_titular'=>'Correo para depósitos','rut_representante'=>'RUT representante','nombre_representante'=>'Nombre representante'] as $campo=>$etiqueta)
            <div class="form-group col-md-6"><label>{{ $etiqueta }}</label><input type="{{ $campo === 'email_titular' ? 'email' : 'text' }}" name="{{ $campo }}" class="form-control form-control-sm" value="{{ $campo === 'email_titular' ? $cuenta?->email : $cuenta?->{$campo} }}" maxlength="{{ str_starts_with($campo,'rut_') ? 20 : ($campo === 'numero_cuenta' ? 50 : 255) }}" @required(!str_contains($campo,'representante'))></div>
            @endforeach
            <div class="form-group col-md-6"><label>Banco</label><select name="banco_titular" class="form-control form-control-sm" required><option value="">Seleccione</option>@foreach($bancos as $banco)<option value="{{ $banco->id }}" @selected((int)$cuenta?->id_banco === (int)$banco->id)>{{ $banco->nombre }}</option>@endforeach</select></div>
            <div class="form-group col-md-6"><label>Tipo de cuenta</label><select name="tipo_cuenta" class="form-control form-control-sm" required><option value="">Seleccione</option>@foreach($tipo_cuentas_bancarias as $tipo)<option value="{{ $tipo->id }}" @selected((int)$cuenta?->id_tipo_cuenta === (int)$tipo->id)>{{ $tipo->descripcion }}</option>@endforeach</select></div>
        </div>
        <button type="submit" class="btn btn-info btn-sm">{{ $cuenta ? 'Guardar cambios' : 'Agregar cuenta' }}</button>
        <button type="reset" class="btn btn-outline-dark btn-sm">Cancelar</button>
    </form>
    @if($cuenta)
    <form method="post" action="{{ route('adm_cm.eliminar_cuenta_bancaria_institucion') }}" data-centro-form="eliminar-cuenta" data-recargar="1" class="mt-3">
        @csrf<input type="hidden" name="id_institucion" value="{{ $institucion->id }}"><input type="hidden" name="tipo_institucion" value="{{ $tipo_institucion }}"><input type="hidden" name="id" value="{{ $cuenta->id }}">
        <div class="alert d-none" data-resultado role="status"></div><button type="submit" class="btn btn-outline-danger btn-sm">Eliminar cuenta</button>
    </form>
    @endif
</div></div>
