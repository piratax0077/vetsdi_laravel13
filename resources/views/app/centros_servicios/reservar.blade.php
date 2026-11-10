@extends('template.usuario.template')
@section('content')
<div class="pcoded-main-container"><main class="pcoded-content">
    @include('app.centros_servicios.mensajes')
    <a class="btn btn-outline-secondary mb-3" href="{{ route('paciente.centros_servicios.catalogo') }}">Volver a servicios</a>
    <div class="card">
        <div class="card-header"><h5>{{ $centro->nombre }}</h5></div>
        <div class="card-body">
            <p>{{ $centro->direccion }} · {{ $centro->telefono }}</p>
            @if($mascotas->isEmpty() || !$servicios)
                <p class="text-muted mb-0">{{ !$servicios ? 'El centro aún no ofrece servicios.' : 'Agrega una mascota para solicitar un servicio.' }}</p>
            @else
                <form method="post" action="{{ route('paciente.centros_servicios.solicitar', $centro->id) }}" id="solicitar-servicio-mascota">
                    @csrf
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="servicio">Servicio</label>
                            <select class="form-control" id="servicio" name="servicio" required>
                                @foreach($servicios as $clave => $nombre)<option value="{{ $clave }}" @selected(old('servicio') === $clave)>{{ $nombre }}</option>@endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="mascota_id">Mascota</label>
                            <select class="form-control" id="mascota_id" name="mascota_id" required>
                                <option value="">Selecciona</option>
                                @foreach($mascotas as $mascota)
                                    <option value="{{ $mascota->id }}" data-fallecida="{{ (int)$mascota->fallecida }}" @selected(old('mascota_id') == $mascota->id)>{{ $mascota->nombre }}{{ $mascota->fallecida ? ' · En memoria' : '' }}</option>
                                @endforeach
                            </select>
                            @if($centro->tipo === 'cementerio_mascotas')
                                <small class="text-muted">Para retiro y entierro, selecciona una mascota con fallecimiento registrado.</small>
                            @endif
                        </div>
                        <div class="form-group col-md-6">
                            <label for="inicio">Fecha solicitada</label>
                            <input class="form-control" type="date" id="inicio" name="inicio" min="{{ today('America/Santiago')->toDateString() }}" value="{{ old('inicio') }}" required>
                        </div>
                        <div class="form-group col-md-6" data-servicio="hotel">
                            <label for="fin">Fecha de salida</label>
                            <input class="form-control" type="date" id="fin" name="fin" value="{{ old('fin') }}">
                        </div>
                        <div class="form-group col-md-3" data-servicio="guarderia">
                            <label for="periodo">Plan diurno</label>
                            <select class="form-control" id="periodo" name="periodo">
                                <option value="semana" @selected(old('periodo') === 'semana')>Por semanas</option>
                                <option value="mes" @selected(old('periodo') === 'mes')>Por meses</option>
                            </select>
                        </div>
                        <div class="form-group col-md-3" data-servicio="guarderia">
                            <label for="cantidad">Cantidad de períodos</label>
                            <input class="form-control" type="number" id="cantidad" name="cantidad" min="1" max="12" value="{{ old('cantidad', 1) }}">
                        </div>
                        @if($centro->tipo === 'cementerio_mascotas')
                            <div class="form-group col-12" data-servicio="entierro lugar">
                                <label for="lugar_id">Lugar</label>
                                <select class="form-control" id="lugar_id" name="lugar_id">
                                    <option value="">Selecciona</option>
                                    @foreach($lugares as $lugar)
                                        <option value="{{ $lugar->id }}" data-propio="{{ (int)!$lugar->disponible }}" @selected(old('lugar_id') == $lugar->id)>
                                            {{ $lugar->codigo }} · {{ $lugar->sector }} · {{ $lugar->disponible ? '$'.number_format($lugar->precio, 0, ',', '.') : 'Adquirido' }}
                                            {{ $lugar->descripcion ? ' · '.$lugar->descripcion : '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                    </div>
                    @if($centro->configuracion['ofrece_retiro'] ?? false)
                        <div class="form-group" id="opcion-retiro">
                            <label><input type="checkbox" id="retiro" name="retiro" value="1" @checked(old('retiro'))> Solicitar retiro a domicilio</label>
                        </div>
                        <div class="form-group" id="datos-retiro">
                            <label for="direccion_retiro">Dirección del retiro</label>
                            <input class="form-control" id="direccion_retiro" name="direccion_retiro" maxlength="250" value="{{ old('direccion_retiro') }}">
                        </div>
                    @endif
                    @if($centro->configuracion['ofrece_peluqueria'] ?? false)
                        <div class="form-group" data-servicio="guarderia hotel">
                            <label><input type="checkbox" name="peluqueria" value="1" @checked(old('peluqueria'))> Agregar acompañamiento a peluquería y belleza</label>
                        </div>
                    @endif
                    <div class="form-group">
                        <label for="indicaciones">Indicaciones para el centro</label>
                        <textarea class="form-control" id="indicaciones" name="indicaciones" rows="3" maxlength="2000" placeholder="Preferencias de cuidado, horario de retiro o peluquería de destino">{{ old('indicaciones') }}</textarea>
                    </div>
                    <p class="text-muted">El centro confirmará la disponibilidad y el valor de la solicitud.</p>
                    <a href="{{ route('paciente.centros_servicios.catalogo') }}" class="btn btn-outline-secondary">Cancelar</a>
                    <button class="btn btn-info">Enviar solicitud</button>
                </form>
            @endif
        </div>
    </div>
</main></div>
@endsection
@section('page-script')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('solicitar-servicio-mascota');
        if (!form) return;
        const selector = form.elements.servicio;
        const retiro = form.elements.retiro;
        const direccion = form.elements.direccion_retiro;
        function mostrar(grupo, visible) {
            grupo.hidden = !visible;
            grupo.querySelectorAll('input,select').forEach(campo => { campo.disabled = !visible; });
        }
        function actualizar() {
            const servicio = selector.value;
            form.querySelectorAll('[data-servicio]').forEach(grupo => mostrar(grupo, grupo.dataset.servicio.split(' ').includes(servicio)));
            if (retiro) {
                mostrar(document.getElementById('opcion-retiro'), !['retiro', 'lugar'].includes(servicio));
                mostrar(document.getElementById('datos-retiro'), servicio === 'retiro' || (servicio !== 'lugar' && retiro.checked));
                direccion.required = !direccion.disabled;
            }
            const mascotas = form.elements.mascota_id;
            Array.from(mascotas.options).forEach(opcion => {
                opcion.disabled = ['retiro','entierro'].includes(servicio) && opcion.value !== '' && opcion.dataset.fallecida !== '1';
            });
            if (mascotas.selectedOptions[0]?.disabled) mascotas.value = '';
            const lugares = form.elements.lugar_id;
            if (lugares) {
                Array.from(lugares.options).forEach(opcion => { opcion.disabled = servicio === 'lugar' && opcion.dataset.propio === '1'; });
                if (lugares.selectedOptions[0]?.disabled) lugares.value = '';
                lugares.required = !lugares.disabled;
            }
            if (form.elements.fin) form.elements.fin.required = servicio === 'hotel';
        }
        selector.addEventListener('change', actualizar);
        if (retiro) retiro.addEventListener('change', actualizar);
        actualizar();
    });
</script>
@endsection
