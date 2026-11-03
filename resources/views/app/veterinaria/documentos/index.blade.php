@extends('template.documentos_veterinarios')
@section('content')
<div class="card"><div class="card-header"><h5>PDF generados</h5></div><div class="card-body"><div class="table-responsive"><table class="table table-striped"><thead><tr><th>Fecha</th><th>Documento</th><th>Acciones</th></tr></thead><tbody>
@forelse($documentos as $doc)<tr><td>{{ $doc->created_at->format('d/m/Y H:i') }}</td><td>{{ $doc->nombre }}</td><td><a class="btn btn-outline-info btn-sm" href="{{ route('vet.pdf.detalle',['documento'=>$doc->id]) }}">Ver y preparar envío</a></td></tr>@empty<tr><td colspan="3">No hay PDF archivados todavía. Los documentos generados quedan disponibles aquí.</td></tr>@endforelse
</tbody></table></div>{{ $documentos->links('pagination::bootstrap-4') }}</div></div>
@if($historicos->isNotEmpty())
<div class="card"><div class="card-header"><h5>Documentos clínicos registrados</h5></div><div class="card-body"><p class="text-muted">Al abrir un documento se prepara su PDF para visualizarlo y compartirlo con el tutor.</p><div class="table-responsive"><table class="table table-striped"><thead><tr><th>Fecha</th><th>Tipo</th><th>Documento</th></tr></thead><tbody>@foreach($historicos as $h)<tr><td>{{ $h['fecha'] ?: 'Sin fecha registrada' }}</td><td>{{ $h['titulo'] }}</td><td><a class="btn btn-outline-info btn-sm" href="{{ route('vet.pdf.historico',['tipo'=>$h['tipo'],'origen'=>$h['id']]) }}">Preparar PDF</a></td></tr>@endforeach</tbody></table></div></div></div>
@endif
@endsection
