@extends('template.profesional.template')
@section('content')
    <!--Container Completo-->
    <div class="pcoded-main-container">
        <div class="pcoded-content">
            <!--Header-->
            <div class="row">
                <div class="col-md-12 mb-2">
                    <h5 class="f-26 d-inline">Mis documentos e indicaciones</h5>
                </div>
            </div>
            <!--Cierre: Header-->
            <div class="row">
                <div class="col-sm-12">
                    <div class="card">
                         <div class="card-header">
                            <h4 class="text-c-blue f-20 d-inline ml-4 my-1 py-1">Mis documentos e indicaciones</h4>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-sm-6 col-md-12">
                                    <table id="tabla_recetas_paciente_ro"
                                        class="display table table-striped dt-responsive nowrap table-xs"
                                        style="width:100%">
                                        <thead>
                                            <tr>
                                                <th class="text-center align-middle">Fecha Registro</th>
                                                <th class="text-center align-middle">Paciente</th>
                                                <th class="text-center align-middle">F. Inicio</th>
                                                <th class="text-center align-middle">F. Termino</th>
                                                <th class="text-center align-middle">Diagnóstico</th>
                                                <th class="text-center align-middle">Lugar Atención</th>
                                                <th class="text-center align-middle">ver documento</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @if (isset($licencias))
                                                    {{-- @php
                                                    echo json_encode($licencias);
                                                    @endphp --}}
                                                @foreach ($licencias as $f)
                                                    <tr>
                                                        <td class="text-wrap text-center align-middle">{{ \Carbon\Carbon::parse($f->created_at)->format('d/m/Y') }}</td>
                                                        <td class="align-middle text-center">
                                                            {{ $f->paciente->nombres }} {{ $f->paciente->apellido_uno }} {{ $f->paciente->apellido_dos }}<br/>
                                                            {{ $f->paciente->rut }}
                                                        </td>
                                                        <td class="align-middle text-center">{{ $f->fecha_inicio }}</td>
                                                        <td class="align-middle text-center">{{ $f->fecha_termino }}</td>
                                                        <td class="align-middle text-center">{{ $f->descripcion_hipotesis }}</td>
                                                        <td class="align-middle text-center">{{ $f->LugarAtencion->nombre }}</td>

                                                        <td class="text-center align-middle">
                                                            <button href="#!" class="btn btn-danger-light-c btn-xxs" onclick="ver_pdf_licencia({{ $f->id }});"><i class="feather icon-file-plus"></i> Ver </button>
                                                        </td>
                                                    </tr>
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

        function ver_pdf_licencia(id)
        {
            Fancybox.show(
                [
                    {
                        src: "{{ route('paciente.licencia.pdf') }}?id_licencia="+id,
                        type: "iframe",
                        preload: false,
                    },
                ]
            );
        }
    </script>
@endsection
