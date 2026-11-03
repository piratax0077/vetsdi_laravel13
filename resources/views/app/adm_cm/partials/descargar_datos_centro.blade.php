@if($puedeExportarPacientes ?? false)
@error('exportacion')
<div class="alert alert-info" role="status">{{ $message }}</div>
@enderror
<div class="tab-pane fade" id="descargar-datos-centro" role="tabpanel" aria-labelledby="descargar-datos-centro-tab">
    <div class="card"><div class="card-header encabezado-card"><h5 class="encabezado-card-titulo"><span class="encabezado-card-icono"><i class="feather icon-download"></i></span>Descargar pacientes y atenciones del centro</h5></div>
    <div class="card-body">
        <p>Descargue los datos de <strong>{{ $institucion->nombre }}</strong> y sus sucursales en un archivo ZIP con tablas CSV, compatibles con Excel.</p>
        <div class="table-responsive"><table class="table table-striped table-sm"><thead><tr><th>Datos incluidos</th><th>Contenido</th></tr></thead><tbody>
            <tr><td>Pacientes y tutores</td><td>Mascotas vinculadas al centro y datos de contacto de sus tutores.</td></tr>
            <tr><td>Agenda y atenciones</td><td>Horas y fichas de atención registradas en los lugares de atención del centro.</td></tr>
            <tr><td>Servicios</td><td>Reservas y atenciones de las áreas del centro.</td></tr>
            <tr><td>Hospitalización</td><td>Ingresos, altas, evoluciones, enfermería, cirugías y controles hospitalarios registrados.</td></tr>
        </tbody></table></div>
        <p class="text-muted">Incluye todos los períodos registrados. La descarga contiene tablas de datos; no es un respaldo SQL restaurable. No incluye contraseñas, usuarios, personal ni información comercial.</p>
        <form method="post" action="{{ route('vet.centro.exportar_pacientes') }}">@csrf
            <input type="hidden" name="id_institucion" value="{{ $institucion->id }}">
            <button type="submit" class="btn btn-info"><i class="feather icon-download mr-1"></i>Descargar ZIP de pacientes y atenciones</button>
        </form>
    </div></div>
</div>
@endif
