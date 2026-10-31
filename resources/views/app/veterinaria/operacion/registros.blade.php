{{-- Formularios y tablas de cada sección; también los usa la vista de cada área. --}}
    @php
        // Las vistas de área abren sus formularios en modales; la operación del programador sigue en cards
        $formulariosEnModal = isset($areaCentro);
        // Estas áreas además dividen sus tablas en pestañas
        $vistaEnPestanas = in_array($areaCentro ?? null,\App\Services\OperacionVeterinaria::AREAS_EN_PESTANAS);
        $registroEnModal = $formulariosEnModal && $accion;
        // Los modales se abren solos al editar o cuando el servidor devolvió el formulario con errores
        $abrirModalRegistro = $registroEnModal && ($registro || old('_formulario') === $accion.'-nuevo');
        // Hospitalización muestra dos secciones en la misma página y cada una necesita su modal
        $idModalRegistro = $seccion === 'plazas' ? 'modal_registro_plazas' : 'modal_registro_area';
        $pestanaCatalogo = $vistaEnPestanas && (request('pestana') === 'catalogo' || ($servicioEditado ?? null) || str_starts_with((string) old('_formulario'),'servicio-'));
    @endphp
    @if($registroEnModal)
    <div id="{{ $idModalRegistro }}" class="modal fade modal-area-operacion" tabindex="-1" role="dialog" aria-labelledby="{{ $idModalRegistro }}_titulo" aria-hidden="true" data-abrir="{{ $abrirModalRegistro ? '1' : '0' }}" @if($registro) data-url-cerrar="{{ $urlSeccion() }}" @endif>
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header modal-header-purple">
                    <h5 class="modal-title" id="{{ $idModalRegistro }}_titulo">{{ $registro ? 'Editar registro' : 'Nuevo registro' }}</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">×</span></button>
                </div>
                @include('app.veterinaria.operacion.registro_contenido',['enModal'=>true])
            </div>
        </div>
    </div>
    @elseif($accion)
    <div class="card"><div class="card-header encabezado-card"><h5 class="encabezado-card-titulo"><span class="encabezado-card-icono"><i class="feather icon-edit"></i></span>{{ $registro ? 'Editar registro' : 'Nuevo registro' }}</h5></div><div class="card-body">
        @include('app.veterinaria.operacion.registro_contenido')
    </div></div>
    @endif
    @if($vistaEnPestanas)
    <ul class="nav pill-moderno pill-moderno-white" role="tablist" aria-label="Secciones de {{ $tituloArea }}">
        <li class="nav-item"><a class="nav-link {{ $pestanaCatalogo ? '' : 'active' }}" data-toggle="pill" href="#pestana_registros" role="tab">Registros del establecimiento</a></li>
        <li class="nav-item"><a class="nav-link {{ $pestanaCatalogo ? 'active' : '' }}" data-toggle="pill" href="#pestana_catalogo" role="tab">Catálogo de servicios</a></li>
    </ul>
    <div class="tab-content">
    <div class="tab-pane fade {{ $pestanaCatalogo ? '' : 'show active' }}" id="pestana_registros" role="tabpanel">
    @endif
    @if(count($columnas))
    <div class="card"><div class="card-header encabezado-card"><h5 class="encabezado-card-titulo">Registros del establecimiento</h5>@if($registroEnModal)<div class="encabezado-card-acciones"><button type="button" class="btn btn-info" data-toggle="modal" data-target="#{{ $idModalRegistro }}"><i class="feather icon-plus"></i> Agregar registro</button></div>@endif</div><div class="card-body">
        @if(in_array($seccion,['hospitalizacion','impuestos']) || isset(\App\Services\OperacionVeterinaria::SERVICIOS[$seccion]))
        <form method="get" class="form-row mb-3">
            <input type="hidden" name="id_institucion" value="{{ $institucion->id }}">
            <div class="col-md-3"><div class="form-group"><label class="floating-label-activo-sm">Estado</label><select name="estado" class="form-control form-control-sm"><option value="">Todos</option>@foreach(collect($campos)->firstWhere('nombre','estado')['opciones'] ?? [] as $key=>$label)<option value="{{ $key }}" @selected(request('estado') === $key)>{{ $label }}</option>@endforeach</select></div></div>
            @if($seccion !== 'hospitalizacion')
            <div class="col-md-3"><div class="form-group"><label class="floating-label-activo-sm">Desde</label><input name="desde" type="date" value="{{ request('desde') }}" class="form-control form-control-sm"></div></div><div class="col-md-3"><div class="form-group"><label class="floating-label-activo-sm">Hasta</label><input name="hasta" type="date" value="{{ request('hasta') }}" class="form-control form-control-sm"></div></div>
            @endif
            <div class="col-md-3 form-group"><button class="btn btn-info btn-sm" type="submit">Filtrar</button><a href="{{ $urlSeccion() }}" class="btn btn-light btn-sm">Limpiar</a></div>
        </form>
        @endif
        <div class="table-responsive"><table class="table table-striped table-sm"><thead><tr>@foreach($columnas as $key=>$label)<th>{{ $formulariosEnModal && $key === 'activa' ? 'Estado' : $label }}</th>@endforeach<th>Acciones</th></tr></thead><tbody>
        @forelse($filas as $fila)<tr>
            @foreach($columnas as $key=>$label)<td>
                @if($key === 'id_mascota'){{ $mascotas[$fila->{$key}] ?? '#'.$fila->{$key} }}
                @elseif($key === 'plaza_id'){{ $nombresPlazas[$fila->{$key}] ?? '#'.$fila->{$key} }}
                @elseif(in_array($key,['precio','monto']))${{ number_format($fila->{$key},0,',','.') }}
                @elseif($key === 'estado' && $formulariosEnModal && isset(\App\Services\OperacionVeterinaria::SERVICIOS[$seccion]))<span class="badge estado-agenda estado-agenda--{{ $fila->estado }}">{{ collect($campos)->firstWhere('nombre','estado')['opciones'][$fila->estado] ?? $fila->estado }}</span>
                @elseif($key === 'activa' && $formulariosEnModal)<span class="badge {{ $fila->activa ? 'badge-light-success' : 'badge-light-secondary' }}">{{ $fila->activa ? 'Habilitada' : 'Deshabilitada' }}</span>@if(in_array($fila->id,$ocupadas ?? [])) <span class="badge badge-light-warning">Ocupada</span>@endif
                @elseif($key === 'activa'){{ $fila->activa ? 'Sí' : 'No' }}{{ in_array($fila->id,$ocupadas ?? []) ? ' · Ocupada' : '' }}
                @else{{ $sinSegundos($fila->{$key}) ?: '—' }}@endif
            </td>@endforeach
            <td>@if($formulariosEnModal)<a class="btn btn-warning btn-icon abrir-edicion-area" data-plantilla="{{ $idModalRegistro }}_{{ $fila->id }}" href="{{ $urlSeccion(['editar'=>$fila->id]) }}" title="Ver / editar" aria-label="Ver / editar"><i class="feather icon-edit"></i></a>@else<a class="btn btn-outline-info btn-sm" href="{{ $urlSeccion(['editar'=>$fila->id]) }}">Ver / editar</a>@endif
                @if(in_array($seccion,['hospitalizacion','asistentes']))<a class="btn btn-light btn-sm" href="{{ $urlSeccion(['detalle'=>$fila->id]) }}">{{ $seccion === 'hospitalizacion' ? 'Evoluciones' : 'Candidatos' }}</a>@endif
            </td>
        </tr>@empty<tr><td colspan="{{ count($columnas)+1 }}" class="text-center text-muted">No hay registros para esta selección.</td></tr>@endforelse
        </tbody></table></div>
        {{ $filas->links('pagination::bootstrap-4') }}
    </div></div>
    {{-- La edición de cada fila ya viene en la página, para que el modal abra sin esperar al servidor --}}
    @if($registroEnModal)
    @foreach($filas as $fila)
    <template id="{{ $idModalRegistro }}_{{ $fila->id }}">
        <div class="modal fade modal-area-edicion" tabindex="-1" role="dialog" aria-label="Editar registro" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header modal-header-purple">
                        <h5 class="modal-title">Editar registro</h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">×</span></button>
                    </div>
                    @include('app.veterinaria.operacion.registro_contenido',['enModal'=>true,'registro'=>$fila])
                </div>
            </div>
        </div>
    </template>
    @endforeach
    @endif
    @endif
    @if($seccion === 'hospitalizacion' && ($detalle ?? null))
    <div class="card"><div class="card-header encabezado-card"><h5 class="encabezado-card-titulo">Internación #{{ $detalle->id }} · {{ $mascotas[$detalle->id_mascota] ?? '#'.$detalle->id_mascota }}</h5>@if($formulariosEnModal && $detalle->estado === 'hospitalizada')<div class="encabezado-card-acciones"><button type="button" class="btn btn-info" data-toggle="modal" data-target="#modal_evolucion_area"><i class="feather icon-plus"></i> Agregar evolución</button></div>@endif</div><div class="card-body">
        <p><strong>Ingreso:</strong> {{ $sinSegundos($detalle->ingreso) }} · <strong>Estado:</strong> {{ $detalle->estado }} · <strong>Responsable:</strong> {{ $detalle->responsable }}</p>
        <p><strong>Motivo:</strong> {{ $detalle->motivo }}</p><p><strong>Indicaciones:</strong> {{ $detalle->indicaciones }}</p>
        @if($detalle->egreso)<p><strong>Egreso:</strong> {{ $sinSegundos($detalle->egreso) }} · {{ $detalle->observaciones_alta }}</p>@endif
        @if($detalle->estado === 'hospitalizada' && !$formulariosEnModal)
        <h6 class="mb-3">Agregar evolución</h6>
        @include('app.veterinaria.operacion.formulario',['accionForm'=>'evolucion','registroForm'=>null,'camposForm'=>$camposEvolucion,'ocultos'=>['internacion_id'=>$detalle->id],'valores'=>['fecha'=>now()->format('Y-m-d\TH:i')]])
        @endif
        <div class="table-responsive mt-3"><table class="table table-sm"><thead><tr><th>Fecha</th><th>Peso kg</th><th>Temperatura °C</th><th>Evolución</th><th>Registrado por</th></tr></thead><tbody>@forelse($evoluciones as $e)<tr><td>{{ $sinSegundos($e->fecha) }}</td><td>{{ $e->peso ?? '—' }}</td><td>{{ $e->temperatura ?? '—' }}</td><td class="text-wrap">{{ $e->observaciones }}</td><td>Usuario #{{ $e->id_usuario }}</td></tr>@empty<tr><td colspan="5">Sin evoluciones registradas.</td></tr>@endforelse</tbody></table></div>
    </div></div>
    @if($formulariosEnModal && $detalle->estado === 'hospitalizada')
    <div id="modal_evolucion_area" class="modal fade modal-area-operacion" tabindex="-1" role="dialog" aria-labelledby="modal_evolucion_area_titulo" aria-hidden="true" data-abrir="{{ old('_formulario') === 'evolucion-nuevo' ? '1' : '0' }}">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header modal-header-purple">
                    <h5 class="modal-title" id="modal_evolucion_area_titulo">Agregar evolución</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">×</span></button>
                </div>
                @include('app.veterinaria.operacion.formulario',['accionForm'=>'evolucion','registroForm'=>null,'camposForm'=>$camposEvolucion,'ocultos'=>['internacion_id'=>$detalle->id],'valores'=>['fecha'=>now()->format('Y-m-d\TH:i')],'enModal'=>true])
            </div>
        </div>
    </div>
    @endif
    @endif
    @if($vistaEnPestanas)
    </div>
    <div class="tab-pane fade {{ $pestanaCatalogo ? 'show active' : '' }}" id="pestana_catalogo" role="tabpanel">
    @endif
    @if(isset($catalogo))
    <div class="card"><div class="card-header encabezado-card"><h5 class="encabezado-card-titulo">Catálogo de servicios · {{ $titulo }}</h5>@if($vistaEnPestanas)<div class="encabezado-card-acciones"><button type="button" class="btn btn-info" data-toggle="modal" data-target="#modal_servicio_area"><i class="feather icon-plus"></i> Agregar servicio</button></div>@endif</div><div class="card-body">
        <div class="table-responsive"><table class="table table-sm"><thead><tr><th>Servicio</th><th>Duración</th><th>Precio CLP</th><th>{{ $formulariosEnModal ? 'Estado' : 'Activo' }}</th><th>Acciones</th></tr></thead><tbody>@forelse($catalogo as $c)<tr><td>{{ $c->nombre }}</td><td>{{ $c->duracion_minutos }} min</td><td>${{ number_format($c->precio,0,',','.') }}</td><td>@if($formulariosEnModal)<span class="badge {{ $c->activo ? 'badge-light-success' : 'badge-light-secondary' }}">{{ $c->activo ? 'Activo' : 'Inactivo' }}</span>@else{{ $c->activo ? 'Sí' : 'No' }}@endif</td><td>@if($formulariosEnModal)<a class="btn btn-warning btn-icon abrir-edicion-area" data-plantilla="modal_servicio_area_{{ $c->id }}" href="{{ $urlSeccion(['catalogo'=>$c->id]) }}" title="Editar" aria-label="Editar"><i class="feather icon-edit"></i></a>@else<a class="btn btn-outline-info btn-sm" href="{{ $urlSeccion(['catalogo'=>$c->id]) }}">Editar</a>@endif</td></tr>@empty<tr><td colspan="5">Agregue el primer servicio para habilitar reservas.</td></tr>@endforelse</tbody></table></div>
        @unless($vistaEnPestanas)
        <h6 class="mb-3">{{ $servicioEditado ? 'Editar servicio' : 'Agregar servicio' }}</h6>
        @include('app.veterinaria.operacion.formulario',['accionForm'=>'servicio','registroForm'=>$servicioEditado,'camposForm'=>$camposServicio,'ocultos'=>['modulo'=>$seccion],'valores'=>['duracion_minutos'=>30,'precio'=>0,'activo'=>1]])
        @endunless
    </div></div>
    @if($vistaEnPestanas)
    @foreach($catalogo as $c)
    <template id="modal_servicio_area_{{ $c->id }}">
        <div class="modal fade modal-area-edicion" tabindex="-1" role="dialog" aria-label="Editar servicio" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header modal-header-purple">
                        <h5 class="modal-title">Editar servicio</h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">×</span></button>
                    </div>
                    @include('app.veterinaria.operacion.formulario',['accionForm'=>'servicio','registroForm'=>$c,'camposForm'=>$camposServicio,'ocultos'=>['modulo'=>$seccion],'valores'=>[],'enModal'=>true])
                </div>
            </div>
        </div>
    </template>
    @endforeach
    @endif
    @endif
    @if($vistaEnPestanas)
    </div>
    </div>
    {{-- Fuera de las pestañas: un modal dentro de una pestaña oculta no se vería --}}
    <div id="modal_servicio_area" class="modal fade modal-area-operacion" tabindex="-1" role="dialog" aria-labelledby="modal_servicio_area_titulo" aria-hidden="true" data-abrir="{{ ($servicioEditado || old('_formulario') === 'servicio-nuevo') ? '1' : '0' }}" @if($servicioEditado) data-url-cerrar="{{ $urlSeccion(['pestana'=>'catalogo']) }}" @endif>
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header modal-header-purple">
                    <h5 class="modal-title" id="modal_servicio_area_titulo">{{ $servicioEditado ? 'Editar servicio' : 'Agregar servicio' }}</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">×</span></button>
                </div>
                @include('app.veterinaria.operacion.formulario',['accionForm'=>'servicio','registroForm'=>$servicioEditado,'camposForm'=>$camposServicio,'ocultos'=>['modulo'=>$seccion],'valores'=>['duracion_minutos'=>30,'precio'=>0,'activo'=>1],'enModal'=>true])
            </div>
        </div>
    </div>
    @endif
