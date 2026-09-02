@extends('template.profesional.template')
@section('content')
<style>
.ref-shell{max-width:1400px;margin:auto}.ref-hero{background:linear-gradient(135deg,#147d72,#12b8b5);color:#fff;border-radius:18px;padding:28px 32px;box-shadow:0 10px 30px rgba(20,125,114,.2)}
.ref-stat,.ref-panel{border:0;border-radius:16px;box-shadow:0 5px 18px rgba(46,60,80,.1)}.ref-stat{padding:18px;background:#fff}.ref-stat strong{display:block;font-size:26px;color:#663294}.ref-stat span{color:#758198}
.ref-panel .card-header{border:0;background:#12b8b5;color:#fff;border-radius:16px 16px 0 0}.step{display:flex;gap:13px;margin-bottom:17px}.step b{width:30px;height:30px;border-radius:50%;background:#e1f7f5;color:#147d72;text-align:center;line-height:30px;flex:none}
.state{display:inline-block;border-radius:18px;padding:4px 10px;font-size:11px;font-weight:700}.state-enviada,.state-visitada{background:#fff2cc;color:#8a6500}.state-registrada{background:#dfeeff;color:#245aa5}.state-activada,.state-bonificada{background:#d9f5e5;color:#167346}.state-cancelada{background:#eee;color:#777}
.ref-back-row{display:flex;justify-content:flex-start;margin-bottom:16px}.ref-back{display:inline-flex;align-items:center;justify-content:center;padding:6px;border:0;color:#117a72!important;background:transparent;font-size:24px;line-height:1;transition:.2s ease}.ref-back:hover{color:#168f87!important;background:transparent;transform:translateY(-1px) scale(1.08)}
</style>
<div class="pcoded-main-container"><div class="pcoded-content"><div class="ref-shell">
    <div class="ref-back-row"><a href="{{ route('profesional.home') }}" class="ref-back" title="Volver al inicio" aria-label="Volver al inicio"><i class="feather icon-home" aria-hidden="true"></i></a></div>
    <div class="ref-hero mb-4"><div class="row align-items-center"><div class="col-md-8">
        <h3 class="text-white mb-2"><i class="feather icon-gift mr-2"></i>Invita y gana con VET SDI</h3>
        <p class="mb-0">Invita a colegas o centros veterinarios. Sigue cada incorporación y recibe crédito cuando comiencen a utilizar la plataforma.</p>
    </div><div class="col-md-4 text-md-right mt-3 mt-md-0"><span class="h4 text-white">Código personal</span><div class="h5 text-white mb-0">VET-{{ strtoupper(substr(hash('sha256', Auth::id().'|'.Auth::user()->email), 0, 8)) }}</div></div></div></div>

    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if(session('warning'))<div class="alert alert-warning">{{ session('warning') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

    <div class="row mb-4">
        <div class="col-6 col-lg-3 mb-3"><div class="ref-stat"><strong>{{ $resumen['enviadas'] }}</strong><span>Invitaciones</span></div></div>
        <div class="col-6 col-lg-3 mb-3"><div class="ref-stat"><strong>{{ $resumen['registradas'] }}</strong><span>Registrados</span></div></div>
        <div class="col-6 col-lg-3 mb-3"><div class="ref-stat"><strong>{{ $resumen['activadas'] }}</strong><span>Activados</span></div></div>
        <div class="col-6 col-lg-3 mb-3"><div class="ref-stat"><strong>{{ $resumen['puntos'] }}</strong><span>Puntos obtenidos</span></div></div>
    </div>

    <div class="row">
        <div class="col-lg-7 mb-4"><div class="card ref-panel h-100"><div class="card-header"><h5 class="text-white mb-0">Nueva invitación</h5></div><div class="card-body">
            <form method="POST" action="{{ route('profesional.referidos.store') }}">@csrf
                <div class="form-row"><div class="form-group col-md-6"><label>Quiero invitar</label><select class="form-control" name="tipo" id="tipo_referido" required><option value="profesional">A un colega veterinario</option><option value="centro">A un centro veterinario</option></select></div>
                <div class="form-group col-md-6"><label>Nombre de contacto</label><input class="form-control" name="nombre_invitado" value="{{ old('nombre_invitado') }}" required></div></div>
                <div class="form-row"><div class="form-group col-md-6"><label>Correo electrónico</label><input type="email" class="form-control" name="email_invitado" value="{{ old('email_invitado') }}" required></div>
                <div class="form-group col-md-6"><label>WhatsApp</label><input class="form-control" name="telefono_invitado" value="{{ old('telefono_invitado') }}" placeholder="Ej.: +56912345678"></div></div>
                <div class="form-row"><div class="form-group col-md-6" id="grupo_nombre_centro" style="display:none"><label>Nombre del centro</label><input class="form-control" name="nombre_centro" value="{{ old('nombre_centro') }}"></div></div>
                <button class="btn btn-info px-4"><i class="feather icon-send mr-1"></i>Enviar invitación</button>
            </form>
        </div></div></div>
        <div class="col-lg-5 mb-4"><div class="card ref-panel h-100"><div class="card-header"><h5 class="text-white mb-0">Esquema de incentivo propuesto</h5></div><div class="card-body">
            <div class="step"><b>1</b><div><strong>Registro verificado</strong><br><small>100 puntos cuando el invitado crea y verifica su cuenta.</small></div></div>
            <div class="step"><b>2</b><div><strong>Primera activación</strong><br><small>200 puntos adicionales al completar su primera atención veterinaria.</small></div></div>
            <div class="step"><b>3</b><div><strong>Centro activo</strong><br><small>500 puntos si el referido es un centro y registra su primera atención pagada.</small></div></div>
            <div class="alert alert-light border mb-0"><strong>Conversión sugerida:</strong> 100 puntos = $1.000 de crédito, aplicable hasta al 50% del plan mensual. Vigencia: 12 meses.</div>
        </div></div></div>
    </div>

    <div class="card ref-panel"><div class="card-header"><h5 class="text-white mb-0">Seguimiento de invitaciones</h5></div><div class="card-body table-responsive">
        <table class="table table-hover"><thead><tr><th>Invitado</th><th>Tipo</th><th>Código</th><th>Fecha</th><th>Estado</th><th>Puntos</th><th></th></tr></thead><tbody>
        @forelse($referidos as $referido)@php($wa=preg_replace('/\D+/','',(string)$referido->telefono_invitado))@php($mensaje='Hola '.$referido->nombre_invitado.', te invito a VET SDI: '.route('referidos.aceptar',$referido->token).' Código: '.$referido->codigo)<tr><td><strong>{{ $referido->nombre_invitado }}</strong><br><small>{{ $referido->email_invitado }}</small>@if($referido->telefono_invitado)<br><small>{{ $referido->telefono_invitado }}</small>@endif</td><td>{{ $referido->tipo === 'centro' ? 'Centro' : 'Profesional' }}@if($referido->nombre_centro)<br><small>{{ $referido->nombre_centro }}</small>@endif</td><td>{{ $referido->codigo }}</td><td>{{ optional($referido->fecha_envio)->format('d-m-Y') }}</td><td><span class="state state-{{ $referido->estado }}">{{ ucfirst($referido->estado) }}</span></td><td>{{ $referido->puntos_otorgados }} / {{ $referido->puntos_propuestos }}</td><td>@if($wa)<a class="btn btn-success btn-sm mb-1" href="https://wa.me/{{ $wa }}?text={{ urlencode($mensaje) }}" target="_blank" rel="noopener"><i class="feather icon-message-circle"></i> WhatsApp</a>@endif @if(!in_array($referido->estado,['activada','bonificada','cancelada']))<form method="POST" class="d-inline" action="{{ route('profesional.referidos.cancelar',$referido) }}">@csrf @method('PATCH')<button class="btn btn-outline-danger btn-sm" onclick="return confirm('¿Cancelar esta invitación?')">Cancelar</button></form>@endif</td></tr>
        @empty<tr><td colspan="7" class="text-center text-muted py-4">Aún no has enviado invitaciones.</td></tr>@endforelse
        </tbody></table>
    </div></div>
</div></div></div>
<script>document.addEventListener('DOMContentLoaded',function(){var tipo=document.getElementById('tipo_referido'),grupo=document.getElementById('grupo_nombre_centro'),input=grupo.querySelector('input');function actualizar(){var centro=tipo.value==='centro';grupo.style.display=centro?'block':'none';input.required=centro;}tipo.addEventListener('change',actualizar);actualizar();});</script>
@endsection
