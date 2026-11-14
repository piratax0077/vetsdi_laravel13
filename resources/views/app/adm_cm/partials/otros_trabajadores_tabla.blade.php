<div class="card">
    <div class="card-header encabezado-card">
        <h5 class="encabezado-card-titulo"><span class="encabezado-card-icono"><i class="feather icon-settings"></i></span>Otros trabajadores</h5>
        <div class="encabezado-card-acciones">
            <button type="button" class="btn btn-outline-info btn-sm" data-otro-accion="categorias">Categorías</button>
            <button type="button" class="btn btn-info btn-sm" data-otro-accion="nuevo"><i class="feather icon-plus mr-1"></i>Agregar trabajador</button>
        </div>
    </div>
    <div class="card-body">
        <div class="alert d-none" role="status" id="otros-trabajadores-resultado"></div>
        <div class="table-responsive">
            <table id="otros_trabajadores_tabla" class="display table table-striped dt-responsive nowrap" style="width:100%">
                <thead><tr><th>Nombre / RUT</th><th>Categoría</th><th>Sucursales</th><th>Info</th><th>Mensaje</th></tr></thead>
                <tbody>
                    @forelse($otrosTrabajadores['trabajadores'] as $trabajador)
                    <tr>
                        <td class="align-middle"><strong>{{ $trabajador->nombre_completo }}</strong><br>{{ rut_formatear($trabajador->rut) ?? $trabajador->rut }}@if(!$trabajador->activo)<br><span class="badge badge-secondary">Desvinculado</span>@endif</td>
                        <td class="align-middle">{{ $trabajador->categoria }}</td>
                        <td class="align-middle">@foreach($trabajador->sucursales as $sucursal)<span>{{ $sucursal }}</span><br>@endforeach</td>
                        <td class="align-middle">
                            <button type="button" class="btn btn-primary btn-sm btn-icon" title="Información" aria-label="Información" data-otro-accion="info" data-id="{{ $trabajador->id }}"><i class="feather icon-eye"></i></button>
                            <button type="button" class="btn btn-warning btn-sm btn-icon" title="Editar" aria-label="Editar" data-otro-accion="editar" data-id="{{ $trabajador->id }}"><i class="feather icon-edit"></i></button>
                            <button type="button" class="btn btn-purple btn-sm btn-icon" title="Contrato" aria-label="Contrato" data-otro-accion="contrato" data-id="{{ $trabajador->id }}"><i class="feather icon-file-text"></i></button>
                            <button type="button" class="btn {{ $trabajador->activo ? 'btn-danger' : 'btn-success' }} btn-sm btn-icon" title="{{ $trabajador->activo ? 'Desvincular' : 'Reactivar' }}" aria-label="{{ $trabajador->activo ? 'Desvincular' : 'Reactivar' }}" data-otro-accion="estado" data-activo="{{ $trabajador->activo ? 0 : 1 }}" data-id="{{ $trabajador->id }}"><i class="feather {{ $trabajador->activo ? 'icon-user-minus' : 'icon-user-plus' }}"></i></button>
                        </td>
                        <td class="align-middle"><button type="button" class="btn btn-warning btn-sm btn-icon" title="Mensaje por correo" aria-label="Mensaje por correo" data-otro-accion="mensaje" data-id="{{ $trabajador->id }}" @disabled(!$trabajador->activo || !$trabajador->email)><i class="feather icon-mail"></i></button></td>
                    </tr>
                    @empty
                    <tr data-vacio><td colspan="5" class="text-center">Sin trabajadores registrados. Agregue personal de mantención, limpieza, contabilidad u otra categoría.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
