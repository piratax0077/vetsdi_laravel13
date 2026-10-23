@extends('template.adm_cm.template')
@section('content')

<!--****Container Completo****-->
<style>
    .select2-container--open{
        z-index: 9999999 !important;
    }
</style>
<div class="pcoded-main-container">
    <div class="pcoded-content">
        <div class="row">
            <div class="col-md-12 mb-3 encabezado-pagina">
                <div>
                    <h5 class="f-26 d-block mb-0">Contratos</h5>
                </div>
            </div>
        </div>

        {{-- Mismas pestañas que Personal: profesionales, asistentes y otros --}}
        <div class="row">
            <div class="col-md-12">
                <ul class="nav pill-moderno pill-moderno-white" role="tablist">
                    <li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#contratos_profesionales" role="tab">Profesionales veterinarios</a></li>
                    <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#contratos_asistentes" role="tab">Asistentes</a></li>
                    <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#contratos_otros" role="tab">Otros</a></li>
                </ul>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="tab-content">
                    <!--Contratos de profesionales veterinarios-->
                    <div class="tab-pane fade active show" id="contratos_profesionales" role="tabpanel">
                        {{-- Antes era la pestaña Contratos dentro de Personal; ahora se entra desde Accesos principales del escritorio --}}
                        <div class="card">
                            <div class="card-header encabezado-card">
                                <h5 class="encabezado-card-titulo"><span class="encabezado-card-icono"><i class="feather icon-file-text"></i></span>Profesionales contratados</h5>
                                <div class="encabezado-card-acciones">
                                    <button type="button" class="btn btn-info btn-sm" onclick="registrar_contrato_profesional();"><i class="feather icon-plus mr-1"></i>Registrar contrato</button>
                                    <button type="button" class="btn btn-info btn-sm" onclick="asociar_profesional();"><i class="feather icon-link mr-1"></i>Vincular otros profesionales</button>
                                </div>
                            </div>
                            <div class="card-body" id="card_body_profesionales_contratados">
                                <div class="table-responsive">
                                    <table id="tab_profesionales_cont_centroc" class="display table table-striped dt-responsive nowrap" style="width:100%">
                                        <thead>
                                            <tr>
                                                <th class="align-middle">Nombre / RUT</th>
                                                <th class="align-middle">Profesión</th>
                                                <th class="align-middle">Tipo de contrato / Fecha</th>
                                                <th class="align-middle">Remuneración mes</th>
                                                <th class="align-middle">Acción</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($profesionales_contratados ?? [] as $profesional)
                                                @php($nombreProfesional = $profesional->nombre.' '.$profesional->apellido_uno.' '.$profesional->apellido_dos)
                                                <tr>
                                                    <td class="align-middle">
                                                        <span><strong>{{ $nombreProfesional }}</strong></span><br>
                                                        <span>{{ $profesional->rut }}</span>
                                                    </td>
                                                    <td class="align-middle">
                                                        <span>{{ $profesional->especialidad }}</span><br>
                                                        <span>{{ $profesional->tipo_especialidad }}</span><br>
                                                        <span>{{ $profesional->sub_tipo_especialidad }}</span>
                                                    </td>
                                                    <td class="align-middle">
                                                        @if($profesional->contrato !== null)
                                                            <span>{{ $profesional->contrato->tipo_contrato == 1 ? 'INDEFINIDO' : '' }}</span><br>
                                                            <span>{{ $profesional->contrato->fecha_inicio }}</span>
                                                        @else
                                                            <span>Convenio</span>
                                                        @endif
                                                    </td>
                                                    <td class="align-middle">
                                                        @if($profesional->contrato !== null)
                                                            @if(!$profesional->es_convenio)
                                                                <span>{{ $profesional->horas_semanales }} horas semanales <br> ${{ number_format($profesional->contrato->monto_imponible, 0, ",", ".") }}</span>
                                                            @else
                                                                <span>${{ number_format($profesional->contrato->valor_fijo, 0, ",", ".") }}</span>
                                                            @endif
                                                        @else
                                                            <span>Contrato no definido</span>
                                                        @endif
                                                    </td>
                                                    <td class="align-middle">
                                                        {{-- Abre el mismo modal de registro con los datos cargados; si aún no tiene contrato, se crea al guardar --}}
                                                        <button type="button" class="btn btn-info btn-sm btn-icon" onclick="editar_contrato_profesional({{ $profesional->id }});" data-toggle="tooltip" data-placement="top" title="Editar contrato"><i class="feather icon-edit"></i></button>
                                                        @if($profesional->contrato !== null)
                                                            <button type="button" class="btn btn-danger btn-sm btn-icon" onclick="modal_desactivar_profesional({{ $profesional->id }}, {{ $profesional->contrato->id }}, {{ \Illuminate\Support\Js::from($nombreProfesional) }});" data-toggle="tooltip" data-placement="top" title="Desvincular"><i class="feather icon-x"></i></button>
                                                        @else
                                                            <button type="button" class="btn btn-info btn-sm btn-icon" onclick="contacto_profesional_contrato({{ $profesional->id }});" data-toggle="tooltip" data-placement="top" title="Contacto"><i class="feather icon-phone"></i></button>
                                                            <button type="button" class="btn btn-success btn-sm btn-icon" onclick="depositar_remuneracion({{ $profesional->id }});" data-toggle="tooltip" data-placement="top" title="Depositar"><i class="fas fa-hand-holding-usd"></i></button>
                                                            <button type="button" class="btn btn-danger btn-sm btn-icon" onclick="modal_desactivar_profesional({{ $profesional->id }}, 0, {{ \Illuminate\Support\Js::from($nombreProfesional) }});" data-toggle="tooltip" data-placement="top" title="Desvincular"><i class="feather icon-x"></i></button>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!--Contratos de asistentes: se registran y editan en el mismo modal que los de profesionales-->
                    <div class="tab-pane fade" id="contratos_asistentes" role="tabpanel">
                        <div class="card">
                            <div class="card-header encabezado-card">
                                <h5 class="encabezado-card-titulo"><span class="encabezado-card-icono"><i class="feather icon-user-check"></i></span>Asistentes contratados</h5>
                                <div class="encabezado-card-acciones">
                                    <button type="button" class="btn btn-info btn-sm" onclick="registrar_contrato_asistente();"><i class="feather icon-plus mr-1"></i>Registrar contrato</button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table id="tab_asistentes_contratos" class="display table table-striped dt-responsive nowrap" style="width:100%">
                                        <thead>
                                            <tr>
                                                <th class="align-middle">Nombre / RUT</th>
                                                <th class="align-middle">Tipo</th>
                                                <th class="align-middle">Tipo de contrato / Fecha</th>
                                                <th class="align-middle">Remuneración mes</th>
                                                <th class="align-middle">Acción</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($lista_asistente ?? [] as $asistente)
                                                @php($nombreAsistente = $asistente->nombres.' '.$asistente->apellido_uno.' '.$asistente->apellido_dos)
                                                @php($contratoAsistente = $asistente->contrato_vigente)
                                                <tr>
                                                    <td class="align-middle">
                                                        <span><strong>{{ $nombreAsistente }}</strong></span><br>
                                                        <span>{{ $asistente->rut }}</span>
                                                    </td>
                                                    <td class="align-middle">
                                                        <span>{{ $asistente->asistente_tipo->nombre }}</span>
                                                    </td>
                                                    <td class="align-middle">
                                                        @if($contratoAsistente)
                                                            {{-- En contratos de dependientes: 1 indefinido, 2 a plazo fijo --}}
                                                            <span>{{ $contratoAsistente->tipo_contrato == 1 ? 'INDEFINIDO' : 'PLAZO FIJO' }}</span><br>
                                                            <span>{{ $contratoAsistente->fecha_inicio }}</span>
                                                        @else
                                                            <span class="text-muted">Sin contrato vigente</span>
                                                        @endif
                                                    </td>
                                                    <td class="align-middle">
                                                        @if($contratoAsistente)
                                                            <span>${{ number_format((float) $contratoAsistente->monto_imponible, 0, ",", ".") }}</span>
                                                        @else
                                                            <span>Contrato no definido</span>
                                                        @endif
                                                    </td>
                                                    <td class="align-middle">
                                                        @if($contratoAsistente)
                                                            <button type="button" class="btn btn-info btn-sm btn-icon" onclick="editar_contrato_asistente({{ $asistente->id }}, {{ \Illuminate\Support\Js::from($contratoAsistente) }}, {{ \Illuminate\Support\Js::from(mb_strtoupper($asistente->asistente_tipo->nombre)) }});" data-toggle="tooltip" data-placement="top" title="Editar contrato"><i class="feather icon-edit"></i></button>
                                                        @else
                                                            {{-- Sin contrato vigente no hay nada que editar; el asistente se vuelve a contratar desde Registrar contrato --}}
                                                            <button type="button" class="btn btn-info btn-sm btn-icon" disabled data-toggle="tooltip" data-placement="top" title="Sin contrato vigente"><i class="feather icon-edit"></i></button>
                                                        @endif
                                                        <button type="button" class="btn btn-info btn-sm btn-icon" onclick="contacto('asistente', {{ $asistente->id }});" data-toggle="tooltip" data-placement="top" title="Contacto"><i class="feather icon-phone"></i></button>
                                                        <button type="button" class="btn btn-success btn-sm btn-icon" onclick="depositar_remuneracion({{ $asistente->id }}, 'asistente');" data-toggle="tooltip" data-placement="top" title="Depositar"><i class="fas fa-hand-holding-usd"></i></button>
                                                        @if($contratoAsistente)
                                                            <button type="button" class="btn btn-danger btn-sm btn-icon" onclick="modal_desactivar_asistente({{ $asistente->id }}, {{ $contratoAsistente->id }}, {{ \Illuminate\Support\Js::from($nombreAsistente) }});" data-toggle="tooltip" data-placement="top" title="Desvincular"><i class="feather icon-x"></i></button>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!--Contratos de otros trabajadores (mantención, limpieza, contabilidad y otras categorías)-->
                    <div class="tab-pane fade" id="contratos_otros" role="tabpanel">
                        <div class="card">
                            <div class="card-header encabezado-card">
                                <h5 class="encabezado-card-titulo"><span class="encabezado-card-icono"><i class="feather icon-settings"></i></span>Otros trabajadores contratados</h5>
                                <div class="encabezado-card-acciones">
                                    {{-- Pendiente para programación: igual que en Personal, todavía no hay registro de otros trabajadores --}}
                                    <button type="button" class="btn btn-info btn-sm" disabled><i class="feather icon-plus mr-1"></i>Registrar contrato</button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table id="tab_otros_contratos" class="table table-striped nowrap" style="width:100%">
                                        <thead>
                                            <tr>
                                                <th class="align-middle">Nombre / RUT</th>
                                                <th class="align-middle">Categoría</th>
                                                <th class="align-middle">Tipo de contrato / Fecha</th>
                                                <th class="align-middle">Remuneración mes</th>
                                                <th class="align-middle">Acción</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {{-- Pendiente para programación: listar los contratos de las otras categorías del centro --}}
                                            <tr>
                                                <td colspan="5" class="p-0 border-0">
                                                    <div class="aviso-sin-registros">
                                                        <span class="aviso-sin-registros-icono"><i class="feather icon-alert-circle"></i></span>
                                                        <div>
                                                            <p class="aviso-sin-registros-titulo">Sin registros</p>
                                                            <p class="aviso-sin-registros-texto">Aquí aparecerán los contratos de los trabajadores que no son profesionales ni asistentes, como mantención, limpieza o contabilidad. Se registrarán y editarán con el mismo formulario de contrato.</p>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
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
</div>
<!--****Cierre Container Completo****-->
@endsection

