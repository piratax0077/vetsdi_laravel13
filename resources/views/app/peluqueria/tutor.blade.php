@extends('app.peluqueria.layout')
@php
    // Pantalla del tutor con el diseño de la del profesional; los datos son los que guarda la peluquería
    $perfil = \App\Models\Peluqueria::where('id_usuario', Auth::id())->first();
    abort_unless($perfil, 403);
    $tutor = \Illuminate\Support\Facades\DB::table('peluqueria_clientes')->where('peluqueria_id', $perfil->id)->where('id', $cliente)->first();
    abort_unless($tutor, 404);

    $nombreTutor = $tutor->tutor ?: 'Tutor sin nombre';
    $rutTutor = $tutor->rut ?? null;
    $digitos = fn ($telefono) => preg_replace('/\D/', '', (string) $telefono);
    $whatsappTutor = preg_match('/^(?:56)?(9\d{8})$/', $digitos($tutor->telefono), $celular) ? '56' . $celular[1] : null;
    $ciudad = !empty($tutor->id_ciudad) ? \App\Models\Ciudad::find($tutor->id_ciudad) : null;
    $direccionTexto = collect([$tutor->direccion ?? null, optional($ciudad)->nombre])->filter()->implode(', ');

    // Sus mascotas: las registradas con el mismo RUT o, sin RUT, con el mismo nombre y teléfono
    $mascotas = \Illuminate\Support\Facades\DB::table('peluqueria_clientes')->where('peluqueria_id', $perfil->id)->orderBy('mascota')->get()
        ->filter(fn ($registro) => $rutTutor
            ? ($registro->rut ?? null) === $rutTutor
            : mb_strtolower(trim($registro->tutor)) === mb_strtolower(trim($tutor->tutor)) && $digitos($registro->telefono) === $digitos($tutor->telefono))
        ->values();
    $nombresMascotas = $mascotas->pluck('mascota', 'id');
    $especieTexto = fn ($registro) => $registro->especie === 'Exótico' && !empty($registro->especie_exotico) ? 'Exótico (' . $registro->especie_exotico . ')' : $registro->especie;

    $atenciones = \Illuminate\Support\Facades\DB::table('peluqueria_citas')->where('peluqueria_id', $perfil->id)
        ->whereIn('cliente_id', $mascotas->pluck('id'))->orderByDesc('inicio')->get();
    $finalizadas = $atenciones->where('estado', 'finalizada');
    $estados = ['reservada' => 'Reservada', 'confirmada' => 'Confirmada', 'en_atencion' => 'En atención', 'finalizada' => 'Finalizada', 'cancelada' => 'Cancelada'];
    $colorEstado = ['finalizada' => 'badge-light-success', 'cancelada' => 'badge-light-danger', 'en_atencion' => 'badge-light-primary'];
    $pesos = fn ($monto) => '$' . number_format((float) $monto, 0, ',', '.');
    // La tabla muestra solo lo más reciente; los totales consideran todo
    $maximoFilas = 15;
    $pestanasTutor = [
        'atenciones' => 'Historial de atenciones',
        'contacto' => 'Contacto',
        'mascotas' => 'Mascotas',
        'firmados' => 'Lo que firmó',
        'notas' => 'Notas internas del tutor',
    ];
@endphp
@section('content')
<div class="row">
    <div class="col-md-12 mb-3 encabezado-pagina">
        <div class="mb-2 mb-md-0">
            <a href="{{ route('peluqueria.mascotas') }}" class="text-muted f-12 d-print-none">
                <i class="feather icon-arrow-left" aria-hidden="true"></i> Mascotas y tutores
            </a>
            <h5 class="f-26 d-block mb-0">{{ $nombreTutor }}</h5>
            <span class="text-muted">
                @if ($rutTutor)
                    {{ $rutTutor }}
                @else
                    <span class="text-danger">falta el RUT</span>
                @endif
            </span>
        </div>
        <div class="ml-md-auto d-print-none">
            @if ($whatsappTutor)
                <a href="https://wa.me/{{ $whatsappTutor }}" target="_blank" rel="noopener" class="btn btn-sm btn-success">
                    <i class="feather icon-message-circle mr-1"></i>WhatsApp
                </a>
            @endif
            @if ($tutor->email)
                <a href="mailto:{{ $tutor->email }}" class="btn btn-sm btn-warning">
                    <i class="feather icon-mail mr-1"></i>Correo
                </a>
            @endif        </div>
    </div>
</div>

