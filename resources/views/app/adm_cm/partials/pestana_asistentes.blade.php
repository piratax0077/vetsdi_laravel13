{{-- Pestaña Asistentes dentro de Personal: misma tabla y botones de la página Administrador/Personal --}}
{{-- Igual que en Administrador/Personal, el administrador médico no registra empleados --}}
@php($esAdministradorMedico = Auth::user()->roles()->where('name', 'AdministradorMedico')->exists())
<!--Tab asistentes-->
<div class="tab-pane fade" id="asistentes" role="tabpanel" aria-labelledby="asistentes-tab">
    <div class="row mb-n4">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header encabezado-card">
                    <h5 class="encabezado-card-titulo"><span class="encabezado-card-icono"><i class="feather icon-users"></i></span>Asistentes</h5>
                    <div class="encabezado-card-acciones">
                        <a href="{{ route('asistentes.autorizaciones') }}" class="btn btn-info btn-sm"><i class="feather icon-shield mr-1"></i>Autorizaciones</a>
                        <div class="btn-group">
                        @if(!$esAdministradorMedico)
                        <button type="button" class="btn btn-info btn-sm" onclick="registrar_personal();"><i class="feather icon-plus"></i> Registrar nuevo/a empleado/a</button>
                        <button type="button" class="btn btn-info btn-sm dropdown-toggle dropdown-toggle-split" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><span class="sr-only">Toggle Dropdown</span></button>
                        @endif
                        <div class="dropdown-menu">
                        <button class="dropdown-item" type="button" class="btn  btn-primary" onclick="Asociar_personal();">Asociar asistente</button>
                        </div>
                        </div>
                    </div>
                </div>
                <div class="card-body" id="contenedor_asistentes_personal">
                    <table id="asistentes_personal" class="display table table-striped dt-responsive nowrap" style="width:100%">
                        <thead>
                            <tr>
                                <th class="align-middle">Nombre / Rut</th>
                                <th class="align-middle">Tipo</th>
                                <th class="align-middle">Sucursales</th>
                                <th class="align-middle">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if($lista_asistente)
                                @foreach ( $lista_asistente as $asistente)
                                <tr>
                                    <td class="align-middle">
                                        <span><strong>{{ $asistente->nombres.' '.$asistente->apellido_uno.' '.$asistente->apellido_dos }}</strong></span><br>
                                        <span>{{ $asistente->rut }}</span>
                                    </td>
                                    <td class="align-middle">
                                        <span><strong>{{ $asistente->asistente_tipo->nombre }}</strong></span>
                                    </td>
                                    <td class="align-middle">
                                        {{ $asistente->direccion()->first()->direccion }} #{{ $asistente->direccion()->first()->numero_dir }}, {{ $asistente->direccion()->first()->ciudad()->first()->nombre }}
                                    </td>
                                    <td class="align-middle">
                                        <!--Botón Modal-->
                                        <button type="button" class="btn btn-info btn-sm btn-icon" onclick="contacto('asistente publico',{{ $asistente->id }});" data-toggle="tooltip" data-placement="top" title="Contacto"><i class="feather icon-phone"></i></button>
                                  
                                        <!--Botón Modal-->
                                        <button type="button" class="btn btn-success btn-sm btn-icon" onclick="datos_depositos('asistente publico',{{ $asistente->id_usuario }});" data-toggle="tooltip" data-placement="top" title="Datos Bancarios"><i class="feather icon-credit-card"></i></button>
                                        <!--Botón Modal-->
                                        <button type="button" class="btn btn-purple btn-sm btn-icon" onclick="horario_profesional_cm('{{ $asistente->asistente_tipo->nombre }}',{{ $asistente->id }}, {{ $institucion->id_lugar_atencion }});" data-toggle="tooltip" data-placement="top" title="Horario y Días de atención"><i class="feather icon-clock"></i></button>
                                 
                                        <!--Botón Modal-->
                                        <button type="button" class="btn btn-warning btn-sm btn-icon" onclick="roles_permisos({{ $asistente->asistente_tipo->id }}, {{ $asistente->id_usuario }}, '{{ $asistente->roles }}');" data-toggle="tooltip" data-placement="top" title="Ver"><i class="feather icon-settings"></i></button>
                            
                                        <button type="button" class="btn btn-info btn-xxs" onclick="editar_datos_asistente({{ $asistente->id }});"><i class="feather icon-edit"></i> Editar</button>
                                        @if($asistente->contrato !== null)
                                        <button type="button" class="btn btn-danger btn-xxs" onclick="modal_desactivar_asistente({{ $asistente->id}}, {{ $asistente->contrato->id }}, '{{ $asistente->nombres.' '.$asistente->apellido_uno.' '.$asistente->apellido_dos }}');"><i class="feather icon-x"></i> Desvincular</button>
                                        @endif
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

<!--Cierre: Tab asistentes-->
