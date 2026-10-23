{{-- Pestaña Asistentes dentro de Personal (antes era la página /Personal) --}}
@php
    // El administrador médico solo vincula asistentes; el registro de nuevos queda para el administrador general
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
                <button type="button" class="btn btn-info btn-sm" onclick="Asociar_personal();"><i class="feather icon-link mr-1"></i>Vincular asistente</button>
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
                                $regionAsistente = $ciudadAsistente?->Region()->first();
                                // Si al asistente le cambiaron el tipo, su contrato sigue guardado con el tipo anterior;
                                // se busca el vigente por "ASISTENTE..." para que el horario lo encuentre igual
                                $tipoContratoAsistente = \App\Models\ContratoDependiente::where('id_empleado', $asistente->id)
                                    ->where('id_lugar_atencion', $institucion->id_lugar_atencion)
                                    ->where('estado', 2)
                                    ->where('tipo_empleado', 'like', 'ASISTENTE%')
                                    ->value('tipo_empleado');
                                // Todo lo que muestra el modal Información; cuentas y horario se consultan al abrirlo
                                $infoAsistente = [
                                    'id' => $asistente->id,
                                    'id_usuario' => $asistente->id_usuario,
                                    'id_tipo' => $asistente->asistente_tipo->id,
                                    'tipo' => $asistente->asistente_tipo->nombre,
                                    'tipo_contrato' => $tipoContratoAsistente,
                                    'nombre' => $nombreAsistente,
                                    'rut' => $asistente->rut,
                                    'sexo' => $asistente->sexo,
                                    'fecha_nacimiento' => $asistente->fecha_nac,
                                    'email' => $asistente->email,
                                    'telefono_uno' => $asistente->telefono_uno,
                                    'telefono_dos' => $asistente->telefono_dos,
                                    'region' => $regionAsistente?->nombre,
                                    'comuna' => $ciudadAsistente?->nombre,
                                    'direccion' => $direccionAsistente ? trim($direccionAsistente->direccion.($direccionAsistente->numero_dir ? ' #'.$direccionAsistente->numero_dir : '')) : null,
                                    'roles' => $asistente->roles,
                                    'id_lugar_atencion' => $institucion->id_lugar_atencion,
                                ];
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
                                    {{-- Contacto, cuentas bancarias, horario y roles quedaron juntos en el modal Información --}}
                                    <button type="button" class="btn btn-danger btn-sm btn-icon" onclick="abrir_info_asistente({{ \Illuminate\Support\Js::from($infoAsistente) }});" data-toggle="tooltip" data-placement="top" title="Información"><i class="feather icon-info"></i></button>
                                    <button type="button" class="btn btn-info btn-xxs" onclick="editar_datos_asistente({{ $asistente->id }});"><i class="feather icon-edit"></i> Editar</button>
                                    @if($asistente->contrato !== null)
                                        <button type="button" class="btn btn-danger btn-sm btn-icon" onclick="modal_desactivar_asistente({{ $asistente->id }}, {{ $asistente->contrato->id }}, {{ \Illuminate\Support\Js::from($nombreAsistente) }});" data-toggle="tooltip" data-placement="top" title="Desvincular"><i class="feather icon-x"></i></button>
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
