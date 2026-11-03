<div class="card">
    <div class="card-header encabezado-card">
        <h5 class="encabezado-card-titulo"><span class="encabezado-card-icono"><i class="feather icon-settings"></i></span>Otros trabajadores contratados</h5>
        <div class="encabezado-card-acciones"><button type="button" class="btn btn-info btn-sm" data-otro-accion="contrato-nuevo" @disabled($otrosTrabajadores['trabajadores']->isEmpty())><i class="feather icon-plus mr-1"></i>Registrar contrato</button></div>
    </div>
    <div class="card-body"><div class="alert d-none" role="status" id="otros-trabajadores-resultado"></div><div class="table-responsive">
        <table id="tab_otros_contratos" class="display table table-striped dt-responsive nowrap" style="width:100%">
            <thead><tr><th>Nombre / RUT</th><th>Categoría</th><th>Tipo de contrato / Fecha</th><th>Remuneración mes</th><th>Acción</th></tr></thead><tbody>
            @forelse($otrosTrabajadores['contratos'] as $contrato)
            @php($trabajador = $otrosTrabajadores['trabajadores']->firstWhere('id',$contrato->trabajador_id))
            <tr>
                <td><strong>{{ $trabajador->nombre_completo }}</strong><br>{{ rut_formatear($trabajador->rut) ?? $trabajador->rut }}</td><td>{{ $trabajador->categoria }}</td>
                <td>{{ \App\Services\TrabajadoresCentroVeterinario::TIPOS_CONTRATO[$contrato->tipo] ?? $contrato->tipo }}<br>{{ $contrato->fecha_inicio }}{{ $contrato->fecha_fin ? ' a '.$contrato->fecha_fin : '' }}<br><span class="badge {{ $contrato->estado === 'vigente' ? 'badge-success' : 'badge-secondary' }}">{{ ucfirst($contrato->estado) }}</span>@if($contrato->fecha_termino)<br>Término: {{ $contrato->fecha_termino }}@endif</td>
                <td>${{ number_format($contrato->remuneracion,0,',','.') }}</td>
                <td><button type="button" class="btn btn-info btn-sm btn-icon" title="Información" aria-label="Información" data-otro-accion="info" data-id="{{ $trabajador->id }}"><i class="feather icon-info"></i></button>
                @if($contrato->estado === 'vigente')
                <button type="button" class="btn btn-info btn-sm btn-icon" title="Editar contrato" aria-label="Editar contrato" data-otro-accion="contrato" data-id="{{ $trabajador->id }}" data-contrato="{{ $contrato->id }}"><i class="feather icon-edit"></i></button>
                <button type="button" class="btn btn-danger btn-sm btn-icon" title="Finalizar contrato" aria-label="Finalizar contrato" data-otro-accion="terminar" data-id="{{ $trabajador->id }}" data-contrato="{{ $contrato->id }}"><i class="feather icon-x"></i></button>
                @endif</td>
            </tr>
            @empty<tr data-vacio><td colspan="5" class="text-center">Sin contratos de otros trabajadores. Registre primero el trabajador en Personal → Otros.</td></tr>@endforelse
            </tbody>
        </table>
    </div></div>
</div>
