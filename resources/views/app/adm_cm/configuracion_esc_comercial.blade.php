@extends('template.adm_cm.template')
@section('content')
<div class="pcoded-main-container"><div class="pcoded-content configuracion-administrador">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h4>Configuración comercial · {{ $institucion->nombre }}</h4>
        <a href="{{ route('adm_cm.area_comercial', ['id_institucion'=>$institucion->id, 'tipo_institucion'=>$tipo_institucion]) }}" class="btn btn-outline-info btn-sm">Volver a comercial</a>
    </div>
    <div class="card"><div class="card-header encabezado-card"><h5 class="encabezado-card-titulo"><i class="feather icon-user mr-2"></i>Responsable comercial</h5></div><div class="card-body">
        @if($director_comercial)
        <form method="post" action="{{ route('adm_cm.editar_datos_perfil_responsable_medico') }}" data-centro-form="medico">
            @csrf
            <input type="hidden" name="id_institucion" value="{{ $institucion->id }}">
            <input type="hidden" name="tipo_institucion" value="{{ $tipo_institucion }}">
            <input type="hidden" name="id_responsable" value="{{ $director_comercial->id }}">
            <div class="alert d-none" data-resultado role="status"></div>
            @include('app.adm_cm.perfil.persona', ['persona'=>$director_comercial, 'medico'=>true])
            <button type="submit" class="btn btn-info btn-sm">Guardar cambios</button>
            <button type="reset" class="btn btn-outline-dark btn-sm">Cancelar</button>
        </form>
        @else
        <p>No hay un responsable comercial asignado.</p>
        <a href="{{ route('adm_cm.configuracion') }}" class="btn btn-outline-info btn-sm">Administrar responsables</a>
        @endif
    </div></div>
    <div class="card"><div class="card-header encabezado-card"><h5 class="encabezado-card-titulo"><i class="feather icon-lock mr-2"></i>Contraseña de mi cuenta</h5></div><div class="card-body">
        <form method="post" action="{{ route('perfil.usuario.contrasena') }}" data-centro-form="contrasena">
            @csrf<div class="alert d-none" data-resultado role="status"></div>
            <div class="form-row">
                @foreach(['contrasena_actual'=>'Contraseña actual','password_registro'=>'Nueva contraseña','password_confirmacion_registro'=>'Confirmar contraseña'] as $campo=>$etiqueta)
                <div class="form-group col-md-4"><label>{{ $etiqueta }}</label><input type="password" name="{{ $campo }}" class="form-control form-control-sm" required autocomplete="{{ $campo === 'contrasena_actual' ? 'current-password' : 'new-password' }}" @if($campo !== 'contrasena_actual') minlength="8" @endif></div>
                @endforeach
            </div>
            <button type="submit" class="btn btn-info btn-sm">Guardar contraseña</button>
        </form>
    </div></div>
    @foreach($cuentas_bancarias as $cuenta)
        @include('app.adm_cm.comercial.cuenta_form', ['cuenta'=>$cuenta])
    @endforeach
    @include('app.adm_cm.comercial.cuenta_form', ['cuenta'=>null])
</div></div>
@endsection
@section('page-script')
<script>window.perfilCentroConfig={csrf:@json(csrf_token())};</script>
<script src="{{ asset('js/perfil-centro-medico.js') }}"></script>
@endsection
