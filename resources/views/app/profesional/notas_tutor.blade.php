{{-- Notas internas del tutor: contador, formulario y registro de notas (pestaña de la pantalla del tutor).
     Las guarda guardar_nota_tutor(); con tres conflictivas se abre el modal para bloquearlo --}}
@php
    $vecesConflictivo = $notasTutor->where('conflictivo', true)->count();
@endphp
<div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-start">
    <div class="mr-2">
        <h5 class="mb-0 text-dark">Notas internas del tutor</h5>
        <p class="text-muted font-weight-normal mb-0 mt-1">Solo las ves tú; el tutor no las ve.</p>
    </div>
    <span class="badge badge-light-danger f-12 px-2 py-1 mt-1 ml-auto" title="Notas que lo marcaron como conflictivo">
        <i class="feather icon-alert-triangle mr-1" aria-hidden="true"></i><strong class="js-veces-conflictivo">{{ $vecesConflictivo }}</strong>
        <span class="js-veces-conflictivo-texto">{{ $vecesConflictivo === 1 ? 'vez conflictivo' : 'veces conflictivo' }}</span>
    </span>
</div>
<div class="card-body">
<form id="form_nota_tutor" class="d-print-none" novalidate>
    <div class="form-row">
        <div class="col-md-6">
            <div class="form-group">
                <label class="floating-label-activo-sm" for="nota_tutor_texto">Nueva nota</label>
                <textarea class="form-control form-control-sm" id="nota_tutor_texto" name="nota" rows="3" maxlength="2000" placeholder="Ej.: Llegó tarde y fue agresivo con el equipo" required></textarea>
                <div class="invalid-feedback">Escribe la nota.</div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="bloque-estado mt-0 mb-3">
                <span class="d-block mb-2 font-weight-bold text-dark" id="pregunta_tutor_conflictivo">¿Tutor conflictivo?</span>
                <div class="custom-control custom-switch" role="group" aria-labelledby="pregunta_tutor_conflictivo">
                    <input type="checkbox" class="custom-control-input" id="nota_tutor_conflictivo" name="conflictivo" value="1">
                    <label class="custom-control-label" for="nota_tutor_conflictivo">No</label>
                </div>
            </div>
        </div>
    </div>
    <div class="text-right">
        <button type="submit" class="btn btn-info" id="boton_agregar_nota_tutor"><i class="feather icon-plus mr-1"></i>Agregar nota</button>
    </div>
</form>

<h6 class="text-dark font-weight-bold mt-4 mb-2">Historial de notas internas</h6>
<div @class(["ficha-profesional__vacio", "d-none" => $notasTutor->isNotEmpty()]) id="notas_tutor_vacio">Todavía no hay notas para este tutor.</div>
<div @class(["table-responsive", "d-none" => $notasTutor->isEmpty()]) id="tabla_notas_tutor">
    <table class="table table-striped table-xs mb-0">
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Nota</th>
                <th>Conflictivo</th>
            </tr>
        </thead>
        <tbody id="lista_notas_tutor">
            @foreach ($notasTutor as $nota)
                <tr>
                    <td class="text-nowrap">{{ $nota->created_at->format("d-m-Y H:i") }}</td>
                    <td style="white-space: pre-line;">{{ $nota->nota }}</td>
                    <td>
                        @if ($nota->conflictivo)
                            <span class="badge badge-light-danger">Sí</span>
                        @else
                            <span class="badge badge-light-secondary">No</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
</div>
