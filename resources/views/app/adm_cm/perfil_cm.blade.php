@extends('template.adm_cm.template')
@section('page-styles')
<link href="{{ asset('css/perfiles_usuarios.css') }}" rel="stylesheet">
@endsection
@section('content')
<div class="pcoded-main-container"><div class="pcoded-content">
    <div class="d-flex align-items-center justify-content-between mb-3"><h4>Perfil del centro médico</h4><a href="{{ route('adm_cm.configuracion') }}" class="btn btn-outline-info">Configuración del centro</a></div>
    @if($centros->count() > 1)
    <form method="get" action="{{ route('adm_cm.perfil_cm') }}" class="mb-3"><input type="hidden" name="tipo_institucion" value="{{ $tipo_institucion }}"><label>Centro</label><div class="d-flex"><select name="id_institucion" class="form-control mr-2">@foreach($centros as $centro)<option value="{{ $centro->id }}" @selected($centro->id === $institucion->id)>{{ $centro->nombre }}</option>@endforeach</select><button type="submit" class="btn btn-info">Abrir</button></div></form>
    @endif
    <div class="card"><div class="card-header bg-info"><h5 class="text-white mb-0">{{ $institucion->nombre }}</h5></div><div class="card-body">
        <img id="foto-centro" src="{{ storage_public_url($institucion->foto_perfil) ?: asset('images/iconos/cm-perfiles.png') }}" alt="Foto del centro" style="width:120px;height:120px;object-fit:cover;border-radius:12px" class="mb-3">
        <form action="{{ route('adm_cm.actualizar.foto') }}" method="post" enctype="multipart/form-data" data-centro-form="foto" class="mb-2">
            @csrf<input type="hidden" name="id_institucion" value="{{ $institucion->id }}"><input type="hidden" name="tipo_institucion" value="{{ $tipo_institucion }}">
            <div class="alert d-none" role="status" data-resultado></div><label>Foto del centro (máximo 5 MB)</label><input name="foto_perfil" type="file" accept="image/jpeg,image/png,image/gif,image/webp" class="form-control-file mb-2" required><button type="submit" class="btn btn-info btn-sm">Guardar foto</button>
        </form>
        <form action="{{ route('adm_cm.eliminar.foto') }}" method="post" data-centro-form="eliminar-foto">@csrf<input type="hidden" name="id_institucion" value="{{ $institucion->id }}"><input type="hidden" name="tipo_institucion" value="{{ $tipo_institucion }}"><div class="alert d-none" role="status" data-resultado></div><button type="submit" class="btn btn-outline-danger btn-sm">Eliminar foto</button></form>
    </div></div>
    <div class="card"><div class="card-header"><h5>Datos del centro y dirección</h5></div><div class="card-body">
        <form action="{{ route('adm_cm.editar_datos_perfil') }}" method="post" data-centro-form="centro">
            @csrf<input type="hidden" name="id_institucion" value="{{ $institucion->id }}"><input type="hidden" name="tipo_institucion" value="{{ $tipo_institucion }}"><div class="alert d-none" role="status" data-resultado></div>
            <div class="form-row">
                @foreach(['nombre'=>'Nombre del centro','razon_social'=>'Razón social','rut'=>'RUT','email'=>'Correo electrónico','telefono'=>'Teléfono','whatsapp'=>'WhatsApp','sitio_web'=>'Sitio web','certificado_supersalud'=>'Registro sanitario'] as $campo=>$etiqueta)
                <div class="form-group col-md-6"><label>{{ $etiqueta }}</label><input name="{{ $campo }}" class="form-control" type="{{ $campo === 'email' ? 'email' : ($campo === 'sitio_web' ? 'url' : 'text') }}" value="{{ $institucion->{$campo} }}" maxlength="255" @required(in_array($campo,['nombre','rut']))></div>
                @endforeach
                <div class="form-group col-12"><label>Horario de atención</label><textarea name="horario_atencion" class="form-control" maxlength="2000">{{ $institucion->horario_atencion }}</textarea></div>
            </div>
            @include('app.adm_cm.perfil.direccion',['persona'=>$institucion])
            <button type="submit" class="btn btn-info">Guardar datos del centro</button><button type="reset" class="btn btn-light">Cancelar cambios</button>
        </form>
    </div></div>
    <div class="card"><div class="card-header"><h5>Responsable administrativo</h5></div><div class="card-body">
        <form action="{{ route('adm_cm.editar_datos_perfil_responsable') }}" method="post" data-centro-form="responsable">
            @csrf<input type="hidden" name="id_institucion" value="{{ $institucion->id }}"><input type="hidden" name="tipo_institucion" value="{{ $tipo_institucion }}"><input type="hidden" name="id_responsable" value="{{ $responsable?->id }}"><div class="alert d-none" role="status" data-resultado></div>
            @include('app.adm_cm.perfil.persona',['persona'=>$responsable,'medico'=>false])
            <button type="submit" class="btn btn-info">Guardar responsable</button><button type="reset" class="btn btn-light">Cancelar cambios</button>
        </form>
    </div></div>
    @forelse($directores as $director)
    <div class="card"><div class="card-header"><h5>Responsable profesional: {{ $director->nombre }} {{ $director->apellido_uno }}</h5></div><div class="card-body">
        <form action="{{ route('adm_cm.editar_datos_perfil_responsable_medico') }}" method="post" data-centro-form="medico">
            @csrf<input type="hidden" name="id_institucion" value="{{ $institucion->id }}"><input type="hidden" name="tipo_institucion" value="{{ $tipo_institucion }}"><input type="hidden" name="id_responsable" value="{{ $director->id }}"><div class="alert d-none" role="status" data-resultado></div>
            @include('app.adm_cm.perfil.persona',['persona'=>$director,'medico'=>true])
            <button type="submit" class="btn btn-info">Guardar responsable profesional</button><button type="reset" class="btn btn-light">Cancelar cambios</button>
        </form>
    </div></div>
    @empty
    <p class="text-muted">No hay responsables profesionales asignados. Puede administrarlos en la configuración del centro.</p>
    @endforelse
    <div class="card"><div class="card-header"><h5>Contraseña de mi cuenta</h5></div><div class="card-body">
        <form action="{{ route('perfil.usuario.contrasena') }}" method="post" data-centro-form="contrasena">
            @csrf<div class="alert d-none" role="status" data-resultado></div>
            <div class="form-row"><div class="form-group col-md-4"><label>Contraseña actual</label><input type="password" name="contrasena_actual" class="form-control" autocomplete="current-password" required></div><div class="form-group col-md-4"><label>Nueva contraseña</label><input type="password" name="password_registro" class="form-control" autocomplete="new-password" minlength="8" required></div><div class="form-group col-md-4"><label>Confirmar contraseña</label><input type="password" name="password_confirmacion_registro" class="form-control" autocomplete="new-password" minlength="8" required></div></div>
            <button type="submit" class="btn btn-info">Guardar contraseña</button>
        </form>
    </div></div>
</div></div>
@endsection
@section('page-script')
<script>window.perfilCentroConfig={csrf:@json(csrf_token()),fotoPredeterminada:@json(asset('images/iconos/cm-perfiles.png'))};</script>
<script src="{{ asset('js/perfil-centro-medico.js') }}"></script>
@endsection
