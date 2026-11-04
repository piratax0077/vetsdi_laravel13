@foreach($notificaciones as $notificacion)
    @if(!$notificacion->leida_en)
        <div class="alert alert-info">
            <strong>{{ $notificacion->nombre }}</strong> ·
            {{ ['respuesta' => 'Recibiste una respuesta. Revísala a continuación.', 'contacto_compartido' => 'El tutor compartió su teléfono contigo. Revísalo en Mis respuestas.', 'aviso_cerrado' => 'El aviso fue cerrado: la mascota volvió con su familia.'][$notificacion->tipo] }}
            <small>{{ $notificacion->created_at }}</small>
            <button type="button" class="btn btn-sm btn-outline-info btn-leer-perdida" data-id="{{ $notificacion->id }}">Marcar como leída</button>
        </div>
    @endif
@endforeach

@if($misRespuestas->isNotEmpty())
    <section class="mi-aviso">
        <h5>Mis respuestas</h5>
        @foreach($misRespuestas as $respuestaEnviada)
            <article class="respuesta-aviso">
                <strong>{{ $respuestaEnviada->nombre }}</strong> · {{ $respuestaEnviada->created_at }}
                <span class="badge {{ $respuestaEnviada->cerrado_en ? 'badge-success' : 'badge-info' }}">{{ $respuestaEnviada->cerrado_en ? 'Aviso cerrado' : 'Aviso activo' }}</span>
                <p>{{ $respuestaEnviada->mensaje }}</p>
                @if($respuestaEnviada->telefono)
                    <a class="btn btn-info btn-sm" href="https://wa.me/{{ preg_replace('/\D/', '', $respuestaEnviada->telefono) }}" target="_blank" rel="noopener">Contactar a {{ $respuestaEnviada->tutor }} · {{ $respuestaEnviada->telefono }}</a>
                @else
                    <small>El tutor aún no ha compartido su teléfono contigo.</small>
                @endif
            </article>
        @endforeach
    </section>
@endif