<div class="row">
    <div class="col-sm-12">
        <!--Una card por pestaña; al imprimir salen todas-->
        <ul class="nav pill-moderno pill-moderno-white d-print-none" id="tutor-secciones" role="tablist">
            @foreach ($pestanasTutor as $clave => $nombrePestana)
                <li class="nav-item">
                    <a @class(['nav-link', 'active' => $loop->first]) id="tutor-{{ $clave }}-tab" data-toggle="tab"
                        href="#tutor-{{ $clave }}" role="tab" aria-controls="tutor-{{ $clave }}"
                        aria-selected="{{ $loop->first ? 'true' : 'false' }}">{{ $nombrePestana }}</a>
                </li>
            @endforeach
        </ul>

        <div class="tab-content">
            <!--Atenciones de sus mascotas en la peluquería-->
            <div class="tab-pane d-print-block active" id="tutor-atenciones" role="tabpanel" aria-labelledby="tutor-atenciones-tab">
                <div class="card">
                    <div class="card-body">
                        <h6 class="ficha-profesional__titulo"><i class="fas fa-cut mr-1" aria-hidden="true"></i> Historial de atenciones</h6>
                        <p class="text-muted mb-2">Las horas agendadas para sus mascotas, de la más reciente a la más antigua.</p>
                        @if ($atenciones->isEmpty())
                            <div class="ficha-profesional__vacio">Todavía no tiene atenciones.</div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-striped table-xs mb-0">
                                    <thead>
                                        <tr>
                                            <th>Fecha</th>
                                            <th>Mascota</th>
                                            <th>Servicio</th>
                                            <th>Estado</th>
                                            <th class="text-right">Valor</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($atenciones->take($maximoFilas) as $atencion)
                                            <tr @class(['text-muted' => $atencion->estado === 'cancelada'])>
                                                <td>{{ \Carbon\Carbon::parse($atencion->inicio)->format('d-m-Y H:i') }}</td>
                                                <td>{{ $nombresMascotas[$atencion->cliente_id] ?? '-' }}</td>
                                                <td><a href="{{ route('peluqueria.ficha', $atencion->id) }}" class="text-purple">{{ $atencion->servicio }}</a></td>
                                                <td><span class="badge {{ $colorEstado[$atencion->estado] ?? 'badge-light-warning' }}">{{ $estados[$atencion->estado] ?? $atencion->estado }}</span></td>
                                                <td class="text-right">
                                                    @if ($atencion->estado === 'cancelada')
                                                        <del>{{ $pesos($atencion->valor) }}</del>
                                                    @else
                                                        {{ $pesos($atencion->valor) }}
                                                        @if (($atencion->estado_pago ?? null) === 'pagado')
                                                            <span class="badge badge-light-success ml-1">Pagado</span>
                                                        @else
                                                            <span class="badge badge-light-danger ml-1">No pagado</span>
                                                        @endif
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @if ($atenciones->count() > $maximoFilas)
                                <p class="text-muted f-12 mt-2 mb-0">Se muestran las {{ $maximoFilas }} más recientes de {{ $atenciones->count() }}.</p>
                            @endif
                        @endif
                    </div>
                </div>
            </div>

            <!--Contacto-->
            <div class="tab-pane d-print-block" id="tutor-contacto" role="tabpanel" aria-labelledby="tutor-contacto-tab">
                <div class="card">
                    <div class="card-body">
                        <h6 class="ficha-profesional__titulo"><i class="fas fa-address-book mr-1" aria-hidden="true"></i> Contacto</h6>
                        <div class="form-row mb-n2">
                            <div class="col-sm-6 col-lg-4 mb-2">
                                <div class="ficha-profesional__dato py-2 h-100">
                                    <span class="ficha-profesional__icono"><i class="fas fa-envelope" aria-hidden="true"></i></span>
                                    <div>
                                        <span class="ficha-profesional__etiqueta">Correo</span>
                                        <span class="ficha-profesional__valor">
                                            @if ($tutor->email)
                                                <a href="mailto:{{ $tutor->email }}">{{ $tutor->email }}</a>
                                            @else
                                                Sin registro
                                            @endif
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6 col-lg-4 mb-2">
                                <div class="ficha-profesional__dato py-2 h-100">
                                    <span class="ficha-profesional__icono"><i class="fas fa-phone-alt" aria-hidden="true"></i></span>
                                    <div>
                                        <span class="ficha-profesional__etiqueta">Teléfono</span>
                                        <span class="ficha-profesional__valor">
                                            @if ($tutor->telefono)
                                                <a href="tel:{{ $digitos($tutor->telefono) }}">{{ $tutor->telefono }}</a>
                                            @else
                                                Sin registro
                                            @endif
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6 col-lg-4 mb-2">
                                <div class="ficha-profesional__dato py-2 h-100">
                                    <span class="ficha-profesional__icono"><i class="fas fa-id-card" aria-hidden="true"></i></span>
                                    <div>
                                        <span class="ficha-profesional__etiqueta">RUT</span>
                                        <span class="ficha-profesional__valor">{{ $rutTutor ?: 'Sin registro' }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6 col-lg-8 mb-2">
                                <div class="ficha-profesional__dato py-2 h-100">
                                    <span class="ficha-profesional__icono"><i class="fas fa-home" aria-hidden="true"></i></span>
                                    <div>
                                        <span class="ficha-profesional__etiqueta">Dirección</span>
                                        <span class="ficha-profesional__valor">{{ $direccionTexto ?: 'Sin registro' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!--Mascotas del tutor registradas en la peluquería-->
            <div class="tab-pane d-print-block" id="tutor-mascotas" role="tabpanel" aria-labelledby="tutor-mascotas-tab">
                <div class="card">
                    <div class="card-body">
                        <h6 class="ficha-profesional__titulo"><i class="fas fa-paw mr-1" aria-hidden="true"></i> Mascotas</h6>
                        <div class="form-row mb-n2">
                            @foreach ($mascotas as $mascota)
                                @php
                                    $ultimaFicha = $atenciones->where('cliente_id', $mascota->id)->first();
                                    $ultimaFinalizada = $finalizadas->where('cliente_id', $mascota->id)->first();
                                @endphp
                                <div class="col-md-6 mb-2">
                                    <div class="ficha-profesional__dato py-2 h-100">
                                        <span class="traspaso-mascota-foto">
                                            <img src="{{ asset('images/iconos/mascotas.svg') }}" alt="">
                                        </span>
                                        <div class="flex-grow-1">
                                            <span class="ficha-profesional__valor">{{ $mascota->mascota }}</span>
                                            <span class="ficha-profesional__etiqueta">{{ collect([$especieTexto($mascota), $mascota->raza])->filter()->implode(' · ') }}</span>
                                            @if ($ultimaFinalizada)
                                                <span class="ficha-profesional__convenio d-inline-block mt-1"><i class="far fa-calendar-alt mr-1" aria-hidden="true"></i>Última atención {{ \Carbon\Carbon::parse($ultimaFinalizada->inicio)->format('d-m-Y') }}</span>
                                            @endif
                                            @if (trim((string) $mascota->cuidados) !== '')
                                                <span class="d-block f-12 text-muted mt-1">{{ $mascota->cuidados }}</span>
                                            @endif
                                        </div>
                                        @if ($ultimaFicha)
                                            <div class="d-flex flex-shrink-0 d-print-none">
                                                <a href="{{ route('peluqueria.ficha', $ultimaFicha->id) }}" class="btn btn-icon btn-purple js-tooltip-accion"
                                                    title="Ver su última ficha" aria-label="Ver su última ficha de {{ $mascota->mascota }}">
                                                    <i class="feather icon-file-text"></i>
                                                </a>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!--Consentimientos y documentos firmados-->
            <div class="tab-pane d-print-block" id="tutor-firmados" role="tabpanel" aria-labelledby="tutor-firmados-tab">
                <div class="card">
                    <div class="card-body">
                        <h6 class="ficha-profesional__titulo"><i class="fas fa-file-signature mr-1" aria-hidden="true"></i> Lo que firmó</h6>
                        <p class="text-muted mb-2">Consentimientos y autorizaciones a su nombre para las atenciones de peluquería.</p>
                        <div class="ficha-profesional__vacio">Todavía no ha firmado ninguno.</div>
                    </div>
                </div>
            </div>

            <!--Notas que solo ve el equipo de la peluquería-->
            <div class="tab-pane d-print-block" id="tutor-notas" role="tabpanel" aria-labelledby="tutor-notas-tab">
                <div class="card">
                    <div class="card-body">
                        @include('app.peluqueria.notas_tutor')
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('page-script')
<script>
    // La pestaña elegida queda en la dirección, para volver a ella al recargar
    $(function () {
        var pestana = window.location.hash;
        if (pestana && $('#tutor-secciones a[href="' + pestana + '"]').length) {
            $('#tutor-secciones a[href="' + pestana + '"]').tab('show');
        }
        $('#tutor-secciones a[data-toggle="tab"]').on('shown.bs.tab', function (evento) {
            if (window.history && window.history.replaceState) {
                window.history.replaceState(null, '', $(evento.target).attr('href'));
            }
        });
    });

    // Tooltips de los botones de icono, solo si está el de Bootstrap
    $(function () {
        if (!$.fn.tooltip || !$.fn.tooltip.Constructor) return;
        $('.js-tooltip-accion').tooltip({ container: 'body', trigger: 'hover' }).on('click', function () { $(this).tooltip('hide'); });
    });
</script>
@endsection
