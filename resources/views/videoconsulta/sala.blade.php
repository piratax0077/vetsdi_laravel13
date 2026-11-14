@extends($profesional ? 'template.profesional.template' : 'template.paciente_dependiente.template')

@section('content')
<div class="pcoded-main-container">
    <div class="pcoded-content">
        <div class="card">
            <div class="card-header">
                <h5>Videoconsulta veterinaria · {{ $hora->Mascota->nombre }}</h5>
            </div>
            <div class="card-body">
                <p>{{ $hora->fecha_consulta }} · {{ substr((string) $hora->hora_inicio, 0, 5) }}</p>
                <button class="btn btn-primary" type="button" id="iniciar-video">Ingresar a la llamada</button>
                <button class="btn btn-outline-primary" type="button" id="copiar-video">Copiar enlace</button>
                @if ($profesional && $hora->permiteAbrirAtencion())
                    <a class="btn btn-outline-primary"
                        href="{{ route('profesional.realizar_consulta', ['id_hora_realizar' => $hora->id]) }}"
                        target="_blank" rel="noopener">Abrir atención veterinaria</a>
                @endif
                <p id="estado-video" role="status" class="mt-2"></p>
                <div id="video-veterinario"></div>
            </div>
        </div>
        <div class="card">
            <div class="card-header d-flex flex-wrap justify-content-between">
                <h5>Ficha Veterinaria Única</h5>
                <a href="{{ $fvu }}" target="_blank" rel="noopener">Abrir ficha en otra pestaña</a>
            </div>
            <iframe title="Ficha Veterinaria Única" src="{{ $fvu }}" class="w-100 border-0" style="height: 75vh" loading="lazy"></iframe>
        </div>
    </div>
</div>
<script>
(() => {
    const entrar = document.getElementById('iniciar-video');
    const estado = document.getElementById('estado-video');
    document.getElementById('copiar-video').addEventListener('click', async () => {
        try {
            await navigator.clipboard.writeText(location.href);
            estado.textContent = 'Enlace copiado.';
        } catch {
            window.prompt('Copie el enlace de la sala', location.href);
        }
    });
    entrar.addEventListener('click', () => {
        entrar.disabled = true;
        estado.textContent = 'Conectando con la sala…';
        const script = document.createElement('script');
        script.src = 'https://8x8.vc/' + encodeURIComponent(@json($appId)) + '/external_api.js';
        const error = () => {
            script.remove();
            entrar.disabled = false;
            estado.textContent = 'No se pudo abrir la sala. Intente nuevamente.';
        };
        script.onerror = error;
        script.onload = () => {
            try {
                const llamada = new JitsiMeetExternalAPI('8x8.vc', {
                    roomName: @json($appId . '/' . $sala->nombre_grupo),
                    jwt: @json($token),
                    parentNode: document.getElementById('video-veterinario'),
                    height: 480,
                    lang: 'es'
                });
                estado.textContent = '';
                llamada.addListener('readyToClose', () => {
                    llamada.dispose();
                    script.remove();
                    entrar.disabled = false;
                    estado.textContent = 'Salió de la llamada.';
                });
            } catch {
                error();
            }
        };
        document.head.append(script);
    });
})();
</script>
@endsection
