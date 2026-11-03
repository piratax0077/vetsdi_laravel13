<!DOCTYPE html><html lang="es"><head><meta charset="utf-8"><title>Carné de alta</title><style>body{font:15px Arial,sans-serif;max-width:850px;margin:35px auto;color:#252525}h1{color:#7040be}section{border-bottom:1px solid #ddd;padding:12px 0}.texto{white-space:pre-wrap}@media print{button{display:none}body{margin:0}}</style></head><body>
<button onclick="window.print()">Imprimir carné</button>
<h1>Carné de alta hospitalaria</h1><h2>{{ $institucion->nombre }}</h2>
<section><strong>Mascota:</strong> {{ $mascota->nombre ?? '#'.$internacion->id_mascota }} · {{ $mascota->especie ?? '' }}<br><strong>Hospitalización:</strong> #{{ $internacion->id }}<br><strong>Ingreso:</strong> {{ $internacion->ingreso }}<br><strong>Alta:</strong> {{ $internacion->egreso }}<br><strong>Profesional que registra el alta:</strong> {{ $autor->name ?? $internacion->responsable }}</section>
<section><h3>Resumen del alta</h3><p class="texto">{{ $internacion->observaciones_alta }}</p></section>
<section><h3>Tratamiento, cuidados y signos de alarma</h3><p class="texto">{{ $internacion->indicaciones }}</p></section>
<section><h3>Cirugías registradas</h3>@forelse($cirugias as $cirugia)<p>{{ $cirugia->fecha }} · {{ $cirugia->procedimiento }}</p>@empty<p>Sin cirugías registradas.</p>@endforelse</section>
<section><h3>Citaciones a control</h3>@forelse($controles as $control)<p>{{ $control->fecha }} · {{ $control->motivo }}</p>@empty<p>Sin controles programados.</p>@endforelse</section>
</body></html>