@section('page-script')
<script>
    $(function () {
        // Mismo buscador y paginación que las tablas de Personal
        $('#tab_profesionales_cont_centroc, #tab_asistentes_contratos').each(function () {
            if ($.fn.dataTable && !$.fn.dataTable.isDataTable(this)) {
                $(this).DataTable({ responsive: true });
            }
        });

        // La pestaña queda en la URL (?tab=) para volver a ella al recargar después de guardar
        const pestanasContratos = ['contratos_profesionales', 'contratos_asistentes', 'contratos_otros'];
        const tab = new URLSearchParams(window.location.search).get('tab');
        if (pestanasContratos.includes(tab)) {
            $('.pill-moderno a[href="#' + tab + '"]').first().tab('show');
        }

        $('.pill-moderno a[data-toggle="tab"]').not('.modal .pill-moderno a').on('shown.bs.tab', function (e) {
            const url = new URL(window.location.href);
            url.searchParams.set('tab', $(e.target).attr('href').substring(1));
            window.history.replaceState(null, '', url.toString());

            // Las tablas de pestañas ocultas se recalculan al mostrarse para que no queden descuadradas
            if ($.fn.dataTable) {
                $($.fn.dataTable.tables(true)).DataTable().columns.adjust().responsive.recalc();
            }
        });
    });
</script>
@include('app.adm_cm.partials.contratos_js')
@endsection

@section('modales-profesionales_inst')
    {{-- Vincular otros profesionales --}}
    @include('app.adm_cm.modal_adm.asociar_profesional')
    {{-- Contacto de profesionales con convenio y de asistentes (modal #contacto_usuario) --}}
    @include('app.adm_cm.modales.personal.contacto_personal')

    {{-- Registrar y editar contrato: un solo modal para profesionales y asistentes --}}
    @include('app.contabilidad.modals.datos_profesional')
    @include('app.adm_cm.modales.personal.depositar_remuneracion')
    @include('app.adm_cm.modales.personal.finalizar_contrato')
    @include('app.adm_cm.modales.personal.finalizar_personal')
    @include('app.adm_cm.amodales_nuevos.liquidacion_profesionales')
@endsection
