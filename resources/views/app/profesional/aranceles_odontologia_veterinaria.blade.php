@extends('template.profesional.template')

@section('page-styles')
<style>
    .ucov-page{background:#eef3f7;min-height:100vh;padding-bottom:40px}
    .ucov-hero{background:linear-gradient(135deg,#147a70,#13b8b7);border-radius:16px;color:#fff;padding:24px 28px;box-shadow:0 10px 28px rgba(20,122,112,.18)}
    .ucov-icon{width:68px;height:68px;border-radius:20px;background:rgba(255,255,255,.18);display:flex;align-items:center;justify-content:center;font-size:35px}
    .ucov-card{border:0;border-radius:15px;box-shadow:0 5px 18px rgba(29,48,71,.09)}
    .ucov-card .card-header{border:0;border-radius:15px 15px 0 0;background:#fff;padding:20px 22px}
    .ucov-toolbar{display:grid;grid-template-columns:minmax(220px,1fr) minmax(180px,260px) auto;gap:12px;align-items:end}
    .ucov-value{font-size:19px;font-weight:700;color:#147a70;white-space:nowrap}
    .ucov-table th{background:#e9eff4;color:#34455a;border:0;font-size:12px;text-transform:uppercase}
    .ucov-table td{vertical-align:middle}
    .ucov-table input{min-width:90px}
    .ucov-category{background:#f5f0fa;color:#71339a;font-weight:700}
    .ucov-empty{padding:45px;text-align:center;color:#7b8794}
    @media(max-width:768px){.ucov-toolbar{grid-template-columns:1fr}.ucov-hero{padding:20px}.ucov-table{min-width:850px}}
</style>
@endsection

@section('content')
<div class="pcoded-main-container ucov-page">
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
                            <li class="breadcrumb-item"><span>Aranceles odontológicos veterinarios</span></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <section class="ucov-hero mb-4 d-flex align-items-center">
            <div class="ucov-icon mr-3 position-relative"><i class="fas fa-tooth"></i><span style="position:absolute;right:7px;bottom:4px;font-size:17px;font-weight:800">$</span></div>
            <div>
                <h3 class="text-white mb-1">Aranceles odontológicos veterinarios</h3>
                <p class="text-white mb-0" style="opacity:.9">Configure tratamientos y valores UCOV. Este mismo tarifario se usa en odontograma y presupuestos.</p>
            </div>
        </section>

        <div class="card ucov-card">
            <div class="card-header">
                <div class="ucov-toolbar">
                    <div class="form-group mb-0">
                        <label class="font-weight-bold">Lugar de atención</label>
                        <select id="ucovLugar" class="form-control">
                            <option value="">Tarifario general del profesional</option>
                            @foreach($lugares_atencion as $lugar)
                                <option value="{{ $lugar->id }}">{{ $lugar->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group mb-0">
                        <label class="font-weight-bold">Valor de 1 UCOV</label>
                        <input id="ucovValor" type="number" min="1" class="form-control" value="1000">
                    </div>
                    <button id="ucovGuardar" type="button" class="btn btn-info"><i class="feather icon-save"></i> Guardar aranceles</button>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="d-flex justify-content-between align-items-center px-4 py-3 border-bottom">
                    <div><strong>Catálogo de prestaciones</strong><br><small class="text-muted">Valor prestación = UCOV × valor unitario</small></div>
                    <button id="ucovAgregar" type="button" class="btn btn-outline-info btn-sm"><i class="feather icon-plus"></i> Nuevo tratamiento</button>
                </div>
                <div class="table-responsive">
                    <table class="table ucov-table mb-0" id="ucovTabla">
                        <thead><tr><th>Código</th><th>Categoría</th><th>Acción o tratamiento</th><th style="width:120px">UCOV</th><th style="width:150px">Valor final</th><th style="width:55px"></th></tr></thead>
                        <tbody><tr><td colspan="6" class="ucov-empty"><i class="fas fa-spinner fa-spin mr-2"></i>Cargando tarifario…</td></tr></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('page-script')
<script>
(function(){
    var urls={list:@json(route('veterinaria.odontologia.tarifario.index')),save:@json(route('veterinaria.odontologia.tarifario.guardar'))};
    var rows=[];
    var money=function(value){return '$'+Math.round(Number(value)||0).toLocaleString('es-CL');};
    var esc=function(value){return $('<div>').text(value||'').html();};

    function render(){
        var body=$('#ucovTabla tbody').empty(), category='';
        if(!rows.length){body.html('<tr><td colspan="6" class="ucov-empty">No hay prestaciones configuradas.</td></tr>');return;}
        rows.forEach(function(row,index){
            if(row.categoria!==category){category=row.categoria;body.append('<tr><td colspan="6" class="ucov-category">'+esc(category)+'</td></tr>');}
            body.append('<tr data-index="'+index+'">'+
                '<td><input class="form-control form-control-sm js-code" value="'+esc(row.codigo)+'"></td>'+
                '<td><input class="form-control form-control-sm js-category" value="'+esc(row.categoria)+'"></td>'+
                '<td><input class="form-control form-control-sm js-name" value="'+esc(row.nombre)+'"></td>'+
                '<td><input type="number" min="0.01" step="0.01" class="form-control form-control-sm js-ucov" value="'+Number(row.uco||1)+'"></td>'+
                '<td class="ucov-value js-total">'+money(Number(row.uco||1)*Number($('#ucovValor').val()||0))+'</td>'+
                '<td><button type="button" class="btn btn-danger btn-icon btn-sm js-delete" title="Quitar"><i class="feather icon-x"></i></button></td></tr>');
        });
    }
    function sync(){
        $('#ucovTabla tbody tr[data-index]').each(function(){var i=Number($(this).data('index'));rows[i]={id:rows[i]&&rows[i].id,codigo:$(this).find('.js-code').val(),categoria:$(this).find('.js-category').val(),nombre:$(this).find('.js-name').val(),uco:Number($(this).find('.js-ucov').val()||0)};});
    }
    function load(){
        $('#ucovTabla tbody').html('<tr><td colspan="6" class="ucov-empty"><i class="fas fa-spinner fa-spin mr-2"></i>Cargando tarifario…</td></tr>');
        $.get(urls.list,{id_lugar_atencion:$('#ucovLugar').val()||null}).done(function(res){rows=res.prestaciones||[];if(rows[0])$('#ucovValor').val(rows[0].valor_uco||1000);render();}).fail(function(){$('#ucovTabla tbody').html('<tr><td colspan="6" class="ucov-empty text-danger">No fue posible cargar el tarifario UCOV.</td></tr>');});
    }
    $('#ucovLugar').on('change',load);
    $('#ucovValor').on('input',function(){sync();render();});
    $('#ucovAgregar').on('click',function(){sync();rows.push({codigo:'',categoria:'Personalizados',nombre:'',uco:1});render();$('#ucovTabla tbody tr[data-index]').last().find('.js-name').focus();});
    $('#ucovTabla').on('click','.js-delete',function(){sync();rows.splice(Number($(this).closest('tr').data('index')),1);render();}).on('input','.js-ucov',function(){$(this).closest('tr').find('.js-total').text(money(Number(this.value)*Number($('#ucovValor').val())));});
    $('#ucovGuardar').on('click',function(){
        sync();
        if(!rows.length||rows.some(function(r){return !r.categoria||!r.nombre||r.uco<=0;})){swal('Revise el tarifario','Cada fila necesita categoría, tratamiento y UCOV mayor a cero.','warning');return;}
        var btn=$(this).prop('disabled',true);
        $.ajax({url:urls.save,type:'POST',data:{_token:@json(csrf_token()),id_lugar_atencion:$('#ucovLugar').val()||null,valor_uco:Number($('#ucovValor').val()),prestaciones:rows}})
            .done(function(res){rows=res.prestaciones||rows;render();swal('Guardado',res.msj||'Tarifario UCOV actualizado.','success');})
            .fail(function(xhr){swal('Error',(xhr.responseJSON&&xhr.responseJSON.message)||'No fue posible guardar el tarifario.','error');})
            .always(function(){btn.prop('disabled',false);});
    });
    load();
})();
</script>
@endsection
