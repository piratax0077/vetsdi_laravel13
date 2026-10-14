@extends('template.paciente_dependiente.template')
@section('content')
    <!--Container Completo-->
    <div class="pcoded-main-container">
        <div class="pcoded-content">
            @include('template.partials.boton_volver', ['url' => route('paciente.dependiente.receta', ['id_dependiente_activo' => $id_dependiente_activo])])
            <!--Header-->
            <div class="row">
            <div class="col-md-12 mb-3">
                <h5 class="f-26 d-inline">Mis recetas</h5>
            </div>
        </div>
        <!--Cierre: Header-->
            <div class="row">
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-sm-6 col-md-12">
                                    <table id="tabla_recetas_paciente_ro"
                                        class="display table table-striped table-hover dt-responsive nowrap table-sm"
                                        style="width:100%">
                                        <thead>
                                            <tr>
                                                <th class="text-left align-middle">Fecha</th>
                                                <th class="text-left align-middle">Profesional</th>
                                                <th class="text-left align-middle">Diagnóstico</th>
                                                <th class="text-left align-middle">Estado</th>
                                                <th class="text-left align-middle">Receta</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @if (isset($fichas))
                                                @foreach ($fichas as $f)
                                                    @foreach ($f->Recetas()->get() as $r)
                                                        <tr>
                                                            <td class="text-wrap text-left align-middle">
                                                                {{ \Carbon\Carbon::parse($r->created_at)->format('d/m/Y') }}
                                                            </td>
                                                            <td class="align-middle text-left">
                                                                <strong>{{ $f->Profesional()->first()->nombre }}
                                                                    {{ $f->Profesional()->first()->apellido_uno }}
                                                                    {{ $f->Profesional()->first()->apellido_dos }}
                                                                </strong>
                                                                <br>
                                                                {{ $f->Profesional()->first()->especialidad()->first()->txt_esp }}
                                                            </td>
                                                            <td class="align-middle text-left">{{ $f->diagnostico }}</td>
                                                            <td class="align-middle text-left">Enviado</td>
                                                            <td class="text-left align-middle">
                                                                <button href="#!" class="btn btn-danger btn-sm" data-toggle="modal" data-target="#m_cons_receta">
                                                                    <i class="feather icon-file-plus"></i> Ver Receta
                                                                </button>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                @endforeach
                                            @endif
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--Cierre: Container Completo-->
@endsection
@section('page-script')
    <script>
        $(document).ready(function() {
            $('#tabla_recetas_paciente_ro').DataTable({
                responsive: true,
            });
        });
    </script>
@endsection
