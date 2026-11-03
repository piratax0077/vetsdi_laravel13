@extends('template.documentos_veterinarios')
@section('content')
<a class="btn btn-outline-info btn-sm mb-3" href="{{ route('vet.pdf.index',$documento->id_mascota ? ['id_mascota'=>$documento->id_mascota] : ['id_institucion'=>$documento->id_institucion]) }}">Historial de documentos</a>
<div class="card"><div class="card-header"><h5>{{ $documento->nombre }}</h5></div><div class="card-body">
@if($mascota)
<p>{{ $mascota->nombre }} · Tutor: {{ $tutor->nombres ?? 'Sin registro' }}</p>
<div class="d-flex flex-wrap mb-3"><form method="post" action="{{ route('vet.pdf.enviar',['documento'=>$documento->id]) }}" class="mr-2 mb-2">@csrf<button type="submit" class="btn btn-info" @disabled(in_array(config('mail.default'),['log','array'],true))>Enviar PDF al correo del tutor</button></form><button type="button" id="preparar-whatsapp" class="btn btn-outline-info mb-2" data-url="{{ route('vet.pdf.preparar',['documento'=>$documento->id]) }}">Preparar WhatsApp</button></div>
@if(in_array(config('mail.default'),['log','array'],true))<p class="text-muted">El envío por correo estará disponible cuando se configure el correo del sistema. El PDF está guardado y puede compartirse por WhatsApp.</p>@endif
<p class="text-muted">Correo del tutor: {{ $tutor?->email ?: 'No registrado' }}. Revise sus datos antes de enviar.</p>
<div id="envio-preparado" hidden><p>El enlace para compartir vence en 48 horas.</p><a id="abrir-whatsapp" class="btn btn-success mb-3" target="_blank" rel="noopener noreferrer">Abrir WhatsApp con el documento</a></div><p id="error-preparar" role="status"></p>
@endif
<a class="btn btn-outline-info btn-sm mb-3" href="{{ route('vet.pdf.ver',['documento'=>$documento->id]) }}" target="_blank" rel="noopener">Abrir PDF</a>
<iframe src="{{ route('vet.pdf.ver',['documento'=>$documento->id]) }}" title="{{ $documento->nombre }}" style="width:100%;height:75vh;border:1px solid #ddd"></iframe>
</div></div>
@endsection
@section('scripts')
<script>
(function(){var btn=document.getElementById('preparar-whatsapp');if(!btn)return;btn.addEventListener('click',async function(){btn.disabled=true;var error=document.getElementById('error-preparar');error.textContent='';try{var r=await fetch(btn.dataset.url,{method:'POST',headers:{'Accept':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content}});var datos=await r.json();if(!r.ok)throw new Error(datos.message||'No se pudo preparar el documento.');document.getElementById('abrir-whatsapp').href=datos.whatsapp;document.getElementById('envio-preparado').hidden=false;}catch(e){error.textContent=e.message;}finally{btn.disabled=false;}});})();
</script>
@endsection
