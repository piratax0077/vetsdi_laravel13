@if ($responsablesAreasCentro->isNotEmpty())
<div class="table-responsive mb-3"><table class="table table-hover">
    <thead><tr><th>Área</th><th>Responsable</th><th>RUT</th><th>Contacto</th><th></th></tr></thead>
    <tbody>
    @foreach ($responsablesAreasCentro as $responsableArea)
        <tr><td>{{ $responsableArea->area_nombre }}</td>
            <td>{{ trim($responsableArea->nombre.' '.$responsableArea->apellido_uno.' '.$responsableArea->apellido_dos) }}</td>
            <td>{{ $responsableArea->rut ?: 'Sin registro' }}</td><td>{{ $responsableArea->email }}</td>
            <td><button type="button" class="btn btn-sm btn-outline-info" data-area="{{ $responsableArea->area }}" data-profesional="{{ $responsableArea->id_profesional }}" onclick="editarResponsableAreaCentro(this)">Editar</button></td></tr>
    @endforeach
    </tbody>
</table></div>
@endif
