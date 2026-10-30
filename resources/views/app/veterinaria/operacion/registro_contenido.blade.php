{{-- Contenido del registro: va dentro de la card o del modal, según quién lo incluya. --}}
@php $enModal = $enModal ?? false; @endphp
@if($seccion === 'hospitalizacion' && $registro && $registro->estado !== 'hospitalizada')
<div @class(['modal-body' => $enModal])>
    <p>Internación cerrada. Consulte el ingreso y sus evoluciones en el detalle.</p>
</div>
@elseif(isset(\App\Services\OperacionVeterinaria::SERVICIOS[$seccion]) && $registro && in_array($registro->estado,['completada','cancelada','no_asiste']))
<div @class(['modal-body' => $enModal])>
    <p><strong>{{ $registro->servicio }}</strong> · {{ $mascotas[$registro->id_mascota] ?? '#'.$registro->id_mascota }} · {{ $sinSegundos($registro->inicio) }} a {{ $sinSegundos($registro->fin) }}</p>
    <p><strong>Responsable:</strong> {{ $registro->responsable }} · <strong>Estado:</strong> {{ $registro->estado }}</p>
    <p><strong>Observaciones:</strong> {{ $registro->observaciones }}</p><p class="text-wrap"><strong>Resultado:</strong> {{ $registro->resultado }}</p>
</div>
@else
@include('app.veterinaria.operacion.formulario',['accionForm'=>$accion,'registroForm'=>$registro,'camposForm'=>$campos,'ocultos'=>[],'valores'=>['ingreso'=>now()->format('Y-m-d\TH:i'),'inicio'=>now()->format('Y-m-d\TH:i'),'estado'=>$seccion === 'hospitalizacion' ? 'hospitalizada' : ($seccion === 'impuestos' ? 'pendiente' : ($seccion === 'asistentes' ? 'borrador' : 'reservada')),'activa'=>1]])
@endif
