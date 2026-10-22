{{-- Pestaña Asistentes dentro de Personal (antes era la página /Personal) --}}
@php
    // El administrador médico solo asocia asistentes; el registro de nuevos queda para el administrador general
    $puedeRegistrarAsistente = !Auth::user()->roles()->where('name', 'AdministradorMedico')->exists();
@endphp
<div class="tab-pane fade" id="asistentes" role="tabpanel">
    <div class="card">
        <div class="card-header encabezado-card">
            <h5 class="encabezado-card-titulo"><span class="encabezado-card-icono"><i class="feather icon-user-check"></i></span>Asistentes</h5>
            <div class="encabezado-card-acciones">
                <a href="{{ route('asistentes.autorizaciones') }}" class="btn btn-info btn-sm"><i class="feather icon-shield mr-1"></i>Autorizaciones</a>
                @if($puedeRegistrarAsistente)
                    <button type="button" class="btn btn-info btn-sm" onclick="registrar_personal();"><i class="feather icon-plus mr-1"></i>Registrar asistente</button>
                @endif
                <button type="button" class="btn btn-info btn-sm" onclick="Asociar_personal();"><i class="feather icon-link mr-1"></i>Asociar asistente</button>
            </div>
        </div>
        <div class="card-body" id="contenedor_asistentes_personal">
            <div class="table-responsive">
                <table id="asistentes_personal" class="display table table-striped dt-responsive nowrap" style="width:100%">
                    <thead>
                        <tr>
                            <th class="align-middle">Nombre / RUT</th>
                            <th class="align-middle">Tipo</th>
                            <th class="align-middle">Sucursales</th>
                            <th class="align-middle">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($lista_asistente ?? [] as $asistente)
                            @php
                                $direccionAsistente = $asistente->direccion()->first();
                                $ciudadAsistente = $direccionAsistente?->ciudad()->first();
                                $nombreAsistente = $asistente->nombres.' '.$asistente->apellido_uno.' '.$asistente->apellido_dos;
                            @endphp
                            <tr>
                                <td class="align-middle">
                                    <span><strong>{{ $nombreAsistente }}</strong></span><br>
                                    <span>{{ $asistente->rut }}</span>
                                </td>
                                <td class="align-middle">
                                    <span><strong>{{ $asistente->asistente_tipo->nombre }}</strong></span>
                                </td>
                                <td class="align-middle">
                                    @if($direccionAsistente)
                                        {{ $direccionAsistente->direccion }}{{ $direccionAsistente->numero_dir ? ' #'.$direccionAsistente->numero_dir : '' }}{{ $ciudadAsistente ? ', '.$ciudadAsistente->nombre : '' }}
                                    @else
                                        <span class="text-muted">Sin direcci&oacute;n registrada</span>
                                    @endif
                                </td>
                                <td class="align-middle">
                                    <button type="button" class="btn btn-info btn-sm btn-icon" onclick="contacto('asistente publico',{{ $asistente->id }});" data-toggle="tooltip" data-placement="top" title="Contacto"><i class="feather icon-phone"></i></button>
                                    <button type="button" class="btn btn-success btn-sm btn-icon" onclick="datos_depositos('asistente publico',{{ $asistente->id_usuario }});" data-toggle="tooltip" data-placement="top" title="Datos bancarios"><i class="feather icon-credit-card"></i></button>
                                    <button type="button" class="btn btn-purple btn-sm btn-icon" onclick="horario_profesional_cm('{{ $asistente->asistente_tipo->nombre }}',{{ $asistente->id }}, {{ $institucion->id_lugar_atencion }});" data-toggle="tooltip" data-placement="top" title="Horario y días de atención"><i class="feather icon-clock"></i></button>
                                    @if($asistente->asistente_tipo->id)
                                        <button type="button" class="btn btn-warning btn-sm btn-icon" onclick="roles_permisos({{ $asistente->asistente_tipo->id }}, {{ $asistente->id_usuario }}, '{{ $asistente->roles }}');" data-toggle="tooltip" data-placement="top" title="Roles y permisos"><i class="feather icon-settings"></i></button>
                                    @endif
                                    <button type="button" class="btn btn-info btn-xxs" onclick="editar_datos_asistente({{ $asistente->id }});"><i class="feather icon-edit"></i> Editar</button>
                                    @if($asistente->contrato !== null)
                                        <button type="button" class="btn btn-danger btn-sm btn-icon" onclick="modal_desactivar_asistente({{ $asistente->id }}, {{ $asistente->contrato->id }}, {{ \Illuminate\Support\Js::from($nombreAsistente) }});" data-toggle="tooltip" data-placement="top" title="Desasociar"><i class="feather icon-x"></i></button>
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
