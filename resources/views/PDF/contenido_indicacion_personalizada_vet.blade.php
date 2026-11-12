@include('PDF.datos_consentimiento_veterinario', ['mostrarRegistroProfesional' => false])

<p><strong>Indicaciones</strong></p>
<p>{!! nl2br(e($datos['indicaciones'])) !!}</p>
