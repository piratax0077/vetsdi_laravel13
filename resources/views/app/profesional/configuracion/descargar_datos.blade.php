<div class="tab-pane fade {{ $errors->has('rut_tutor') ? 'show active' : '' }}" id="config-prof-descarga" role="tabpanel" aria-labelledby="config-prof-descarga-tab">
    <div class="card"><div class="card-header encabezado-card"><div>
        <h5 class="encabezado-card-titulo"><span class="encabezado-card-icono"><i class="feather icon-download"></i></span>Descargar mis pacientes y atenciones</h5>
        <p class="encabezado-card-subtitulo">Descargue un ZIP con tablas CSV compatibles con Excel de sus pacientes y registros de atención.</p>
    </div></div><div class="card-body descarga-datos">

        <!-- Qué trae el archivo -->
        <h6 class="descarga-datos-subtitulo">Datos incluidos</h6>
        <div class="descarga-datos-incluidos">
            <div class="descarga-datos-item">
                <span class="descarga-datos-icono"><i class="feather icon-users"></i></span>
                <div><strong>Pacientes y tutores</strong><span>Mascotas de sus atenciones y datos de contacto de sus tutores.</span></div>
            </div>
            <div class="descarga-datos-item">
                <span class="descarga-datos-icono"><i class="feather icon-calendar"></i></span>
                <div><strong>Agenda y fichas clínicas</strong><span>Horas y fichas registradas a su nombre en todos sus lugares de atención.</span></div>
            </div>
            <div class="descarga-datos-item">
                <span class="descarga-datos-icono"><i class="feather icon-briefcase"></i></span>
                <div><strong>Servicios</strong><span>Atenciones vinculadas a su perfil o registradas por usted.</span></div>
            </div>
            <div class="descarga-datos-item">
                <span class="descarga-datos-icono"><i class="feather icon-home"></i></span>
                <div><strong>Hospitalización</strong><span>Pacientes asignados en centros donde mantiene vínculo activo, con sus propios registros de atención, enfermería, cirugías y controles.</span></div>
            </div>
        </div>

        <div class="descarga-datos-aviso">
            <i class="feather icon-info"></i>
            <span>Incluye todos los períodos. No incluye las atenciones de otros profesionales ni un respaldo completo del centro. No contiene contraseñas ni información comercial. No es un respaldo SQL restaurable.</span>
        </div>

        <!-- Dos formas de descargar: todo, o solo los pacientes de un tutor -->
        <div class="descarga-datos-opciones">
            <form method="post" action="{{ route('vet.profesional.exportar_pacientes') }}" class="descarga-datos-opcion">@csrf
                <h6>Todos mis pacientes</h6>
                <p>Un solo archivo con todos sus pacientes y atenciones.</p>
                <button type="submit" class="btn btn-info"><i class="feather icon-download mr-1"></i>Descargar mis pacientes y atenciones</button>
            </form>
            <form method="post" action="{{ route('vet.profesional.exportar_pacientes') }}" class="descarga-datos-opcion">
                @csrf
                <input type="hidden" name="alcance" value="tutor">
                <h6>Solo un tutor</h6>
                <div class="form-group">
                    <label class="floating-label-activo-sm" for="exportacion_rut_tutor">RUT del tutor</label>
                    <input id="exportacion_rut_tutor" name="rut_tutor" type="text" maxlength="20"
                        value="{{ old('rut_tutor') }}" class="form-control form-control-sm @error('rut_tutor') is-invalid @enderror" required>
                    @error('rut_tutor')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <button type="submit" class="btn btn-info"><i class="feather icon-download mr-1"></i>Descargar pacientes y atenciones de este tutor</button>
            </form>
        </div>
    </div></div>
</div>
