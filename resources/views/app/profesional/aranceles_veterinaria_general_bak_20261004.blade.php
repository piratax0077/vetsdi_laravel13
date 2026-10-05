@extends('template.profesional.template')

@section('page-styles')
<style>
    .tarifario-page{background:#eef3f7;min-height:100vh;padding:0 0 45px}
    .tarifario-hero{background:linear-gradient(135deg,#147c70,#15b8b5);color:#fff;border-radius:16px;padding:22px 26px;box-shadow:0 8px 25px rgba(20,124,112,.18)}
    .tarifario-card{border:0;border-radius:16px;box-shadow:0 5px 18px rgba(29,48,71,.09)}
    .tarifario-card .card-header{background:#fff;border:0;border-radius:16px 16px 0 0;padding:20px 24px}
    .tarifario-category{color:#129e9d;border-bottom:1px solid #d7e2e8;padding-bottom:8px;margin-bottom:16px}
    .tarifario-card label{color:#435268;font-weight:600}
    .tarifario-card .input-group-text{background:#e9eff4;color:#34455a}
</style>
@endsection

@section('content')
<div class="pcoded-main-container tarifario-page">
    <div class="pcoded-content m-top">
        <div class="page-header">
            <div class="page-block">
                <div class="row align-items-center">
                    <div class="col-md-12 mt-2">
<ul class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="{{ route('profesional.home') }}" title="Volver a mi escritorio">
                                    <i class="feather icon-home"></i>
                                </a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="{{ route('profesional.configuracion') }}">Configuración</a>
                            </li>
                            <li class="breadcrumb-item"><span>Aranceles veterinarios generales</span></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="tarifario-hero mb-4 d-flex justify-content-between align-items-center flex-wrap">
            <div>
                <h3 class="text-white mb-1">Configurar mis aranceles</h3>
                <div>Valores generales de atención veterinaria disponibles para presupuestos.</div>
            </div>
            <a href="{{ route('profesional.configuracion') }}" class="btn btn-light mt-2 mt-md-0"><i class="feather icon-arrow-left"></i> Volver</a>
        </div>

        <div class="card tarifario-card">
            <div class="card-header">
                <div class="row align-items-end">
                    <div class="col-md-8">
                        <label for="tarifarioLugar">Lugar de atención</label>
                        <select id="tarifarioLugar" class="form-control">
                            @foreach($lugares_atencion as $lugar)
                                <option value="{{ $lugar->id }}">{{ $lugar->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 text-md-right mt-3 mt-md-0">
                        <button id="guardarTarifario" type="button" class="btn btn-info px-4" @if($lugares_atencion->isEmpty()) disabled @endif><i class="feather icon-save"></i> Guardar aranceles</button>
                    </div>
                </div>
            </div>
            <div class="card-body p-4">
                @if($lugares_atencion->isEmpty())
                    <div class="alert alert-warning mb-0">Primero debe registrar un lugar de atención.</div>
                @else
                    @foreach($catalogoTarifario as $categoria => $servicios)
                        @if($categoria !== 'Cirugia, odontologia y hospitalizacion')
                        <section class="mb-4">
                            <h5 class="tarifario-category">{{ $categoria }}</h5>
                            <div class="row">
                                @foreach($servicios as $servicio)
                                    <div class="col-md-6 col-xl-4 mb-3">
                                        <label>{{ $servicio['nombre'] }}</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend"><span class="input-group-text">$</span></div>
                                            <input type="number" min="0" step="100" class="form-control tarifa-general" data-servicio="{{ $servicio['nombre'] }}" placeholder="Sin valor">
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </section>
                        @endif
                    @endforeach
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@section('page-script')
<script>
(function () {
    var listar = @json(route('profesional.mis_valores_lugar_atencion'));
    var guardar = @json(route('profesional.guardar_tarifario_veterinario'));
    function cargar() {
        var lugar = $('#tarifarioLugar').val();
        if (!lugar) return;
        $('.tarifa-general').val('');
        $.get(listar, {id_lugar_atencion:lugar}).done(function (registros) {
            if (typeof registros === 'string') registros = JSON.parse(registros);
            (registros || []).forEach(function (item) {
                $('.tarifa-general').filter(function () { return $(this).data('servicio') === item.tipo_atencion; })
                    .val(item.valor === null || item.valor === '' ? '' : Math.round(Number(item.valor)));
            });
        }).fail(function () { swal('Error','No fue posible cargar los aranceles.','error'); });
    }
    $('#tarifarioLugar').on('change', cargar);
    $('#guardarTarifario').on('click', function () {
        var servicios = $('.tarifa-general').map(function () { return {nombre:$(this).data('servicio'), valor:Number($(this).val() || 0)}; }).get();
        var boton = $(this).prop('disabled', true);
        $.post(guardar, {_token:@json(csrf_token()), id_lugar_atencion:$('#tarifarioLugar').val(), servicios:servicios})
            .done(function (r) { swal('Guardado',r.msj || 'Aranceles actualizados.','success'); })
            .fail(function (xhr) { swal('Error',(xhr.responseJSON && xhr.responseJSON.message) || 'No fue posible guardar.','error'); })
            .always(function () { boton.prop('disabled', false); });
    });
    cargar();
})();
</script>
@endsection
