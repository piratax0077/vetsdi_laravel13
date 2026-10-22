@extends('app.peluqueria.layout')
@section('page-styles')<link rel="stylesheet" href="{{ asset('css/peluqueria-ficha.css') }}?v={{ filemtime(public_path('css/peluqueria-ficha.css')) }}">@endsection
@section('content')
@php
    $lectura = in_array($atencion->estado, ['finalizada','cancelada']);
    $valor = fn ($campo, $defecto = '') => old($campo, $datos[$campo] ?? $defecto);
@endphp
<main class="pelu-ficha">
    <header class="pelu-cabecera">
        <div><span class="pelu-etiqueta">{{ $perfil->nombre }}</span><h1><i class="feather icon-scissors" aria-hidden="true"></i> Peluquería canina y felina</h1><p>Ficha de atención · N.º {{ $atencion->id }} <span>· {{ \Carbon\Carbon::parse($atencion->inicio)->format('d/m/Y H:i') }}</span></p></div>
        <div class="pelu-acciones"><a href="{{ route('peluqueria.agenda.ver', ['fecha'=>\Carbon\Carbon::parse($atencion->inicio)->format('Y-m-d')]) }}">Volver a la agenda</a><button type="button" onclick="window.print()">Imprimir ficha</button></div>
    </header>
    <ul class="nav nav-pills mt-3 mb-3 pelu-ficha-pills" role="tablist" aria-label="Secciones de la ficha">
        <li class="nav-item"><a class="nav-link active" id="pelu-actual-tab" data-toggle="pill" href="#pelu-actual" role="tab" aria-controls="pelu-actual" aria-selected="true"><i class="feather icon-scissors" aria-hidden="true"></i> Atención actual</a></li>
        <li class="nav-item"><a class="nav-link" id="pelu-anteriores-tab" data-toggle="pill" href="#pelu-anteriores" role="tab" aria-controls="pelu-anteriores" aria-selected="false"><i class="feather icon-clock" aria-hidden="true"></i> Atenciones anteriores <span class="badge badge-light ml-1">{{ $anteriores->total() }}</span></a></li>
    </ul>
    <div class="tab-content">
    <div class="tab-pane fade show active" id="pelu-actual" role="tabpanel" aria-labelledby="pelu-actual-tab">
    @if(session('mensaje'))<p class="pelu-aviso" role="status">{{ session('mensaje') }}</p>@endif
    @if($errors->any())<div class="pelu-error" role="alert"><strong>Revisa los campos antes de guardar.</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    @if($lectura)<p class="pelu-aviso">Atención {{ $atencion->estado }}. Esta ficha está disponible para consulta.</p>@endif
    <form id="pelu-ficha-form" method="post" action="{{ route('peluqueria.ficha.guardar',$atencion->id) }}">
        @csrf
        <fieldset @disabled($lectura)>
        <div class="pelu-columnas">
            <section class="pelu-panel"><h2><span>01</span> Datos de la mascota</h2>
                <p class="pelu-tutor"><strong>Tutor:</strong> {{ $datos['tutor'] ?? $cliente->tutor }} · {{ $cliente->telefono }}</p>
                <div class="pelu-campos">
                    @foreach(['mascota'=>'Nombre de la mascota','especie'=>'Especie','raza'=>'Raza','edad'=>'Edad (años o meses)'] as $campo=>$etiqueta)
                    <label>{{ $etiqueta }}<input name="{{ $campo }}" value="{{ $valor($campo) }}" maxlength="{{ in_array($campo,['especie','edad'])?60:100 }}" @required(in_array($campo,['mascota','especie']))></label>
                    @endforeach
                    <label>Esterilizada/o<select name="esterilizada">@foreach(['desconocido'=>'Sin información','si'=>'Sí','no'=>'No'] as $id=>$texto)<option value="{{ $id }}" @selected($valor('esterilizada','desconocido')===$id)>{{ $texto }}</option>@endforeach</select></label>
                </div>
                <label>Alergias conocidas<textarea name="alergias" maxlength="2000" placeholder="Anota las alergias informadas por el tutor">{{ $valor('alergias') }}</textarea></label>
                <label>Alergias a productos de belleza<textarea name="alergias_productos" maxlength="2000" placeholder="Champús, perfumes u otros productos">{{ $valor('alergias_productos') }}</textarea></label>
            </section>
            <section class="pelu-panel"><h2><span>02</span> Comportamiento durante la atención</h2>
                <p class="pelu-ayuda">Marca las conductas observadas; puedes seleccionar más de una.</p>
                <div class="pelu-opciones">@foreach(\App\Http\Controllers\PeluqueriaController::COMPORTAMIENTOS as $opcion)
                    <label class="pelu-switch"><input role="switch" type="checkbox" name="comportamientos[]" value="{{ $opcion }}" @checked(in_array($opcion,$valor('comportamientos',[])))><span>{{ $opcion }}</span></label>
                @endforeach</div>
                <label>Otro comportamiento<textarea name="comportamiento_otro" maxlength="1000">{{ $valor('comportamiento_otro') }}</textarea></label>
            </section>
            <section class="pelu-panel"><h2><span>03</span> Servicios realizados</h2>
                @unless($lectura)
                <p class="pelu-ayuda">Selecciona los servicios configurados en tu escritorio y añádelos al cobro. <a href="{{ route('peluqueria.configuracion') }}" target="_blank" rel="noopener">Configurar servicios y valores</a></p>
                <div class="pelu-agregar"><label>Servicio<select id="pelu-servicio"><option value="">Seleccione un servicio</option>@foreach($procedimientos as $procedimiento)<option value="{{ $procedimiento->id }}" data-nombre="{{ $procedimiento->nombre }}" data-precio="{{ $procedimiento->precio }}">{{ $procedimiento->nombre }} — ${{ number_format($procedimiento->precio,0,',','.') }}</option>@endforeach</select></label><label>Precio ($ CLP)<input id="pelu-precio" type="number" readonly placeholder="Valor configurado"></label><button type="button" id="pelu-anadir" @disabled($procedimientos->isEmpty())>+ Añadir</button></div>
                @if($procedimientos->isEmpty())<p class="pelu-ayuda">No hay servicios activos. Agrégalos en el panel de configuración del escritorio.</p>@endif
                @endunless
                <p id="pelu-lista-error" class="pelu-error" role="alert" hidden></p>
                <div class="pelu-tabla"><table><thead><tr><th>Servicio</th><th>Precio ($ CLP)</th>@unless($lectura)<th>Acción</th>@endunless</tr></thead><tbody id="pelu-lista" data-lectura="{{ $lectura?'1':'0' }}">
                    @foreach($valor('servicios',[]) as $i=>$servicio)
                    <tr><td>@if(!empty($servicio['procedimiento_id']))<input type="hidden" name="servicios[{{ $i }}][procedimiento_id]" value="{{ $servicio['procedimiento_id'] }}">@endif<input aria-label="Nombre del servicio" name="servicios[{{ $i }}][nombre]" value="{{ $servicio['nombre'] }}" readonly required></td><td><input aria-label="Precio del servicio en pesos" type="number" name="servicios[{{ $i }}][precio]" value="{{ $servicio['precio'] }}" readonly required></td>@unless($lectura)<td><button type="button" class="pelu-quitar">Quitar</button></td>@endunless</tr>
                    @endforeach
                </tbody></table></div>
                <p class="pelu-total">Total a cobrar <strong id="pelu-total" aria-live="polite">${{ number_format(array_sum(array_column($valor('servicios',[]),'precio')),0,',','.') }}</strong></p>
                <label>Observaciones de los servicios<textarea name="observaciones_servicios" maxlength="3000">{{ $valor('observaciones_servicios') }}</textarea></label>
                <label>Productos utilizados<textarea name="productos_utilizados" maxlength="2000">{{ $valor('productos_utilizados') }}</textarea></label>
            </section>
            <section class="pelu-panel"><h2><span>04</span> Análisis visual</h2>
                <p class="pelu-ayuda">Registra los hallazgos observados durante el servicio.</p>
                <div class="pelu-opciones">@foreach(\App\Http\Controllers\PeluqueriaController::HALLAZGOS as $opcion)
                    <label class="pelu-switch"><input role="switch" type="checkbox" name="hallazgos[]" value="{{ $opcion }}" @checked(in_array($opcion,$valor('hallazgos',[])))><span>{{ $opcion }}</span></label>
                @endforeach</div>
                <label>Otros hallazgos<textarea name="hallazgos_otro" maxlength="2000">{{ $valor('hallazgos_otro') }}</textarea></label>
            </section>
        </div>
        <section class="pelu-panel"><h2><span>05</span> Observaciones generales</h2><label>Cuidados, recomendaciones o incidentes<textarea name="observaciones" rows="4" maxlength="5000">{{ $valor('observaciones') }}</textarea></label></section>
        @unless($lectura)<footer class="pelu-guardar"><span>Los servicios y su total quedarán registrados en el historial.</span><button name="accion" value="guardar">Guardar ficha</button><button class="pelu-finalizar" name="accion" value="finalizar">Guardar y finalizar atención</button></footer>@endunless
        </fieldset>
    </form>
    </div>
    <div class="tab-pane fade" id="pelu-anteriores" role="tabpanel" aria-labelledby="pelu-anteriores-tab">
        <section class="pelu-panel"><h2>Atenciones anteriores de {{ $cliente->mascota }}</h2>
            <div class="table-responsive"><table class="table table-sm table-striped"><thead><tr><th>Fecha</th><th>Servicio</th><th>Registro de la atención</th><th>Total cobrado</th><th>Ficha</th></tr></thead><tbody>
            @forelse($anteriores as $anterior)<tr><td>{{ \Carbon\Carbon::parse($anterior->inicio)->format('d/m/Y H:i') }}</td><td>{{ $anterior->servicio }}</td><td style="white-space:pre-wrap">{{ $anterior->registro }}</td><td>${{ number_format($anterior->valor,0,',','.') }}</td><td><button type="button" class="btn btn-sm btn-outline-info pelu-ver-ficha" data-url="{{ route('peluqueria.ficha',$anterior->id) }}">Ver ficha</button></td></tr>
            @empty<tr><td colspan="5" class="text-center text-muted py-4">Esta mascota no tiene atenciones anteriores finalizadas.</td></tr>@endforelse
            </tbody></table></div>{{ $anteriores->fragment('pelu-anteriores')->links() }}
        </section>
    </div></div>
</main>
<div class="modal fade" id="pelu-historial-modal" tabindex="-1" role="dialog" aria-modal="true" aria-labelledby="pelu-historial-titulo">
    <div class="modal-dialog modal-xl" role="document"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title" id="pelu-historial-titulo">Ficha de atención anterior</h5><button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button></div>
        <div class="modal-body pelu-ficha" id="pelu-historial-contenido" aria-live="polite"></div>
        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button></div>
    </div></div>
</div>
@endsection
@section('page-script')<script src="{{ asset('js/peluqueria-ficha.js') }}?v={{ filemtime(public_path('js/peluqueria-ficha.js')) }}"></script><script>if(window.location.hash==='#pelu-anteriores'){jQuery('#pelu-anteriores-tab').tab('show');}</script>@endsection
