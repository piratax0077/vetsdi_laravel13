@extends('template.paciente_dependiente.template')
@section('content')
<!--Container Completo-->
<div class="pcoded-main-container">
    <div class="pcoded-content">
        <!--Header-->
        <div class="row">
             <div class="col-md-12 mb-3 encabezado-pagina">
                <div>
                    <h5 class="f-26 d-block mb-0">Registro de vacunas</h5>
                </div>
            </div>
        </div>
        <!--Cierre: Header-->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        {{-- Las vacunas solo las registra el profesional; el tutor las consulta --}}
                        <div class="table-responsive">
                            <table id="tabla_recetas_paciente_ro"
                                        class="display table table-striped dt-responsive nowrap table-sm"
                                        style="width:100%">
                                        <thead>
                                            <tr>
                                                <th class="align-middle">Edad</th>
                                                <th class="align-middle">Fecha dosis</th>
                                                <th class="align-middle">Vacuna</th>
                                                <th class="align-middle">Próx. Dosis</th>
                                                <th class="align-middle">Realizado por</th>
                                            </tr>
                                        </thead>
                                        <tbody id="tabla_vacunas_body">
                                            @forelse(($vacunas ?? []) as $vacuna)
                                                <tr>
                                                    <td class="align-middle">{{ $vacuna['edad'] ?? '-' }}</td>
                                                    <td class="align-middle">
                                                        <span class="badge badge-secondary">
                                                            {{ !empty($vacuna['fecha_dosis']) ? \Carbon\Carbon::parse($vacuna['fecha_dosis'])->format('d-m-Y') : '-' }}
                                                        </span>
                                                    </td>
                                                    <td class="align-middle">{{ $vacuna['vacuna'] ?? '-' }}</td>
                                                    <td class="align-middle text-center">
                                                        <span class="badge badge-info">
                                                            {{ !empty($vacuna['proxima_dosis']) ? \Carbon\Carbon::parse($vacuna['proxima_dosis'])->format('d-m-Y') : '-' }}
                                                        </span>
                                                    </td>
                                                    <td class="align-middle">{{ $vacuna['realizado_por'] ?? '-' }}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td class="align-middle text-center text-muted" colspan="5">Sin registros</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
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
        function active_e(tipo_esp){
            if(tipo_esp=='all')
            {
                $('.filtro_le').removeClass('d-none');
            }else{
                $('.filtro_le').addClass('d-none');
                $('.le_'+tipo_esp).removeClass('d-none');
            }
        }
    </script>
@endsection
