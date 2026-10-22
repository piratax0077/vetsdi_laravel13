{{-- Pestaña Contratos dentro de Personal (antes era la página /Administracion/Contratos) --}}
<div class="tab-pane fade" id="contratos" role="tabpanel">
    <div class="card">
        <div class="card-header encabezado-card">
            <h5 class="encabezado-card-titulo"><span class="encabezado-card-icono"><i class="feather icon-file-text"></i></span>Profesionales contratados</h5>
            <div class="encabezado-card-acciones">
                <button type="button" class="btn btn-info btn-sm" data-toggle="modal" data-target="#registrar_contratoprofesional"><i class="feather icon-plus mr-1"></i>Registrar contrato</button>
                <button type="button" class="btn btn-info btn-sm" onclick="asociar_profesional();"><i class="feather icon-link mr-1"></i>Asociar otros profesionales</button>
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
                                    @if($profesional->contrato !== null)
                                        @if(!$profesional->es_convenio)
                                            <button type="button" class="btn btn-info btn-xxs" onclick="editar_datosprofesionalc({{ $profesional->id }});"><i class="feather icon-edit"></i> Editar</button>
                                        @else
                                            <button type="button" class="btn btn-info btn-xxs" onclick="editar_datosprofesionalc_convenio({{ $profesional->id }});"><i class="feather icon-edit"></i> Editar</button>
                                        @endif
                                        <button type="button" class="btn btn-danger btn-sm btn-icon" onclick="modal_desactivar_profesional({{ $profesional->id }}, {{ $profesional->contrato->id }}, {{ \Illuminate\Support\Js::from($nombreProfesional) }});" data-toggle="tooltip" data-placement="top" title="Desasociar"><i class="feather icon-x"></i></button>
                                    @else
                                        <button type="button" class="btn btn-info btn-sm btn-icon" onclick="contacto_profesional_contrato({{ $profesional->id }});" data-toggle="tooltip" data-placement="top" title="Contacto"><i class="feather icon-phone"></i></button>
                                        <button type="button" class="btn btn-success btn-sm btn-icon" onclick="datoscuenta({{ $profesional->id }});" data-toggle="tooltip" data-placement="top" title="Depositar"><i class="fas fa-hand-holding-usd"></i></button>
                                        <button type="button" class="btn btn-danger btn-sm btn-icon" onclick="modal_desactivar_profesional({{ $profesional->id }}, 0, {{ \Illuminate\Support\Js::from($nombreProfesional) }});" data-toggle="tooltip" data-placement="top" title="Desasociar"><i class="feather icon-x"></i></button>
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
