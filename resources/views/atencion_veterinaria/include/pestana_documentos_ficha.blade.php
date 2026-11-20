{{-- Pestaña Documentos: PDF emitidos en esta atención (quedan archivados en vet_documentos_pdf) e imágenes adjuntas a la ficha --}}
@php
    $idFichaDocumentos = (int) ($id_ficha_atencion ?? 0);
    $documentosEmitidos = collect();
    if ($idFichaDocumentos && \Illuminate\Support\Facades\Schema::hasTable('vet_documentos_pdf')) {
        // Cada vez que se abre un PDF se archiva otra copia; se muestra solo la más reciente de cada documento
        $documentosEmitidos = \App\Models\DocumentoPdfVeterinario::where('id_ficha_atencion', $idFichaDocumentos)
            ->latest()->get()->unique('nombre')->values();
    }
    $imagenesAdjuntas = collect(data_get($fichaVeterinariaGeneralData ?? [], 'imagenes_atencion', []))
        ->filter(fn ($imagen) => !empty($imagen['url']))->values();
    $claseTipoDocumento = fn ($tipo) => match ($tipo) {
        'Receta' => 'tipo-documento-receta',
        'Examen' => 'tipo-documento-examen',
        'Consentimiento informado' => 'tipo-documento-consentimiento',
        default => 'tipo-documento-otro',
    };
@endphp
<div class="tab-pane fade" id="documentos-ficha" role="tabpanel" aria-labelledby="documentos-ficha-tab">
    <div class="card">
        <div class="card-header encabezado-card">
            <h5 class="encabezado-card-titulo"><span class="encabezado-card-icono"><i class="feather icon-folder"></i></span>Documentos de la atención</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Tipo</th>
                            <th>Documento</th>
                            <th>Fecha</th>
                            <th>Origen</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($documentosEmitidos as $documento)
                            <tr data-documento-ficha>
                                <td><span class="tipo-documento {{ $claseTipoDocumento($documento->tipo) }}">{{ $documento->tipo }}</span></td>
                                <td>{{ $documento->nombre }}</td>
                                <td>{{ optional($documento->created_at)->format('d-m-Y H:i') }}</td>
                                <td>Emitido</td>
                                <td class="text-center text-nowrap">
                                    <button type="button" class="btn btn-sm btn-primary-light-c" onclick="verDocumentoFicha('{{ route('vet.pdf.ver', ['documento' => $documento->id]) }}', 'iframe')"><i class="feather icon-file"></i> Ver PDF</button>
                                    @if ($documento->id_mascota)
                                        <a class="btn btn-sm btn-outline-primary" href="{{ route('vet.pdf.detalle', ['documento' => $documento->id]) }}" target="_blank" rel="noopener">Enviar al tutor</a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        @foreach ($imagenesAdjuntas as $imagen)
                            <tr data-documento-ficha>
                                <td><span class="tipo-documento tipo-documento-otro">Adjunto</span></td>
                                <td>{{ $imagen['nombre_original'] ?? $imagen['nombre_archivo'] ?? 'Imagen adjunta' }}</td>
                                <td>—</td>
                                <td>Adjuntado</td>
                                <td class="text-center text-nowrap">
                                    <button type="button" class="btn btn-sm btn-primary-light-c" onclick="verDocumentoFicha('{{ $imagen['url'] }}', 'image')"><i class="feather icon-eye"></i> Ver</button>
                                </td>
                            </tr>
                        @endforeach
                        @if ($documentosEmitidos->isEmpty() && $imagenesAdjuntas->isEmpty())
                            <tr>
                                <td colspan="5" class="text-center">Todavía no hay documentos emitidos ni adjuntos en esta atención.</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script>
    // Abre el PDF o la imagen en el mismo visor que usan receta y orden de exámenes
    function verDocumentoFicha(url, tipo) {
        if (window.Fancybox) {
            Fancybox.show([{ src: url, type: tipo, preload: false }]);
        } else {
            window.open(url, '_blank', 'noopener');
        }
    }

    // Muestra en la pestaña cuántos documentos tiene la atención; se puede volver a llamar al agregar filas
    function actualizarContadorDocumentosFicha() {
        var contador = document.getElementById('contador_documentos_ficha');
        if (!contador) return;
        var total = document.querySelectorAll('#documentos-ficha tr[data-documento-ficha]').length;
        contador.textContent = total;
        contador.hidden = total === 0;
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', actualizarContadorDocumentosFicha);
    } else {
        actualizarContadorDocumentosFicha();
    }
</script>
