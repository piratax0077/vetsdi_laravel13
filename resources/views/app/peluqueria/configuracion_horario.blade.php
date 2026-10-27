@php
 $tramos=$horario['tramos']??[];
 if(!isset($horario['tramos']))foreach($horario['dias'] as $dia){
   if(!empty($horario['pausa_inicio'])){if($horario['apertura']<$horario['pausa_inicio'])$tramos[]=['dia'=>$dia,'inicio'=>$horario['apertura'],'fin'=>$horario['pausa_inicio']];if($horario['pausa_fin']<$horario['cierre'])$tramos[]=['dia'=>$dia,'inicio'=>$horario['pausa_fin'],'fin'=>$horario['cierre']];}
   else $tramos[]=['dia'=>$dia,'inicio'=>$horario['apertura'],'fin'=>$horario['cierre']];
 }
 $dias=[1=>'Lunes',2=>'Martes',3=>'Miércoles',4=>'Jueves',5=>'Viernes',6=>'Sábado',7=>'Domingo'];
@endphp
<form method="post" action="{{ route(($peluqueriaRutaBase??'peluqueria').'.configuracion.agenda') }}" data-horario-form>@csrf<input type="hidden" name="sucursal_id" value="{{ $sucursal->id }}">
<div class="form-row"><div class="col-sm-12"><h6 class="t-aten">Tipo de agenda</h6></div><div class="form-group col-sm-12"><label class="floating-label-activo-sm">Atención</label><input class="form-control form-control-sm" value="Peluquería y belleza · duración según procedimiento" readonly></div></div>
<h6 class="t-aten">Horario de Atención</h6><p class="text-muted">Configura los días y tramos disponibles, igual que en la agenda profesional. Para un descanso, separa la mañana y la tarde en dos tramos. La duración la define cada procedimiento.</p>
<div class="table-responsive"><table class="table table-sm" aria-label="Horario de atención por día"><thead><tr><th>Día de atención</th><th>Hora de inicio</th><th>Hora de término</th><th>Acciones</th></tr></thead><tbody data-horario-tramos>
@foreach($tramos as $i=>$tramo)<tr><td><select class="form-control form-control-sm" name="tramos[{{ $i }}][dia]" required aria-label="Día de atención">@foreach($dias as $numero=>$dia)<option value="{{ $numero }}" @selected($tramo['dia']==$numero)>{{ $dia }}</option>@endforeach</select></td><td><input class="form-control form-control-sm" type="time" name="tramos[{{ $i }}][inicio]" value="{{ $tramo['inicio'] }}" required aria-label="Hora de inicio"></td><td><input class="form-control form-control-sm" type="time" name="tramos[{{ $i }}][fin]" value="{{ $tramo['fin'] }}" required aria-label="Hora de término"></td><td><button type="button" class="btn btn-outline-danger btn-sm" data-quitar-tramo aria-label="Quitar tramo"><i class="feather icon-trash-2"></i> Quitar</button></td></tr>@endforeach
</tbody></table></div><button type="button" class="btn btn-outline-info btn-sm mb-3" data-agregar-tramo><i class="feather icon-plus"></i> Agregar horario</button>
<div class="form-row"><div class="form-group col-md-6"><label>Anticipación mínima (horas)</label><input class="form-control form-control-sm" type="number" name="anticipacion_horas" min="0" max="720" value="{{ $horario['anticipacion_horas'] }}" required></div><div class="form-group col-md-6"><label>Reservar hasta (días desde hoy)</label><input class="form-control form-control-sm" type="number" name="anticipacion_dias" min="1" max="365" value="{{ $horario['anticipacion_dias'] }}" required></div></div><button class="btn btn-info"><i class="feather icon-save"></i> Guardar horario</button>
</form>
