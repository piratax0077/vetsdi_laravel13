<div class="tab-pane fade {{ $errors->has('rut_tutor') ? 'show active' : '' }}" id="config-prof-descarga" role="tabpanel" aria-labelledby="config-prof-descarga-tab">
    <div class="card"><div class="card-header encabezado-card"><h5 class="encabezado-card-titulo"><span class="encabezado-card-icono"><i class="feather icon-download"></i></span>Descargar mis pacientes y atenciones</h5></div><div class="card-body">
        <p>Descargue un ZIP con tablas CSV compatibles con Excel de sus pacientes y registros de atención.</p>
        <div class="table-responsive"><table class="table table-striped table-sm"><thead><tr><th>Datos incluidos</th><th>Alcance</th></tr></thead><tbody>
            <tr><td>Pacientes y tutores</td><td>Mascotas de sus atenciones y datos de contacto de sus tutores.</td></tr>
            <tr><td>Agenda y fichas clínicas</td><td>Horas y fichas registradas a su nombre en todos sus lugares de atención.</td></tr>
            <tr><td>Servicios</td><td>Atenciones vinculadas a su perfil o registradas por usted.</td></tr>
            <tr><td>Hospitalización</td><td>Pacientes asignados en centros donde mantiene vínculo activo, con sus propios registros de atención, enfermería, cirugías y controles.</td></tr>
        </tbody></table></div>
        <p class="text-muted">Incluye todos los períodos. No incluye las atenciones de otros profesionales ni un respaldo completo del centro. No contiene contraseñas ni información comercial. No es un respaldo SQL restaurable.</p>
        <form method="post" action="{{ route('vet.profesional.exportar_pacientes') }}">@csrf
            <button type="submit" class="btn btn-info"><i class="feather icon-download mr-1"></i>Descargar mis pacientes y atenciones</button>
        </form>
        <form method="post" action="{{ route('vet.profesional.exportar_pacientes') }}" class="mt-3">
            @csrf
            <input type="hidden" name="alcance" value="tutor">
            <div class="form-group">
                <label for="exportacion_rut_tutor">RUT del tutor</label>
                <input id="exportacion_rut_tutor" name="rut_tutor" type="text" maxlength="20"
                    value="{{ old('rut_tutor') }}" class="form-control form-control-sm @error('rut_tutor') is-invalid @enderror" required>
                @error('rut_tutor')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <button type="submit" class="btn btn-info"><i class="feather icon-download mr-1"></i>Descargar pacientes y atenciones de este tutor</button>
        </form>
    </div></div>
</div>
