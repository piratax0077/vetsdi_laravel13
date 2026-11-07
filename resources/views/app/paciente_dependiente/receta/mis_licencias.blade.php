@extends('template.paciente_dependiente.template')
@section('content')
<!--Container Completo-->
<div class="pcoded-main-container">
    <div class="pcoded-content">
        @include('template.partials.boton_volver', ['url' => route('paciente.dependiente.receta', ['id_dependiente_activo' => $id_dependiente_activo])])
        <!--Header-->
        <div class="row">
            <div class="col-md-12 mb-3">
                <h5 class="f-26 d-inline">Documentos</h5>
            </div>
        </div>
        <!--Cierre: Header-->
        <!--PDF generados-->
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="tabla_pdf_generados_mascota" class="display table table-striped table-hover table-sm nowrap" style="width:100%">
                                <thead>
                                    <tr>
                                        <th class="text-left align-middle">Fecha</th>
                                        <th class="text-left align-middle">Documento</th>
                                        <th class="text-left align-middle">Tipo</th>
                                        <th class="text-left align-middle">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($documentos as $doc)
                                        <tr>
                                            <td class="text-left align-middle" data-order="{{ $doc->created_at->format('Y-m-d H:i') }}">{{ $doc->created_at->format('d/m/Y H:i') }}</td>
                                            <td class="text-left align-middle">{{ $doc->nombre }}</td>
                                            <td class="text-left align-middle">{{ $doc->tipo }}</td>
                                            <td class="text-left align-middle">
                                                <a class="btn btn-info" href="{{ route('vet.pdf.detalle', ['documento' => $doc->id]) }}">Ver y preparar envío</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        {{ $documentos->links('pagination::bootstrap-4') }}
                    </div>
                </div>
            </div>
        </div>
        <!--Cierre: PDF generados-->
        @if ($historicos->isNotEmpty())
        <!--Documentos clínicos registrados-->
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header encabezado-card">
                        <h5 class="encabezado-card-titulo"><span class="encabezado-card-icono"><i class="feather icon-clipboard"></i></span>Documentos clínicos registrados</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted">Al abrir un documento se prepara su PDF para visualizarlo y compartirlo.</p>
                        <div class="table-responsive">
                            <table id="tabla_pdf_historicos_mascota" class="display table table-striped table-hover table-sm nowrap" style="width:100%">
                                <thead>
                                    <tr>
                                        <th class="text-left align-middle">Fecha</th>
                                        <th class="text-left align-middle">Tipo</th>
                                        <th class="text-left align-middle">Documento</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($historicos as $h)
                                        <tr>
                                            <td class="text-left align-middle">{{ $h['fecha'] ?: 'Sin fecha registrada' }}</td>
                                            <td class="text-left align-middle">{{ $h['titulo'] }}</td>
                                            <td class="text-left align-middle">
                                                <a class="btn btn-info" href="{{ route('vet.pdf.historico', ['tipo' => $h['tipo'], 'origen' => $h['id']]) }}">Preparar PDF</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--Cierre: Documentos clínicos registrados-->
        @endif
    </div>
</div>
<!--Cierre: Container Completo-->
@endsection
@section('page-script')
<script>
    $(document).ready(function () {
        var idioma = { emptyTable: 'No hay documentos registrados todavía.' };
        $('#tabla_pdf_generados_mascota, #tabla_pdf_historicos_mascota').DataTable({
            responsive: true,
            order: [[0, 'desc']],
            language: idioma,
        });
    });
</script>
@endsection
