@extends('template.usuario.template')

@section('page-styles')
<style>
    #modal_ficha_mascota table {
        table-layout: fixed;
        width: 100%;
    }
    #modal_ficha_mascota th,
    #modal_ficha_mascota td {
        white-space: normal;
        overflow-wrap: anywhere;
        word-break: break-word;
    }
    #modal_ficha_mascota th:nth-child(1),
    #modal_ficha_mascota td:nth-child(1) {
        width: 120px;
    }
    .mascotas-page-heading { display:flex; align-items:center; justify-content:flex-start; gap:12px; }
    .mascotas-page-heading h5 { margin:0!important; line-height:1; }
    .inicio-mascotas-icono { display:inline-flex; align-items:center; justify-content:center; padding:6px; border:0; color:#fff!important; background:transparent; font-size:24px; line-height:1; transition:.2s ease; }
    .inicio-mascotas-icono:hover { color:#d9fffc!important; transform:translateY(-1px) scale(1.08); }
    .inicio-mascotas-icono:focus { color:#fff!important; outline:2px solid rgba(255,255,255,.75); outline-offset:3px; }
    #card-lista-dependientes .card-mascota { height:calc(100% - 24px); margin-bottom:24px; overflow:hidden; border:0; border-radius:16px; box-shadow:0 7px 22px rgba(38,59,80,.1); transition:.2s ease; }
    #card-lista-dependientes .card-mascota:hover { transform:translateY(-3px); box-shadow:0 12px 30px rgba(38,59,80,.16); }
    .mascota-card-profile { padding:22px 18px 15px; background:linear-gradient(145deg,#f7ffff 0%,#fff 65%); }
    .mascota-card-photo { width:82px!important; height:82px; margin:0 auto; border:4px solid #fff; border-radius:50%; object-fit:cover; box-shadow:0 4px 14px rgba(18,139,130,.25); }
    .mascota-card-species { color:#718096; font-size:13px; }
    .mascota-action-grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:9px; padding:14px; border-top:1px solid #edf2f5; background:#fbfcfd; }
    .mascota-action { display:flex; align-items:center; min-height:48px; padding:9px 10px; border:1px solid #dfe8ec; border-radius:10px; color:#34475a; background:#fff; font-size:11px; font-weight:600; line-height:1.2; text-align:left; white-space:normal; transition:.18s ease; }
    .mascota-action:hover { border-color:#19aaa2; color:#128b82; background:#f0fbfa; transform:translateY(-1px); }
    .mascota-action i { flex:0 0 28px; color:#17a69e; font-size:17px; text-align:center; }
    .mascota-action.is-primary { grid-column:1/-1; justify-content:center; color:#fff; border-color:#168f87; background:linear-gradient(135deg,#1bb9b1,#168c83); font-size:12px; text-align:center; }
    .mascota-action.is-primary i { color:#fff; }
    #card-lista-dependientes .card-mascota .card-body { padding:22px 14px 14px; background:linear-gradient(145deg,#f7ffff 0%,#fff 62%); }
    #card-lista-dependientes .card-mascota .card-body > img { width:82px!important; height:82px; border:4px solid #fff; object-fit:cover; box-shadow:0 4px 14px rgba(18,139,130,.25); }
    #card-lista-dependientes .card-mascota .card-body > .mt-2.d-flex { display:grid!important; grid-template-columns:repeat(2,minmax(0,1fr)); gap:9px; margin:16px -2px -2px!important; }
    #card-lista-dependientes .card-mascota .card-body > .mt-2.d-flex .btn { display:flex; align-items:center; justify-content:flex-start; min-height:47px; margin:0!important; padding:8px 9px; border:1px solid #dfe8ec; border-radius:9px; color:#34475a; background:#fff; font-size:10px; font-weight:600; line-height:1.2; text-align:left; white-space:normal; box-shadow:none; }
    #card-lista-dependientes .card-mascota .card-body > .mt-2.d-flex .btn:hover { border-color:#19aaa2; color:#128b82; background:#f0fbfa; transform:translateY(-1px); }
    #card-lista-dependientes .card-mascota .card-body > .mt-2.d-flex .btn i { flex:0 0 25px; color:#17a69e; font-size:15px; text-align:center; }
    #card-lista-dependientes .card-mascota .card-body > .mt-2.d-flex .btn-ver-mascota { grid-column:1/-1; grid-row:1; justify-content:center; color:#fff; border-color:#168f87; background:linear-gradient(135deg,#1bb9b1,#168c83); }
    #card-lista-dependientes .card-mascota .card-body > .mt-2.d-flex > :nth-child(1) { grid-column:2; grid-row:2; }
    #card-lista-dependientes .card-mascota .card-body > .mt-2.d-flex .btn-ver-ficha { grid-column:1; grid-row:2; }
    #card-lista-dependientes .card-mascota .card-body > .mt-2.d-flex .btn-escritorio-mascota { grid-column:2; grid-row:3; }
    #card-lista-dependientes .card-mascota .card-body > .mt-2.d-flex > :nth-child(5) { grid-column:1; grid-row:3; }
    #card-lista-dependientes .card-mascota .card-body > .mt-2.d-flex .btn-ver-mascota i { color:#fff; }
    #card-lista-dependientes .card-mascota-fallecida .card-body { background:linear-gradient(145deg,#f4f5f7 0%,#fff 62%); }
    #card-lista-dependientes .card-mascota-fallecida .card-body > img { filter:grayscale(.85); opacity:.92; }
    #card-lista-dependientes .mascota-badge-memoria {
        display:inline-block;
        margin-top:6px;
        padding:3px 10px;
        border-radius:20px;
        background:#6c757d;
        color:#fff;
        font-size:10px;
        font-weight:700;
        letter-spacing:.04em;
        text-transform:uppercase;
    }
    #card-lista-dependientes .btn-memorial-mascota,
    #card-lista-dependientes .btn-registrar-fallecimiento {
        grid-column:1/-1;
        justify-content:center;
    }
    #card-lista-dependientes .btn-memorial-mascota { color:#fff!important; border-color:#5c6370!important; background:linear-gradient(135deg,#6c757d,#495057)!important; }
    #card-lista-dependientes .btn-memorial-mascota i,
    #card-lista-dependientes .btn-registrar-fallecimiento i { color:inherit!important; }
    #card-lista-dependientes .btn-registrar-fallecimiento { color:#495057!important; border-color:#ced4da!important; background:#f8f9fa!important; }
    @media (max-width:430px) { .mascota-action-grid{grid-template-columns:1fr}.mascota-action.is-primary{grid-column:auto} }
    #modal_detalle_mascota .modal-content {
        overflow: hidden;
        border: 0;
        border-radius: 18px;
        box-shadow: 0 22px 55px rgba(25, 43, 65, .28);
    }
    #modal_detalle_mascota .modal-header {
        align-items: center;
        padding: 17px 20px;
        border: 0;
        background: linear-gradient(135deg, #12b8b4 0%, #168c83 100%) !important;
    }
    #modal_detalle_mascota .modal-title {
        margin: 0 !important;
        font-size: 18px;
        font-weight: 700;
    }
    #modal_detalle_mascota .close {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 38px;
        height: 38px;
        margin: -6px -6px -6px 0;
        padding: 0;
        border-radius: 50%;
        color: #fff;
        background: rgba(3, 75, 80, .55);
        font-size: 29px;
        font-weight: 400;
        line-height: 1;
        opacity: 1;
        text-shadow: none;
    }
    #modal_detalle_mascota .close:hover {
        background: rgba(3, 75, 80, .8);
    }
    #modal_detalle_mascota .modal-body {
        padding: 20px;
        background: #f5f8fa;
    }
    #modal_detalle_mascota .mascota-resumen {
        display: flex;
        align-items: center;
        margin-bottom: 16px;
        padding: 14px 16px;
        border: 1px solid #dce8eb;
        border-radius: 14px;
        background: linear-gradient(135deg, #fff 0%, #eefafa 100%);
    }
    #modal_detalle_mascota #modal_mascota_img {
        flex: 0 0 88px;
        width: 88px !important;
        height: 88px;
        margin: 0 16px 0 0 !important;
        border: 4px solid #fff;
        border-radius: 50%;
        object-fit: cover;
        box-shadow: 0 5px 16px rgba(24, 140, 131, .2);
    }
    #modal_detalle_mascota .mascota-resumen-texto {
        min-width: 0;
    }
    #modal_detalle_mascota #modal_mascota_nombre {
        margin: 0 0 5px !important;
        color: #263b50;
        font-size: 20px;
        font-weight: 700;
        line-height: 1.2;
    }
    #modal_detalle_mascota .mascota-resumen-subtitulo {
        margin: 0;
        color: #168c83;
        font-size: 12px;
        font-weight: 600;
        letter-spacing: .35px;
        text-transform: uppercase;
    }
    #modal_detalle_mascota .mascota-detail-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
    }
    #modal_detalle_mascota .mascota-detail-item {
        min-height: 66px;
        padding: 10px 12px;
        border: 1px solid #e1e8ee;
        border-radius: 11px;
        background: #fff;
    }
    #modal_detalle_mascota .mascota-detail-item.is-wide {
        grid-column: 1 / -1;
    }
    #modal_detalle_mascota .mascota-detail-label {
        display: block;
        margin-bottom: 4px;
        color: #718096;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
    }
    #modal_detalle_mascota .mascota-detail-value {
        display: block;
        color: #2f4054;
        font-size: 14px;
        font-weight: 600;
        overflow-wrap: anywhere;
    }
    #modal_detalle_mascota .modal-footer {
        justify-content: space-between;
        padding: 13px 20px 17px;
        border-top: 1px solid #e5ecef;
        background: #fff;
    }
    #modal_detalle_mascota .modal-footer .btn {
        min-width: 112px;
        padding: 8px 15px;
        border: 0;
        border-radius: 9px;
        font-weight: 600;
        box-shadow: none;
    }
    @media (max-width: 575.98px) {
        #modal_detalle_mascota .modal-dialog {
            margin: 10px;
        }
        #modal_detalle_mascota .mascota-detail-grid {
            grid-template-columns: 1fr;
        }
        #modal_detalle_mascota .mascota-detail-item.is-wide {
            grid-column: auto;
        }
    }
</style>
@endsection

@section('content')

    @php
        $especiesMascotas = $especiesMascotas ?? collect();
        $tamanosMascotas = $tamanosMascotas ?? collect();
        $especieTamanosMascotas = $especieTamanosMascotas ?? collect();
    @endphp

    <div class="pcoded-main-container">

        <div class="pcoded-content">

            <!--Header-->

            <div class="page-header">

                <div class="page-block">

                    <div class="row align-items-center">

                        <div class="col-md-12">

                            <div class="page-header-title mascotas-page-heading">

                                <a href="{{ route('paciente.home') }}" class="inicio-mascotas-icono" title="Volver al inicio" aria-label="Volver al inicio">
                                    <i class="feather icon-home" aria-hidden="true"></i>
                                </a>

                                <h5 class="font-weight-bold mb-0">Mis Mascotas</h5>

                            </div>

                            <!-- <ul class="breadcrumb">

                                <li class="breadcrumb-item">

                                    <a href="#">Mis Mascotas</a>

                                </li>

                            </ul> -->

                        </div>

                    </div>

                </div>

            </div>

            <!--Cierre: Header-->



            <!-- TABLA DE DEPENDIENTES-->

            <div class="row ">

                <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12">

                    <div class="card">

                        <div class="card-body bg-info py-3 rounded-xl">

                            <div class="row">

                                <div class="col-md-12">

                                    <h5 class="text-white f-20 d-inline">Mis Mascotas </h5>

                                    <div class="float-md-right">
                                        @if(($titulo ?? '') === 'Mascotas')
                                        <button type="button" class="btn btn-outline-light btn-sm mr-1"
                                            id="btn-solicitud-apareamiento" name="btn-solicitud-apareamiento"
                                            onclick="return abrirModalSolicitudApareamiento(event);">
                                            <i class="feather icon-heart"></i> Solicitud de apareamiento
                                            <span class="badge badge-danger d-none" id="badge-apareamiento-nuevas">0</span>
                                        </button>
                                        <button type="button" class="btn btn-outline-light btn-sm mr-1"
                                            id="btn-traspasar-mascota" name="btn-traspasar-mascota"
                                            onclick="return abrirModalTraspasoMascota(event);">
                                            <i class="feather icon-shuffle"></i> Traspasar mascota
                                        </button>
                                        <button type="button" class="btn btn-outline-light btn-sm mr-1"
                                            id="btn-registrar-defuncion" name="btn-registrar-defuncion"
                                            onclick="return abrirModalSeleccionDefuncion(event);">
                                            <i class="feather icon-cloud"></i> Registrar defunción
                                        </button>
                                        @endif
                                        <button type="button" class="btn btn-light btn-sm d-inline" id="btn-agregar-dep" name="btn-agregar-dep">
                                            <i class="fas fa-plus"></i> Agregar mascota
                                        </button>
                                    </div>

                                    <input type="hidden" name="dependencia" id="dependencia" value="{{ $dependencia }}">

                                    <input type="hidden" name="tipo_dependencias" id="tipo_dependencias" value="{{ $tipo_dependencias }}">

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </div>



            <!-- TABLA DE DEPENDIENTES-->
             <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 d-none" id="card-lista-dependientes">
                @if(isset($registros) && $registros && $registros->count() >0 )
                    @foreach ($registros as $registro)
                        @if ($registro->paciente)
                            <div class="col">
                                <div class="card">
                                    <a href="{{ ROUTE('paciente.dependiente.home',['id_dependiente_activo'=>$registro->paciente->id]) }}">
                                        <div class="card-body text-center" style="cursor:pointer">
                                            @if($registro->paciente->sexo == 'M')
                                                <img class="wid-60 text-center mt-1 rounded-circle" src="{{ asset('images/iconos/paciente-m.svg') }}">
                                            @else
                                                <img class="wid-60 text-center mt-1 rounded-circle" src="{{ asset('images/iconos/paciente-f.svg') }}">
                                            @endif
                                            <h5 class="mt-2 mb-0 text-uppercase">{{ $registro->paciente->nombres.' '.$registro->paciente->apellido_uno. ' '.$registro->paciente->apellido_dos }}</h5>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        @endif
                    @endforeach
                @else
                    <h5>Sin Mascotas Registradas</h5>
                @endif
            </div>

        </div>

    </div>




@endsection

@section('modales')
    @if(($titulo ?? '') !== 'Mascotas')
        @include('app.paciente.modales.dependientes.agregar_acompanante')
        @include('app.paciente.modales.dependientes.ver_acomp')
    @endif
    @include('app.paciente.modales.dependientes.agregar')
    @if(($titulo ?? '') === 'Mascotas')
        @include('app.paciente.modales.dependientes.traspasar_mascota')
        @include('app.paciente.modales.dependientes.solicitud_apareamiento')
        @include('app.paciente.modales.dependientes.fallecimiento_mascota')
        <div class="modal fade" id="modal_seleccionar_defuncion" tabindex="-1" role="dialog"
            aria-labelledby="modalSeleccionarDefuncionLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-secondary">
                        <h5 class="modal-title" id="modalSeleccionarDefuncionLabel">
                            <i class="feather icon-cloud"></i> Registrar defunción
                        </h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-light border py-2">
                            Seleccione la mascota para registrar su defunción y crear su memorial.
                        </div>
                        <div class="form-group fill mb-0">
                            <label class="floating-label-activo-sm">Mascota</label>
                            <select class="form-control form-control-sm" id="defuncion_mascota_id">
                                <option value="">Seleccione una mascota</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary btn-sm" data-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-secondary btn-sm" onclick="return continuarRegistroDefuncion(event);">
                            <i class="feather icon-arrow-right"></i> Continuar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
    <!--MODAL DETALLE MASCOTA-->
    <div class="modal fade" id="modal_detalle_mascota" tabindex="-1" role="dialog" aria-labelledby="modalDetalleMascota" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title mt-1" id="modalDetalleMascota">Información de la mascota </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="mascota-resumen">
                        <img id="modal_mascota_img" src="{{ asset('images/iconos/paciente-m.svg') }}" alt="Foto de la mascota">
                        <div class="mascota-resumen-texto">
                            <h5 class="text-uppercase" id="modal_mascota_nombre"></h5>
                            <p class="mascota-resumen-subtitulo">Ficha de identificación veterinaria</p>
                        </div>
                    </div>

                    <div class="mascota-detail-grid">
                        <div class="mascota-detail-item">
                            <span class="mascota-detail-label">Especie</span>
                            <span class="mascota-detail-value" id="modal_mascota_especie">-</span>
                        </div>
                        <div class="mascota-detail-item">
                            <span class="mascota-detail-label">Raza</span>
                            <span class="mascota-detail-value" id="modal_mascota_raza">-</span>
                        </div>
                        <div class="mascota-detail-item">
                            <span class="mascota-detail-label">Tamaño</span>
                            <span class="mascota-detail-value" id="modal_mascota_tamano">-</span>
                        </div>
                        <div class="mascota-detail-item">
                            <span class="mascota-detail-label">Sexo</span>
                            <span class="mascota-detail-value" id="modal_mascota_sexo">-</span>
                        </div>
                        <div class="mascota-detail-item">
                            <span class="mascota-detail-label">Fecha de nacimiento</span>
                            <span class="mascota-detail-value" id="modal_mascota_fecha">-</span>
                        </div>
                        <div class="mascota-detail-item">
                            <span class="mascota-detail-label">N.º de chip</span>
                            <span class="mascota-detail-value" id="modal_mascota_chip">-</span>
                        </div>
                        <div class="mascota-detail-item">
                            <span class="mascota-detail-label">Esterilización</span>
                            <span class="mascota-detail-value" id="modal_mascota_esterilizado">-</span>
                        </div>
                        <div class="mascota-detail-item">
                            <span class="mascota-detail-label">Fecha de esterilización</span>
                            <span class="mascota-detail-value" id="modal_mascota_fecha_esterilizacion">-</span>
                        </div>
                        <div class="mascota-detail-item is-wide">
                            <span class="mascota-detail-label">Condición crónica o frecuente</span>
                            <span class="mascota-detail-value" id="modal_mascota_enfermedad_cronica">-</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger btn-sm" id="btn_eliminar_mascota"><i class="feather icon-x"></i> Eliminar</button>
                    <button type="button" class="btn btn-secondary btn-sm d-none" id="btn_registrar_fallecimiento_detalle" onclick="return abrirModalFallecimientoDesdeDetalle(event);"><i class="feather icon-cloud"></i> Registrar fallecimiento</button>
                    <button type="button" class="btn btn-info btn-sm" id="btn_editar_mascota"><i class="feather icon-edit"></i> Editar</button>
                    <!--<button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>-->
                </div>
            </div>
        </div>
    </div>
    <!--MODAL FICHA MEDICA-->
    <div class="modal fade" id="modal_ficha_mascota" tabindex="-1" role="dialog" aria-labelledby="modalFichaMascota" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title mt-1" id="modalFichaMascota">Ficha Médica <span id="modal_ficha_mascota_nombre"></span></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered mb-0" id="tabla_ficha_mascota">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Diagnóstico</th>
                                    <th>Indicaciones</th>
                                    <th>Profesional / Lugar</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection



@section('page-script')
<script>
  
  // IMPORTANTE: desactiva auto-discover
  Dropzone.autoDiscover = false;

  // Lista (la mantengo igual)
  var appBaseUrl = '';
  var lista_ven_imagenes = {};

  function prefijoAppActual() {
    var pathname = window.location.pathname || '';
    var marcadores = ['/Paciente/', '/paciente/'];
    for (var i = 0; i < marcadores.length; i++) {
      var indice = pathname.indexOf(marcadores[i]);
      if (indice >= 0) {
        return window.location.origin + pathname.substring(0, indice);
      }
    }

    var metaBase = $('meta[name="app-base-url"]').attr('content');
    if (metaBase) {
      return String(metaBase).replace(/\/$/, '');
    }

    return window.location.origin;
  }

  function urlAppRuta(rutaRelativa) {
    if (!rutaRelativa) return '';
    if (/^https?:\/\//i.test(rutaRelativa)) {
      try {
        var parsed = new URL(rutaRelativa, window.location.origin);
        var path = parsed.pathname + parsed.search + parsed.hash;
        return prefijoAppActual().replace(/\/$/, '') + path;
      } catch (e) {
        return rutaRelativa;
      }
    }
    var base = prefijoAppActual().replace(/\/$/, '');
    var path = rutaRelativa.charAt(0) === '/' ? rutaRelativa : '/' + rutaRelativa;
    return base + path;
  }

  appBaseUrl = prefijoAppActual();
  var rutaMascotasBase = urlAppRuta(@json(route('paciente.mascotas.index', [], false)));
  var rutaMascotasStore = urlAppRuta(@json(route('paciente.mascotas.store', [], false)));
  var rutaMascotasLista = urlAppRuta(@json(route('paciente.mascotas.lista', [], false)));
  var rutaImagenCarga = urlAppRuta(@json(route('paciente.imagen.carga', [], false)));
  var storageAssetBase = appBaseUrl.replace(/\/$/, '') + '/storage';
  @if(($titulo ?? '') === 'Mascotas')
  var rutaFallecimientoGuardar = urlAppRuta(@json(route('paciente.mascotas.fallecimiento.guardar', ['mascota' => '__MASCOTA__'], false)));
  var rutaMemorialMascota = urlAppRuta(@json(route('paciente.mascotas.memorial', ['mascota' => '__MASCOTA__'], false)));
  @else
  var rutaFallecimientoGuardar = '';
  var rutaMemorialMascota = '';
  @endif

  function esRespuestaExitosa(data) {
    if (!data || typeof data !== 'object') {
      return false;
    }
    var estado = data.estado;
    return estado === 1 || estado === '1' || estado === true;
  }

  function parsearRespuestaAjax(respuesta) {
    if (respuesta === null || respuesta === undefined || respuesta === '') {
      return null;
    }
    if (typeof respuesta === 'object') {
      return respuesta;
    }
    if (typeof respuesta !== 'string') {
      return null;
    }

    try {
      return JSON.parse(respuesta);
    } catch (e) {
      var inicio = respuesta.indexOf('{');
      var fin = respuesta.lastIndexOf('}');
      if (inicio >= 0 && fin > inicio) {
        try {
          return JSON.parse(respuesta.substring(inicio, fin + 1));
        } catch (e2) {
          return null;
        }
      }
      return null;
    }
  }

  function parsearRespuestaImagen(respuesta) {
    if (!respuesta) return null;
    if (typeof respuesta === 'string') {
      try {
        return JSON.parse(respuesta);
      } catch (e) {
        return null;
      }
    }
    return respuesta;
  }

  function obtenerRutaImagenSubida(img) {
    if (!img) return '';
    return img.ruta || img.nombre_img || '';
  }

  function cargar_lista_ven_imagenes(obj_dropzone, alias_examen) {
    lista_ven_imagenes[alias_examen] = [];

    let temp = obj_dropzone.getAcceptedFiles();
    $.each(temp, function(index, value) {
      if (value.status === "success" && value.xhr) {
        var img_temp = parsearRespuestaImagen(value.xhr.response);
        if (!img_temp || !img_temp.img) return;

        var rutaImagen = obtenerRutaImagenSubida(img_temp.img);
        lista_ven_imagenes[alias_examen][index] = [
          rutaImagen,
          img_temp.img.original_file_name,
          img_temp.img.nombre_img,
          img_temp.img.file_extension,
        ];

        $('#input_lista_ven_imagenes').val(JSON.stringify(lista_ven_imagenes));
      }
    });
  }

  function inicializarTablaFichaMascota() {
    if (!$.fn.DataTable) return null;
    if (!$('#tabla_ficha_mascota').length) return null;

    if ($.fn.DataTable.isDataTable('#tabla_ficha_mascota')) {
      return $('#tabla_ficha_mascota').DataTable();
    }

    return $('#tabla_ficha_mascota').DataTable({
      paging: true,
      pageLength: 10,
      lengthMenu: [[10, 20, 50], [10, 20, 50]],
      searching: false,
      info: false,
      ordering: false,
      autoWidth: false,
      language: {
        emptyTable: "Sin registros",
        paginate: {
          previous: "&lsaquo;",
          next: "&rsaquo;"
        }
      }
    });
  }

  function construirFilaEstadoFichaMascota(mensaje, claseExtra) {
    return '<tr>'
      + '<td class="text-center ' + (claseExtra || '') + '">' + (mensaje || '-') + '</td>'
      + '<td></td>'
      + '<td></td>'
      + '<td></td>'
      + '</tr>';
  }

  function normalizarRutaImagen(nombreImagen) {
    if (!nombreImagen) return '';
    if (typeof nombreImagen !== 'string') return '';
    if (nombreImagen.startsWith('http://') || nombreImagen.startsWith('https://')) {
      var matchStorage = nombreImagen.match(/\/storage\/(.+)$/);
      if (matchStorage) {
        return storageAssetBase + '/' + matchStorage[1];
      }
      return nombreImagen;
    }

    var ruta = nombreImagen.replace(/\\/g, '/').replace(/^public\//, '').replace(/^\/+/, '');
    if (ruta.startsWith('storage/')) {
      return storageAssetBase + '/' + ruta.slice('storage/'.length);
    }
    if (ruta.indexOf('/') !== -1) {
      return storageAssetBase + '/' + ruta;
    }
    return storageAssetBase + '/imagenes/temp/' + ruta;
  }

  function sincronizarFotoPerfilDesdeGaleria() {
    var foto = $('#imagenes_ven_pre').val();
    if (foto) {
      return foto;
    }

    if (lista_ven_imagenes && lista_ven_imagenes.ven_pre && lista_ven_imagenes.ven_pre[0] && lista_ven_imagenes.ven_pre[0][0]) {
      foto = lista_ven_imagenes.ven_pre[0][0];
      $('#imagenes_ven_pre').val(foto);
      return foto;
    }

    try {
      var galeria = JSON.parse($('#input_lista_ven_imagenes').val() || '{}');
      if (galeria.ven_pre && galeria.ven_pre[0] && galeria.ven_pre[0][0]) {
        foto = galeria.ven_pre[0][0];
        $('#imagenes_ven_pre').val(foto);
        return foto;
      }
    } catch (e) {}

    return '';
  }

  function mostrarErrorCargaImagen(mensaje, xhr) {
    var texto = mensaje || 'No se pudo subir la imagen.';
    if (xhr && xhr.responseJSON && xhr.responseJSON.message) {
      texto = xhr.responseJSON.message;
    } else if (xhr && xhr.responseJSON && xhr.responseJSON.errors && xhr.responseJSON.errors.file) {
      texto = xhr.responseJSON.errors.file.join('\n');
    }
    if (typeof swal === 'function') {
      swal({ title: 'Error al subir imagen', text: texto, icon: 'error' });
    }
  }

  function limpiarFotosActuales() {
    lista_ven_imagenes = {};
    $('#imagenes_ven_pre').val('');
    $('#imagenes_ven_post').val('');
    $('#input_lista_ven_imagenes').val('');
    $('#listado_fotos_actuales').empty();
    $('#contenedor_fotos_actuales').hide();
    if (myDropzone_ven_pre && typeof myDropzone_ven_pre.removeAllFiles === 'function') {
      myDropzone_ven_pre.removeAllFiles(true);
    }
    if (myDropzone_ven_post && typeof myDropzone_ven_post.removeAllFiles === 'function') {
      myDropzone_ven_post.removeAllFiles(true);
    }
  }

  function renderizarFotosActuales(mascota) {
    var $contenedor = $('#contenedor_fotos_actuales');
    var $listado = $('#listado_fotos_actuales');
    if (!$contenedor.length || !$listado.length) return;

    $listado.empty();
    var fotos = [];

    if (mascota && mascota.foto_perfil) {
      fotos.push(normalizarRutaImagen(mascota.foto_perfil));
    }
    if (mascota && mascota.galeria) {
      ['ven_pre', 'ven_post'].forEach(function (clave) {
        (mascota.galeria[clave] || []).forEach(function (item) {
          if (item && item[0]) {
            var urlFoto = item[0];
            if (typeof urlFoto === 'string' && urlFoto.charAt(0) !== '/' && urlFoto.indexOf('http') !== 0) {
              urlFoto = normalizarRutaImagen(urlFoto);
            }
            fotos.push(urlFoto);
          }
        });
      });
    }

    if (!fotos.length) {
      $contenedor.hide();
      return;
    }

    fotos.forEach(function (url) {
      var $img = $('<img>')
        .attr('src', url)
        .addClass('img-thumbnail mr-2 mb-2')
        .css({ width: '75px', height: '75px', objectFit: 'cover' });
      $listado.append($img);
    });
    $contenedor.show();
  }

  function setModoEdicion(idMascota) {
    mascotaEditandoId = idMascota || null;
    $('#mascota_editar_id').val(mascotaEditandoId || '');
    if (mascotaEditandoId) {
      $('#btn_registrar').html('<i class="feather icon-check"></i> Actualizar');
    } else {
      $('#btn_registrar').html('<i class="feather icon-check"></i> Registrar');
    }
  }

  // Destruye cualquier instancia previa (auto o vieja)
  function destroyDZ(selector) {
    var el = document.querySelector(selector);
    if (el && el.dropzone) {
      el.dropzone.destroy();
    }
  }

  // Instancias globales
  var myDropzone_ven_pre = null;
  var myDropzone_ven_post = null;

  function initVenDropzones() {
    // Si no están en el DOM, no hagas nada
    if (!document.querySelector("#mi-imagen-ven-pre")) return;
    if (!document.querySelector("#mi-imagen-ven-post")) return;

    destroyDZ("#mi-imagen-ven-pre");
    destroyDZ("#mi-imagen-ven-post");

    myDropzone_ven_pre = new Dropzone("#mi-imagen-ven-pre", {
      url: rutaImagenCarga,
      method: "post",
      headers: { "X-CSRF-TOKEN": CSRF_TOKEN },

      // ✅ Para descartar falsos “tipo inválido”
      acceptedFiles: "image/jpeg,image/png,image/jpg,.jpeg,.jpg,.png,image/*",
      maxFilesize: 6, // MB
      maxFiles: 12,
      addRemoveLinks: true,
      createImageThumbnails: true,
      paramName: "file", // default, pero lo dejamos explícito

      dictInvalidFileType: "No puedes subir archivos de este tipo.",
      dictFileTooBig: "El archivo es demasiado grande. Max 6 MiB.",

      success: function(file, response) {
        var data = parsearRespuestaImagen(response) || response;
        if (!data || !data.img) return;
        $('#imagenes_ven_pre').val(obtenerRutaImagenSubida(data.img));
        cargar_lista_ven_imagenes(myDropzone_ven_pre, "ven_pre");
      },

      error: function(file, message, xhr) {
        console.log("VEN_PRE ERROR:", message);
        if (xhr && xhr.responseText) console.log("VEN_PRE SERVER:", xhr.responseText);
        mostrarErrorCargaImagen(typeof message === 'string' ? message : 'No se pudo subir la imagen.', xhr);
      },

      removedfile: function(file) {
        $('#imagenes_ven_pre').val('');
        cargar_lista_ven_imagenes(myDropzone_ven_pre, "ven_pre");
        if (file.previewElement) file.previewElement.remove();
      }
    });

    myDropzone_ven_post = new Dropzone("#mi-imagen-ven-post", {
      url: rutaImagenCarga,
      method: "post",
      headers: { "X-CSRF-TOKEN": CSRF_TOKEN },

      acceptedFiles: "image/jpeg,image/png,image/jpg,.jpeg,.jpg,.png,image/*",
      maxFilesize: 6,
      maxFiles: 12,
      addRemoveLinks: true,
      createImageThumbnails: true,
      paramName: "file",

      dictInvalidFileType: "No puedes subir archivos de este tipo.",
      dictFileTooBig: "El archivo es demasiado grande. Max 6 MiB.",

      success: function(file, response) {
        var data = parsearRespuestaImagen(response) || response;
        if (!data || !data.img) return;
        $('#imagenes_ven_post').val(obtenerRutaImagenSubida(data.img));
        cargar_lista_ven_imagenes(myDropzone_ven_post, "ven_post");
      },

      error: function(file, message, xhr) {
        console.log("VEN_POST ERROR:", message);
        if (xhr && xhr.responseText) console.log("VEN_POST SERVER:", xhr.responseText);
        mostrarErrorCargaImagen(typeof message === 'string' ? message : 'No se pudo subir la imagen.', xhr);
      },

      removedfile: function(file) {
        $('#imagenes_ven_post').val('');
        cargar_lista_ven_imagenes(myDropzone_ven_post, "ven_post");
        if (file.previewElement) file.previewElement.remove();
      }
    });
  }

  var dropzoneMemorialMascota = null;
  var albumMemorialTemporal = [];

  function initDropzoneMemorialMascota() {
    var element = document.querySelector('#dropzone-memorial-mascota');
    if (!element) return;

    if (element.dropzone) {
      element.dropzone.destroy();
      element.dropzone = null;
    }
    if (dropzoneMemorialMascota && typeof dropzoneMemorialMascota.destroy === 'function') {
      try {
        dropzoneMemorialMascota.destroy();
      } catch (e) {}
      dropzoneMemorialMascota = null;
    }

    albumMemorialTemporal = [];
    $('#fallecimiento_album_json').val('[]');

    dropzoneMemorialMascota = new Dropzone(element, {
      url: rutaImagenCarga,
      method: 'post',
      headers: { 'X-CSRF-TOKEN': CSRF_TOKEN },
      acceptedFiles: 'image/jpeg,image/png,image/jpg,.jpeg,.jpg,.png,image/*',
      maxFilesize: 6,
      maxFiles: 24,
      addRemoveLinks: true,
      createImageThumbnails: true,
      paramName: 'file',
      success: function(file, response) {
        var data = parsearRespuestaImagen(response) || response;
        if (!data || !data.img) return;
        albumMemorialTemporal.push([
          obtenerRutaImagenSubida(data.img),
          data.img.original_file_name || '',
          data.img.nombre_img || '',
          data.img.file_extension || ''
        ]);
        $('#fallecimiento_album_json').val(JSON.stringify(albumMemorialTemporal));
      },
      removedfile: function(file) {
        if (file.xhr && file.xhr.response) {
          var data = parsearRespuestaImagen(file.xhr.response);
          var ruta = data && data.img ? obtenerRutaImagenSubida(data.img) : null;
          if (ruta) {
            albumMemorialTemporal = albumMemorialTemporal.filter(function(item) {
              return item[0] !== ruta;
            });
            $('#fallecimiento_album_json').val(JSON.stringify(albumMemorialTemporal));
          }
        }
        if (file.previewElement) {
          file.previewElement.remove();
        }
      },
      error: function(file, message, xhr) {
        mostrarErrorCargaImagen(typeof message === 'string' ? message : 'No se pudo subir la imagen.', xhr);
      }
    });
  }

  // ✅ Inicializa cuando el modal se ABRE (bootstrap)
  $(document).on("shown.bs.modal", "#modal_agregar_dep_nuevo", function() {
    initVenDropzones();
    var especieId = $('#espec_masc').val();
    if (especieId && especieId !== '0') {
      handleEspecieChange($('#modal_agregar_dep_nuevo_raza').val());
    }
  });

  // (opcional) por si la vista se carga ya con el modal abierto
  $(document).ready(function() {
    initVenDropzones();
  });
</script>
    <script>
        var mascotasCache = {};
        var mascotaEditandoId = null;
        var mascotasIniciales = @json(isset($mascotas) ? $mascotas : []);
        var especiesMascotas = @json($especiesMascotas ?? []);
        var tamanosMascotas = @json($tamanosMascotas ?? []);
        var especieTamanosMascotas = @json($especieTamanosMascotas ?? []);
        var rutaRazasMascotas = @json(app_route_url('paciente.mascotas.razas', ['especie' => '__id__']));
        var razasMascotasCache = {};
        var razasMascotasPorEspecie = window.razasMascotasPorEspecie = @json(razas_mascotas_catalogo_por_especie());

        function construirMapaNombre(listado)
        {
            var mapa = {};
            (listado || []).forEach(function(item){
                mapa[item.id] = item.nombre;
            });
            return mapa;
        }

        var especiesLabel = construirMapaNombre(especiesMascotas);
        var tamanoLabel = construirMapaNombre(tamanosMascotas);
        var tamanoLabelSlug = {};
        (tamanosMascotas || []).forEach(function(item){
            tamanoLabelSlug[item.slug] = item.nombre;
        });

        function registrarMascotaCache(mascota)
        {
            if(mascota && mascota.id)
            {
                mascotasCache[mascota.id] = mascota;
                mascotasCache[String(mascota.id)] = mascota;
            }
        }

        function obtenerMascotaPorId(idMascota)
        {
            if (idMascota === null || idMascota === undefined || idMascota === '') {
                return null;
            }

            var clave = String(idMascota);
            if (mascotasCache[clave]) {
                return mascotasCache[clave];
            }
            if (mascotasCache[idMascota]) {
                return mascotasCache[idMascota];
            }

            if (Array.isArray(mascotasIniciales)) {
                for (var i = 0; i < mascotasIniciales.length; i++) {
                    if (String(mascotasIniciales[i].id) === clave) {
                        registrarMascotaCache(mascotasIniciales[i]);
                        return mascotasIniciales[i];
                    }
                }
            }

            return null;
        }

        function urlMascotaDesdePlantilla(plantilla, idMascota)
        {
            if (!plantilla) return '';
            return String(plantilla).replace('__MASCOTA__', idMascota);
        }

        function actualizarMascotasEnLista(mascota)
        {
            if (!mascota || !mascota.id) {
                return;
            }

            registrarMascotaCache(mascota);
            var lista = Array.isArray(mascotasIniciales) ? mascotasIniciales.slice() : [];
            var indice = -1;

            for (var i = 0; i < lista.length; i++) {
                if (String(lista[i].id) === String(mascota.id)) {
                    indice = i;
                    break;
                }
            }

            if (indice >= 0) {
                lista[indice] = mascota;
            } else {
                lista.push(mascota);
            }

            mascotasIniciales = lista;
            pintarTarjetasMascotas(mascotasIniciales);
            $('#card-lista-dependientes').removeClass('d-none');
        }

        function registrarMascotasIniciales()
        {
            if(Array.isArray(mascotasIniciales))
            {
                mascotasIniciales.forEach(function(item){
                    registrarMascotaCache(item);
                });
            }
        }

        function getEspecieById(id)
        {
            return (especiesMascotas || []).find(function(item){
                return parseInt(item.id) === parseInt(id);
            });
        }

        function requiereDetalleEspecie(especieId)
        {
            var especie = getEspecieById(especieId);
            return especie ? !!especie.requiere_detalle : false;
        }

        function obtenerTamanosPorEspecie(especieId)
        {
            if(!especieId) return (tamanosMascotas || []);
            var permitidos = (especieTamanosMascotas || []).filter(function(item){
                return parseInt(item.especie_id) === parseInt(especieId);
            }).map(function(item){ return item.tamano_id; });

            return (tamanosMascotas || []).filter(function(tamano){
                return permitidos.length === 0 || permitidos.indexOf(tamano.id) >= 0;
            });
        }

        function actualizarOpcionesTamano(especieId)
        {
            var tamanosDisponibles = obtenerTamanosPorEspecie(especieId);
            var select = $('#modal_agregar_dep_nuevo_tamano');
            var valorActual = select.val();
            select.html('<option value=\"\">Seleccione</option>');

            tamanosDisponibles.forEach(function(tamano){
                select.append('<option value=\"'+tamano.id+'\">'+tamano.nombre+'</option>');
            });

            if(valorActual && select.find('option[value=\"'+valorActual+'\"]').length)
            {
                select.val(valorActual);
            }
        }

        function actualizarOpcionesRaza(especieId, razaSeleccionada)
        {
            var select = $('#modal_agregar_dep_nuevo_raza');
            if (!select.length) return;

            select.html('<option value=\"\">Seleccione</option><option value=\"sin\">Sin raza</option>');
            if (!especieId || especieId === '0') return;

            var catalogo = window.razasMascotasPorEspecie || razasMascotasPorEspecie || {};
            var razas = catalogo[String(especieId)] || null;

            if (razas && razas.length) {
                poblarRazasSelect(select, razas, razaSeleccionada);
                return;
            }

            if (!rutaRazasMascotas) return;

            if (razasMascotasCache[especieId]) {
                poblarRazasSelect(select, razasMascotasCache[especieId], razaSeleccionada);
                return;
            }

            $.get(rutaRazasMascotas.replace('__id__', especieId))
                .done(function(data){
                    var razasAjax = (data && data.razas) ? data.razas : [];
                    razasMascotasCache[especieId] = razasAjax;
                    poblarRazasSelect(select, razasAjax, razaSeleccionada);
                })
                .fail(function(xhr){
                    console.error('No se pudieron cargar las razas', xhr.status);
                    select.html('<option value=\"\">Seleccione</option><option value=\"sin\">Sin raza</option>');
                });
        }

        function poblarRazasSelect(select, razas, razaSeleccionada)
        {
            select.html('<option value=\"\">Seleccione</option><option value=\"sin\">Sin raza</option>');
            (razas || []).forEach(function(raza){
                var valor = raza.slug || String(raza.id);
                select.append('<option value=\"'+valor+'\">'+raza.nombre+'</option>');
            });
            if (razaSeleccionada) {
                select.val(String(razaSeleccionada));
            }
        }

        function handleEspecieChange(razaSeleccionada)
        {
            var especieSeleccionada = $('#espec_masc').val();
            var requiereDetalle = requiereDetalleEspecie(especieSeleccionada);
            $('#div_espec_masc').toggle(requiereDetalle);
            if(!requiereDetalle)
            {
                $('#obs_espec_masc').val('');
            }
            actualizarOpcionesTamano(especieSeleccionada);
            actualizarOpcionesRaza(especieSeleccionada, razaSeleccionada);
        }
        window.handleEspecieChange = handleEspecieChange;
        window.handleEspecieChangePagina = function(especieId) {
            actualizarOpcionesTamano(especieId || $('#espec_masc').val());
        };

        function obtenerLabelEspecie(especie, otra)
        {
            var label = especiesLabel[especie] ? especiesLabel[especie] : '';
            if(requiereDetalleEspecie(especie) && otra)
            {
                label += (label ? ' ('+otra+')' : otra);
            }
            return label || '-';
        }

        function obtenerLabelRazaDesdeListado(razas, razaId)
        {
            if(!razaId) return '-';
            var encontrada = (razas || []).find(function(raza){
                return parseInt(raza.id) === parseInt(razaId);
            });
            return (encontrada && encontrada.nombre) ? encontrada.nombre : '-';
        }

        function obtenerLabelRaza(mascota)
        {
            if(!mascota) return '-';
            if(mascota.raza_mascota && mascota.raza_mascota.nombre) return mascota.raza_mascota.nombre;
            if(mascota.razaMascota && mascota.razaMascota.nombre) return mascota.razaMascota.nombre;
            var razaId = mascota.raza_id || mascota.raza;
            var especieId = mascota.especie_id || mascota.especie;
            if(razaId && razasMascotasCache[especieId])
            {
                return obtenerLabelRazaDesdeListado(razasMascotasCache[especieId], razaId);
            }
            return '-';
        }

        function actualizarDetalleRaza(mascota)
        {
            if(!mascota) return;
            var razaId = mascota.raza_id || mascota.raza;
            var especieId = mascota.especie_id || mascota.especie;
            if(!razaId || !especieId) return;
            if(razasMascotasCache[especieId]) return;

            $.get(rutaRazasMascotas.replace('__id__', especieId))
                .done(function(data){
                    var razas = (data && data.razas) ? data.razas : [];
                    razasMascotasCache[especieId] = razas;
                    $('#modal_mascota_raza').text(obtenerLabelRazaDesdeListado(razas, razaId));
                });
        }

        function obtenerLabelTamano(tamanoId)
        {
            if(tamanoLabel[tamanoId]) return tamanoLabel[tamanoId];
            return tamanoLabelSlug[tamanoId] ? tamanoLabelSlug[tamanoId] : '-';
        }

        function obtenerLabelSexo(sexo)
        {
            if(sexo === 'M') return 'Macho';
            if(sexo === 'F') return 'Hembra';
            return '-';
        }

        function obtenerChipLabel(mascota)
        {
            if(mascota && mascota.tiene_chip)
            {
                return mascota.chip ? mascota.chip : 'Sí';
            }
            return 'No';
        }

        function formatearFecha(fecha)
        {
            if(!fecha) return '-';

            // Normaliza fechas tipo "YYYY-MM-DD" o ISO completas con tiempo.
            var soloFecha = fecha;
            if(typeof fecha === 'string' && fecha.indexOf('T') > -1)
            {
                soloFecha = fecha.split('T')[0];
            }

            // Intenta formatear desde YYYY-MM-DD
            var partes = soloFecha.split('-');
            if(partes.length === 3)
            {
                return partes[2] + '/' + partes[1] + '/' + partes[0];
            }

            // Último recurso: usar Date para cadenas ISO u otros formatos válidos.
            var d = new Date(fecha);
            if(!isNaN(d.getTime()))
            {
                var dia = ('0' + d.getDate()).slice(-2);
                var mes = ('0' + (d.getMonth()+1)).slice(-2);
                return dia + '/' + mes + '/' + d.getFullYear();
            }

            return fecha;
        }

        function formatearFechaInput(fecha)
        {
            if(!fecha) return '';
            if(typeof fecha === 'string' && fecha.indexOf('T') > -1)
            {
                return fecha.split('T')[0];
            }
            var d = new Date(fecha);
            if(!isNaN(d.getTime()))
            {
                var mes = ('0' + (d.getMonth() + 1)).slice(-2);
                var dia = ('0' + d.getDate()).slice(-2);
                return d.getFullYear() + '-' + mes + '-' + dia;
            }
            return fecha;
        }

        function obtenerImagenMascota(mascota)
        {
            var img_m = '{{ asset('images/iconos/paciente-m.svg') }}';
            var img_f = '{{ asset('images/iconos/paciente-f.svg') }}';
            if(mascota.foto_url)
            {
                return mascota.foto_url;
            }
            if(mascota.foto_perfil)
            {
                return normalizarRutaImagen(mascota.foto_perfil);
            }
            if(mascota.galeria && mascota.galeria.ven_pre && mascota.galeria.ven_pre.length>0 && mascota.galeria.ven_pre[0][0])
            {
                return normalizarRutaImagen(mascota.galeria.ven_pre[0][0]);
            }
            if(mascota.sexo === 'M') return img_m;
            return img_f;
        }

        function mostrarDetalleMascota(idMascota)
        {
            var mascota = obtenerMascotaPorId(idMascota);
            if(!mascota) return;

            var $modal = $('#modal_detalle_mascota');
            if(!$modal.length) return;
            $modal.data('id', idMascota);

            $('#modal_mascota_nombre').text(mascota.nombre || '-');
            var especieId = mascota.especie_id || mascota.especie;
            var tamanoId = mascota.tamano_id || mascota.tamano;
            $('#modal_mascota_especie').text(obtenerLabelEspecie(especieId, mascota.otra_especie));
            $('#modal_mascota_raza').text(obtenerLabelRaza(mascota));
            $('#modal_mascota_tamano').text(obtenerLabelTamano(tamanoId));
            $('#modal_mascota_sexo').text(obtenerLabelSexo(mascota.sexo));
            $('#modal_mascota_fecha').text(formatearFecha(mascota.fecha_nacimiento));
            $('#modal_mascota_chip').text(obtenerChipLabel(mascota));
            var esterilizado = (mascota.esterilizado === true || mascota.esterilizado === 1 || mascota.esterilizado === '1');
            $('#modal_mascota_esterilizado').text(esterilizado ? 'Sí' : 'No');
            $('#modal_mascota_fecha_esterilizacion').text(esterilizado ? formatearFecha(mascota.fecha_esterilizacion) : '-');
            $('#modal_mascota_enfermedad_cronica').text(mascota.enfermedad_cronica ? mascota.enfermedad_cronica : '-');
            $('#modal_mascota_img').attr('src', obtenerImagenMascota(mascota));
            actualizarDetalleRaza(mascota);

            if (mascotaEstaFallecida(mascota)) {
                $('#btn_editar_mascota, #btn_eliminar_mascota, #btn_registrar_fallecimiento_detalle').addClass('d-none');
            } else {
                $('#btn_editar_mascota, #btn_eliminar_mascota').removeClass('d-none');
                $('#btn_registrar_fallecimiento_detalle').removeClass('d-none');
            }

            $modal.appendTo('body');
            $modal.modal('show');
        }

        function mascotaEstaFallecida(mascota)
        {
            return !!(mascota && (mascota.fallecida === true || mascota.fallecida === 1 || mascota.fallecida === '1'));
        }

        function abrirEdicionMascota(idMascota)
        {
            var mascota = mascotasCache[idMascota];
            if(!mascota) return;
            if (mascotaEstaFallecida(mascota)) return;

            $('#modal_detalle_mascota').modal('hide');
            limpiarFormularioMascota();
            setModoEdicion(idMascota);

            $('#modal_agregar_dep_nuevo_tiene_chip').val(mascota.tiene_chip ? '1' : '0');
            toggleChipInput();
            $('#modal_agregar_dep_nuevo_rut').val(mascota.tiene_chip ? (mascota.chip || '') : '');
            $('#modal_agregar_dep_nuevo_nombres_paciente').val(mascota.nombre || '');

            var especieId = mascota.especie_id || mascota.especie || '0';
            $('#espec_masc').val(especieId);
            var razaSeleccionada = '';
            if (mascota.raza_mascota && mascota.raza_mascota.slug) {
                razaSeleccionada = mascota.raza_mascota.slug;
            } else if (mascota.razaMascota && mascota.razaMascota.slug) {
                razaSeleccionada = mascota.razaMascota.slug;
            } else if (mascota.raza_id) {
                razaSeleccionada = mascota.raza_id;
            }
            if(!razaSeleccionada) razaSeleccionada = 'sin';
            handleEspecieChange(razaSeleccionada);
            $('#obs_espec_masc').val(mascota.otra_especie || '');

            var tamanoId = mascota.tamano_id || mascota.tamano || '';
            $('#modal_agregar_dep_nuevo_tamano').val(tamanoId);

            var fechaNacimiento = formatearFechaInput(mascota.fecha_nacimiento);
            $('#modal_agregar_dep_nuevo_fecha_nac').val(fechaNacimiento);
            $('#modal_agregar_dep_nuevo_fecha_nac_desconocida').prop('checked', !fechaNacimiento);
            toggleFechaNacimientoDesconocida();
            $('#modal_agregar_dep_nuevo_sexo').val(mascota.sexo || '0');

            var esterilizado = (mascota.esterilizado === true || mascota.esterilizado === 1 || mascota.esterilizado === '1');
            $('#modal_agregar_dep_nuevo_esterilizado').val(esterilizado ? '1' : '0');
            toggleEsterilizacion();
            var fechaEsterilizacion = esterilizado ? formatearFechaInput(mascota.fecha_esterilizacion) : '';
            $('#modal_agregar_dep_nuevo_fecha_esterilizacion').val(fechaEsterilizacion);
            $('#modal_agregar_dep_nuevo_fecha_esterilizacion_desconocida').prop('checked', esterilizado && !fechaEsterilizacion);
            toggleFechaEsterilizacionDesconocida();

            $('#modal_agregar_dep_nuevo_enfermedad_cronica').val(mascota.enfermedad_cronica || '');
            $('#modal_agregar_dep_nuevo_dieta').val(mascota.dieta || '');
            $('#modal_agregar_dep_nuevo_cirugias').val(mascota.cirugias || '');
            $('#modal_agregar_dep_nuevo_vacunas').val(mascota.vacunas || '');
            $('#modal_agregar_dep_nuevo_viajes').val(mascota.viajes || '');
            $('#modal_agregar_dep_nuevo_ultima_desparasitacion').val(formatearFechaInput(mascota.ultima_desparasitacion));
            $('#modal_agregar_dep_nuevo_producto_desparasitacion').val(mascota.producto_desparasitacion || '');
            var viveConAnimales = mascota.vive_con_animales;
            $('#modal_agregar_dep_nuevo_vive_con_animales').val(
                viveConAnimales === true || viveConAnimales === 1 || viveConAnimales === '1'
                    ? '1'
                    : (viveConAnimales === false || viveConAnimales === 0 || viveConAnimales === '0' ? '0' : '')
            );

            var galeriaMascota = mascota.galeria;
            if(typeof galeriaMascota === 'string')
            {
                try { galeriaMascota = JSON.parse(galeriaMascota); }
                catch (e) { galeriaMascota = {}; }
            }
            lista_ven_imagenes = galeriaMascota || {};
            if(lista_ven_imagenes && Object.keys(lista_ven_imagenes).length)
            {
                $('#input_lista_ven_imagenes').val(JSON.stringify(lista_ven_imagenes));
            }
            else
            {
                $('#input_lista_ven_imagenes').val('');
            }

            $('#imagenes_ven_pre').val(mascota.foto_perfil || '');
            $('#obs_fotos_ven').val(mascota.observaciones_fotos || '');
            renderizarFotosActuales(mascota);

            $('#modal_agregar_dep_nuevo').modal('show');
        }

        @if(($titulo ?? '') === 'Mascotas')
        var rutaTraspasoBuscarTutor = urlAppRuta(@json(route('paciente.mascotas.traspaso.buscar_tutor', [], false)));
        var rutaTraspasoResumen = urlAppRuta(@json(route('paciente.mascotas.traspaso.resumen', ['mascotaId' => '__ID__'], false)));
        var rutaTraspasoEjecutar = urlAppRuta(@json(route('paciente.mascotas.traspaso.ejecutar', ['mascotaId' => '__ID__'], false)));
        var rutaApareamientoGuardar = urlAppRuta(@json(route('paciente.mascotas.apareamiento.guardar', ['mascotaId' => '__ID__'], false)));
        var rutaApareamientoBuzon = urlAppRuta(@json(route('paciente.mascotas.apareamiento.buzon', [], false)));
        @php($tplApareamientoLeida = route('paciente.mascotas.apareamiento.leida', ['mascotaId' => '__MASCOTA__', 'solicitudId' => '__SOL__'], false))
        var rutaApareamientoLeida = urlAppRuta(@json($tplApareamientoLeida));
        var plantillaRutaGenealogia = urlAppRuta(@json(route('mascotas.genealogia.show', ['mascota' => '__MASCOTA__'], false)));
        var plantillaRutaCarnet = urlAppRuta(@json(route('paciente.mascotas.carnet_sanitario', ['mascota' => '__MASCOTA__'], false)));
        var plantillaRutaFvu = urlAppRuta(@json(route('paciente.mascota.ficha_veterinaria', ['id_mascota' => '__MASCOTA__'], false)));
        @else
        var rutaTraspasoBuscarTutor = '';
        var rutaTraspasoResumen = '';
        var rutaTraspasoEjecutar = '';
        var rutaApareamientoGuardar = '';
        var rutaApareamientoBuzon = '';
        var rutaApareamientoLeida = '';
        var plantillaRutaGenealogia = '';
        var plantillaRutaCarnet = '';
        var plantillaRutaFvu = '';
        @endif
        var traspasoTutorEncontrado = null;

        function abrirModalTraspasoMascota(event)
        {
            if (event && typeof event.preventDefault === 'function') {
                event.preventDefault();
            }

            var $modal = $('#modal_traspasar_mascota');
            if (!$modal.length) {
                swal({
                    title: 'Modal no disponible',
                    text: 'No se encontró el formulario de traspaso. Recargue la página e intente nuevamente.',
                    icon: 'error',
                });
                return false;
            }

            resetTraspasoModal();
            poblarSelectTraspasoMascotas();

            if ($('#traspaso_mascota_id option').length <= 1) {
                swal({
                    title: 'Sin mascotas',
                    text: 'Debe tener al menos una mascota registrada para realizar un traspaso.',
                    icon: 'info',
                });
                return false;
            }

            if ($modal.parent().length && !$modal.parent().is('body')) {
                $modal.appendTo('body');
            }

            $modal.off('show.bs.modal.traspaso').on('show.bs.modal.traspaso', function(){
                setTimeout(function(){
                    $('.modal-backdrop').last().addClass('traspaso-mascota-backdrop');
                }, 0);
            });

            $modal.modal('show');
            return false;
        }

        window.abrirModalTraspasoMascota = abrirModalTraspasoMascota;

        function resetTraspasoModal()
        {
            traspasoTutorEncontrado = null;
            $('#traspaso_mascota_id').val('');
            $('#traspaso_situacion').val('');
            $('#traspaso_rut_tutor').val('');
            $('#traspaso_tutor_id').val('');
            $('#traspaso_tutor_nombres').val('');
            $('#traspaso_tutor_apellido_uno').val('');
            $('#traspaso_tutor_apellido_dos').val('');
            $('#traspaso_tutor_email').val('');
            $('#traspaso_tutor_telefono').val('');
            $('#traspaso_tutor_direccion').val('');
            $('#traspaso_tutor_resultado').addClass('d-none');
            $('#traspaso_tutor_formulario').addClass('d-none');
            $('#traspaso_tutor_alerta').addClass('d-none').text('');
            $('#traspaso_tutor_error').addClass('d-none').text('');
            $('#traspaso_resumen_bloque').addClass('d-none');
            $('#traspaso_tutor_actual').text('-');
            $('#traspaso_tutor_nuevo_resumen').text('-');
            $('#traspaso_total_fvu').text('0');
            $('#traspaso_total_vacunas').text('0');
            $('#traspaso_total_desparasitaciones').text('0');
            $('#btn_traspaso_confirmar').prop('disabled', true);
        }

        function poblarSelectTraspasoMascotas()
        {
            var $select = $('#traspaso_mascota_id');
            if (!$select.length) return;

            var valorActual = $select.val();
            $select.find('option:not(:first)').remove();

            Object.keys(mascotasCache).forEach(function(id){
                var mascota = mascotasCache[id];
                if(!mascota || !mascota.nombre || mascotaEstaFallecida(mascota)) return;
                $select.append(
                    $('<option></option>').val(mascota.id).text(mascota.nombre)
                );
            });

            if ($select.find('option').length <= 1) {
                $('.card-mascota').each(function(){
                    var id = $(this).data('id');
                    var nombre = $.trim($(this).find('h5').first().text());
                    if (!id || !nombre) return;
                    if ($select.find('option[value="' + id + '"]').length) return;
                    $select.append($('<option></option>').val(id).text(nombre));
                    if (!mascotasCache[id]) {
                        mascotasCache[id] = { id: id, nombre: nombre };
                    }
                });
            }

            if(valorActual && ($select.find('option[value="' + valorActual + '"]').length || mascotasCache[valorActual]))
            {
                $select.val(valorActual);
            }
        }

        function obtenerDatosFormularioTutorTraspaso()
        {
            return {
                tutor_nombres: ($('#traspaso_tutor_nombres').val() || '').trim(),
                tutor_apellido_uno: ($('#traspaso_tutor_apellido_uno').val() || '').trim(),
                tutor_apellido_dos: ($('#traspaso_tutor_apellido_dos').val() || '').trim(),
                tutor_email: ($('#traspaso_tutor_email').val() || '').trim(),
                tutor_telefono: ($('#traspaso_tutor_telefono').val() || '').trim(),
                tutor_direccion: ($('#traspaso_tutor_direccion').val() || '').trim(),
            };
        }

        function sincronizarLabelsFlotantesTraspaso()
        {
            $('#traspaso_tutor_formulario input, #traspaso_rut_tutor').each(function(){
                var $input = $(this);
                var $group = $input.closest('.form-group.fill');
                if (!$group.length) return;
                if (($input.val() || '').trim() !== '') {
                    $group.addClass('fill');
                }
            });
        }

        function llenarFormularioTutorTraspaso(tutor)
        {
            tutor = tutor || {};
            $('#traspaso_tutor_nombres').val(tutor.nombres || '');
            $('#traspaso_tutor_apellido_uno').val(tutor.apellido_uno || '');
            $('#traspaso_tutor_apellido_dos').val(tutor.apellido_dos || '');
            $('#traspaso_tutor_email').val(tutor.email || '');
            $('#traspaso_tutor_telefono').val(tutor.telefono_uno || '');
            $('#traspaso_tutor_direccion').val(tutor.direccion || '');
            sincronizarLabelsFlotantesTraspaso();
        }

        function tutorTraspasoListo()
        {
            if (!traspasoTutorEncontrado) {
                return false;
            }

            if (traspasoTutorEncontrado.id) {
                return true;
            }

            var datos = obtenerDatosFormularioTutorTraspaso();
            return datos.tutor_nombres.length > 0 && datos.tutor_apellido_uno.length > 0;
        }

        function actualizarBotonTraspaso()
        {
            var mascotaId = $('#traspaso_mascota_id').val();
            var situacion = ($('#traspaso_situacion').val() || '').trim();
            var puedeConfirmar = !!mascotaId
                && situacion.length >= 5
                && tutorTraspasoListo()
                && !$('#traspaso_resumen_bloque').hasClass('d-none');
            $('#btn_traspaso_confirmar').prop('disabled', !puedeConfirmar);
        }

        function mostrarTutorTraspasoEncontrado(tutor, mensaje)
        {
            tutor = tutor || {};
            traspasoTutorEncontrado = tutor;

            if (tutor.requiere_formulario) {
                $('#traspaso_tutor_resultado').addClass('d-none');
                $('#traspaso_tutor_formulario').removeClass('d-none');
                llenarFormularioTutorTraspaso(tutor);
            } else {
                $('#traspaso_tutor_formulario').addClass('d-none');
                $('#traspaso_tutor_id').val(tutor.id || '');
                $('#traspaso_tutor_nombre').text(tutor.nombre || '-');
                $('#traspaso_tutor_rut').text(tutor.rut || '-');
                $('#traspaso_tutor_resultado').removeClass('d-none');
            }

            if (mensaje) {
                $('#traspaso_tutor_alerta').removeClass('d-none').text(mensaje);
            } else {
                $('#traspaso_tutor_alerta').addClass('d-none').text('');
            }

            $('#traspaso_tutor_error').addClass('d-none').text('');
            actualizarBotonTraspaso();
        }

        function buscarTutorTraspaso()
        {
            var rutTutor = ($('#traspaso_rut_tutor').val() || '').trim();
            if (!rutTutor) {
                $('#traspaso_tutor_error').removeClass('d-none').text('Ingrese el RUT del nuevo tutor.');
                return;
            }

            $.ajax({
                url: rutaTraspasoBuscarTutor,
                type: 'GET',
                data: { rut: rutTutor },
            })
            .done(function(data){
                if (data.estado != 1 || !data.tutor) {
                    $('#traspaso_tutor_error').removeClass('d-none').text(data.msj || 'No se pudo buscar el tutor.');
                    traspasoTutorEncontrado = null;
                    $('#traspaso_resumen_bloque').addClass('d-none');
                    actualizarBotonTraspaso();
                    return;
                }

                mostrarTutorTraspasoEncontrado(data.tutor, data.msj);

                if (!data.tutor.requiere_formulario) {
                    cargarResumenTraspaso($('#traspaso_mascota_id').val(), rutTutor);
                } else {
                    $('#traspaso_resumen_bloque').addClass('d-none');
                    actualizarBotonTraspaso();
                }
            })
            .fail(function(jqXHR){
                var msj = (jqXHR.responseJSON && jqXHR.responseJSON.msj)
                    ? jqXHR.responseJSON.msj
                    : 'No se pudo buscar el tutor.';
                $('#traspaso_tutor_error').removeClass('d-none').text(msj);
                traspasoTutorEncontrado = null;
                $('#traspaso_resumen_bloque').addClass('d-none');
                actualizarBotonTraspaso();
            });
        }

        function validarTutorTraspaso()
        {
            var mascotaId = $('#traspaso_mascota_id').val();
            var rutTutor = ($('#traspaso_rut_tutor').val() || '').trim();

            if (!mascotaId) {
                swal({
                    title: 'Seleccione mascota',
                    text: 'Primero debe seleccionar la mascota a traspasar.',
                    icon: 'warning',
                });
                return;
            }

            if (!rutTutor) {
                $('#traspaso_tutor_error').removeClass('d-none').text('Ingrese el RUT del nuevo tutor.');
                return;
            }

            var datos = obtenerDatosFormularioTutorTraspaso();
            if (!datos.tutor_nombres || !datos.tutor_apellido_uno) {
                $('#traspaso_tutor_error').removeClass('d-none').text('Nombre y apellido paterno son obligatorios.');
                return;
            }

            traspasoTutorEncontrado = Object.assign({}, traspasoTutorEncontrado || {}, datos, {
                rut: rutTutor,
                nombre: [datos.tutor_nombres, datos.tutor_apellido_uno, datos.tutor_apellido_dos].filter(Boolean).join(' '),
                requiere_formulario: true,
            });

            cargarResumenTraspaso(mascotaId, rutTutor);
        }

        function mostrarResumenTraspaso(data)
        {
            if(!data || data.estado != 1)
            {
                $('#traspaso_resumen_bloque').addClass('d-none');
                actualizarBotonTraspaso();
                return;
            }

            var tutorActual = data.tutor_actual || {};
            var tutorNuevo = data.tutor_nuevo || {};
            var resumen = data.resumen || {};

            $('#traspaso_tutor_actual').text(
                (tutorActual.nombre || '-') + (tutorActual.rut ? ' (' + tutorActual.rut + ')' : '')
            );
            $('#traspaso_tutor_nuevo_resumen').text(
                (tutorNuevo.nombre || '-') + (tutorNuevo.rut ? ' (' + tutorNuevo.rut + ')' : '')
            );
            $('#traspaso_total_fvu').text(resumen.fvu || 0);
            $('#traspaso_total_vacunas').text(resumen.vacunas || 0);
            $('#traspaso_total_desparasitaciones').text(resumen.desparasitaciones || 0);
            $('#traspaso_resumen_bloque').removeClass('d-none');
            actualizarBotonTraspaso();
        }

        function cargarResumenTraspaso(mascotaId, rutTutor)
        {
            if(!mascotaId)
            {
                $('#traspaso_resumen_bloque').addClass('d-none');
                actualizarBotonTraspaso();
                return;
            }

            var url = rutaTraspasoResumen.replace('__ID__', mascotaId);
            var params = Object.assign({}, obtenerDatosFormularioTutorTraspaso());
            if(rutTutor)
            {
                params.rut_tutor = rutTutor;
            }

            $.ajax({
                url: url,
                type: 'GET',
                data: params,
            })
            .done(function(data){
                if(data.estado != 1)
                {
                    $('#traspaso_tutor_error').removeClass('d-none').text(data.msj || 'No se pudo cargar el resumen.');
                    mostrarResumenTraspaso(null);
                    return;
                }

                if(data.tutor_nuevo)
                {
                    traspasoTutorEncontrado = data.tutor_nuevo;
                    if (!data.tutor_nuevo.requiere_formulario) {
                        $('#traspaso_tutor_id').val(data.tutor_nuevo.id || '');
                        $('#traspaso_tutor_nombre').text(data.tutor_nuevo.nombre || '-');
                        $('#traspaso_tutor_rut').text(data.tutor_nuevo.rut || '-');
                        $('#traspaso_tutor_resultado').removeClass('d-none');
                        $('#traspaso_tutor_formulario').addClass('d-none');
                    }
                    $('#traspaso_tutor_error').addClass('d-none').text('');
                }

                mostrarResumenTraspaso(data);
            })
            .fail(function(jqXHR){
                var msj = (jqXHR.responseJSON && jqXHR.responseJSON.msj)
                    ? jqXHR.responseJSON.msj
                    : 'No se pudo cargar el resumen del traspaso.';
                $('#traspaso_tutor_error').removeClass('d-none').text(msj);
                traspasoTutorEncontrado = null;
                mostrarResumenTraspaso(null);
            });
        }

        function ejecutarTraspasoMascota()
        {
            var mascotaId = $('#traspaso_mascota_id').val();
            var situacion = ($('#traspaso_situacion').val() || '').trim();
            var rutTutor = ($('#traspaso_rut_tutor').val() || '').trim();

            if(!mascotaId || situacion.length < 5 || !tutorTraspasoListo())
            {
                swal({
                    title: 'Traspaso incompleto',
                    text: 'Seleccione mascota, indique la situación y complete los datos del nuevo tutor.',
                    icon: 'warning',
                });
                return;
            }

            var datosTutor = obtenerDatosFormularioTutorTraspaso();
            var nombreTutor = traspasoTutorEncontrado.nombre
                || [datosTutor.tutor_nombres, datosTutor.tutor_apellido_uno, datosTutor.tutor_apellido_dos].filter(Boolean).join(' ');
            var mascota = mascotasCache[mascotaId];
            var nombreMascota = mascota ? mascota.nombre : 'la mascota';

            swal({
                title: 'Confirmar traspaso',
                text: '¿Traspasar a ' + nombreMascota + ' al tutor ' + nombreTutor + '? Se transferirán FVU, carné de vacunas y desparasitaciones.',
                icon: 'warning',
                buttons: ['Cancelar', 'Confirmar traspaso'],
                dangerMode: true,
            }).then(function(confirmado){
                if(!confirmado) return;

                $.ajax({
                    url: rutaTraspasoEjecutar.replace('__ID__', mascotaId),
                    type: 'POST',
                    data: Object.assign({
                        _token: CSRF_TOKEN,
                        rut_tutor: rutTutor,
                        situacion: situacion,
                        confirmar: 1,
                    }, obtenerDatosFormularioTutorTraspaso()),
                })
                .done(function(data){
                    if(data.estado == 1)
                    {
                        $('#modal_traspasar_mascota').modal('hide');
                        swal({
                            title: 'Traspaso realizado',
                            text: data.msj || 'La mascota fue traspasada correctamente.',
                            icon: 'success',
                        });
                        cargarDependientes();
                    }
                    else
                    {
                        swal({
                            title: 'No se pudo traspasar',
                            text: data.msj || 'Intente nuevamente.',
                            icon: 'error',
                        });
                    }
                })
                .fail(function(jqXHR){
                    var msj = (jqXHR.responseJSON && jqXHR.responseJSON.msj)
                        ? jqXHR.responseJSON.msj
                        : 'No se pudo completar el traspaso.';
                    swal({
                        title: 'Error en traspaso',
                        text: msj,
                        icon: 'error',
                    });
                });
            });
        }

        function poblarSelectApareamientoMascotas()
        {
            var $select = $('#apareamiento_mascota_id');
            if (!$select.length) return;

            var valorActual = $select.val();
            $select.find('option:not(:first)').remove();

            Object.keys(mascotasCache).forEach(function(id){
                var mascota = mascotasCache[id];
                if(!mascota || !mascota.nombre || mascotaEstaFallecida(mascota)) return;
                $select.append($('<option></option>').val(mascota.id).text(mascota.nombre));
            });

            if ($select.find('option').length <= 1) {
                $('.card-mascota').each(function(){
                    var id = $(this).data('id');
                    var nombre = $.trim($(this).find('h5').first().text());
                    if (!id || !nombre) return;
                    if ($select.find('option[value="' + id + '"]').length) return;
                    $select.append($('<option></option>').val(id).text(nombre));
                });
            }

            if (valorActual && $select.find('option[value="' + valorActual + '"]').length) {
                $select.val(valorActual);
            }
        }

        function obtenerEdadMascotaTexto(mascota)
        {
            if (!mascota) return 'No registrada';
            if (mascota.fecha_nacimiento) {
                var edad = calcularEdad_valor(mascota.fecha_nacimiento);
                return edad + (edad === 1 ? ' año' : ' años');
            }
            if (mascota.edad !== undefined && mascota.edad !== null && mascota.edad !== '') {
                return mascota.edad + ' años';
            }
            return 'No registrada';
        }

        function obtenerRazaMascotaTexto(mascota)
        {
            if (!mascota) return 'No registrada';
            if (mascota.raza_mascota && mascota.raza_mascota.nombre) return mascota.raza_mascota.nombre;
            if (mascota.raza) return mascota.raza;
            return 'No registrada';
        }

        function sexoMascotaTexto(sexo)
        {
            if (sexo === 'M') return 'Macho';
            if (sexo === 'F') return 'Hembra';
            return 'No registrado';
        }

        function obtenerSexoOpuestoApareamiento(sexoMascota)
        {
            if (sexoMascota === 'M') return 'F';
            if (sexoMascota === 'F') return 'M';
            return '';
        }

        function resetSexoApareamientoAutomatico()
        {
            $('#apareamiento_sexo_buscado').val('');
            $('#apareamiento_sexo_companero_row').addClass('d-none');
            $('#apareamiento_sexo_companero_texto').text('—');
            $('#apareamiento_sexo_sin_registro').addClass('d-none');
        }

        function actualizarSexoApareamientoAutomatico(sexoMascota)
        {
            var sexoOpuesto = obtenerSexoOpuestoApareamiento(sexoMascota);
            $('#apareamiento_sexo_buscado').val(sexoOpuesto);

            if (sexoOpuesto) {
                $('#apareamiento_sexo_companero_texto').text(sexoMascotaTexto(sexoOpuesto));
                $('#apareamiento_sexo_companero_row').removeClass('d-none');
                $('#apareamiento_sexo_sin_registro').addClass('d-none');
                return;
            }

            $('#apareamiento_sexo_companero_row').addClass('d-none');
            $('#apareamiento_sexo_sin_registro').removeClass('d-none');
        }

        function poblarRazasBuscadasApareamiento(razas, razaSeleccionada)
        {
            var $select = $('#apareamiento_raza_buscada');
            $select.empty().append($('<option></option>').val('').text('Seleccione una raza'));

            (razas || []).forEach(function(raza){
                if (!raza || !raza.nombre) return;
                $select.append($('<option></option>').val(raza.nombre).text(raza.nombre));
            });

            $select.prop('disabled', false);
            if (razaSeleccionada && $select.find('option').filter(function(){
                return $(this).val() === razaSeleccionada;
            }).length) {
                $select.val(razaSeleccionada);
            }
        }

        function actualizarRazasBuscadasApareamiento(mascota)
        {
            var $select = $('#apareamiento_raza_buscada');
            var especieId = mascota ? (mascota.especie_id || mascota.especie) : null;

            $select.prop('disabled', true)
                .empty()
                .append($('<option></option>').val('').text(
                    especieId ? 'Cargando razas...' : 'Seleccione primero una mascota'
                ));

            if (!especieId || especieId === '0') return;

            var catalogo = window.razasMascotasPorEspecie || razasMascotasPorEspecie || {};
            var razas = catalogo[String(especieId)] || razasMascotasCache[especieId] || null;
            if (razas) {
                poblarRazasBuscadasApareamiento(razas, '');
                return;
            }

            $.get(rutaRazasMascotas.replace('__id__', especieId))
                .done(function(data){
                    var razasAjax = (data && data.razas) ? data.razas : [];
                    razasMascotasCache[especieId] = razasAjax;
                    poblarRazasBuscadasApareamiento(razasAjax, '');
                })
                .fail(function(){
                    $select.empty()
                        .append($('<option></option>').val('').text('No fue posible cargar las razas'))
                        .prop('disabled', true);
                });
        }

        function actualizarResumenMascotaApareamiento()
        {
            var id = $('#apareamiento_mascota_id').val();
            var mascota = mascotasCache[id];
            if (!id || !mascota) {
                $('#apareamiento_mascota_resumen').addClass('d-none');
                resetSexoApareamientoAutomatico();
                actualizarRazasBuscadasApareamiento(null);
                return;
            }

            $('#apareamiento_resumen_nombre').text(mascota.nombre || '-');
            $('#apareamiento_resumen_especie').text(obtenerLabelEspecie(mascota.especie_id || mascota.especie, mascota.otra_especie));
            $('#apareamiento_resumen_raza').text(obtenerRazaMascotaTexto(mascota));
            $('#apareamiento_resumen_sexo').text(sexoMascotaTexto(mascota.sexo));
            $('#apareamiento_resumen_edad').text(obtenerEdadMascotaTexto(mascota));
            $('#apareamiento_mascota_resumen').removeClass('d-none');
            actualizarSexoApareamientoAutomatico(mascota.sexo);
            actualizarRazasBuscadasApareamiento(mascota);
        }

        function resetSolicitudApareamientoModal()
        {
            $('#apareamiento_mascota_id').val('');
            resetSexoApareamientoAutomatico();
            actualizarRazasBuscadasApareamiento(null);
            $('#apareamiento_edad_minima').val('');
            $('#apareamiento_edad_maxima').val('');
            $('#apareamiento_tamano_id').val('');
            $('#apareamiento_color_pelaje').val('');
            $('#apareamiento_pedigree').val('indiferente');
            $('#apareamiento_ubicacion').val('');
            $('#apareamiento_observaciones').val('');
            $('#apareamiento_mascota_resumen').addClass('d-none');
        }

        function abrirModalSolicitudApareamiento(event)
        {
            if (event && typeof event.preventDefault === 'function') {
                event.preventDefault();
            }

            var $modal = $('#modal_solicitud_apareamiento');
            if (!$modal.length) {
                swal({
                    title: 'Modal no disponible',
                    text: 'No se encontró el formulario de solicitud. Recargue la página.',
                    icon: 'error',
                });
                return false;
            }

            resetSolicitudApareamientoModal();
            poblarSelectApareamientoMascotas();

            if ($('#apareamiento_mascota_id option').length <= 1) {
                swal({
                    title: 'Sin mascotas',
                    text: 'Debe tener al menos una mascota registrada para publicar una solicitud.',
                    icon: 'info',
                });
                return false;
            }

            if ($modal.parent().length && !$modal.parent().is('body')) {
                $modal.appendTo('body');
            }

            $modal.modal('show');
            cargarBuzonApareamiento();
            return false;
        }

        window.abrirModalSolicitudApareamiento = abrirModalSolicitudApareamiento;

        function actualizarBadgeApareamiento(nuevas)
        {
            var total = parseInt(nuevas, 10) || 0;
            var $badges = $('#badge-apareamiento-nuevas, #apareamiento_badge_nuevas');
            if (total > 0) {
                $badges.text(total).removeClass('d-none');
            } else {
                $badges.addClass('d-none').text('0');
            }
        }

        function sexoApareamientoLabel(valor)
        {
            if (valor === 'M') return 'Macho';
            if (valor === 'F') return 'Hembra';
            return 'Indiferente';
        }

        function renderBuzonApareamientoRecibidas(recibidas)
        {
            var $lista = $('#apareamiento_lista_recibidas');
            if (!recibidas || !recibidas.length) {
                $lista.html('<div class="alert alert-secondary mb-0">No hay solicitudes recibidas por ahora.</div>');
                return;
            }

            var html = '';
            recibidas.forEach(function(item){
                var esNueva = (item.estado || '') === 'nueva';
                html += '<div class="apareamiento-section mb-2">';
                html += '  <div class="apareamiento-section-body">';
                html += '    <div class="d-flex justify-content-between align-items-start">';
                html += '      <div>';
                if (esNueva) {
                    html += '        <span class="badge badge-danger mb-1">Nueva</span><br>';
                }
                html += '        <strong>' + (item.mascota_origen_nombre || item.mascota_nombre || 'Mascota') + '</strong>';
                html += '        <div class="text-muted small">Para su mascota: <strong>' + (item.mascota_destino_nombre || '-') + '</strong></div>';
                html += '        <div class="small mt-1">';
                html += '          Busca: ' + sexoApareamientoLabel(item.sexo_buscado) + ', raza <strong>' + (item.raza_buscada || '-') + '</strong>';
                if (item.edad_minima || item.edad_maxima) {
                    html += ' · Edad: ' + (item.edad_minima || 'sin mín.') + ' - ' + (item.edad_maxima || 'sin máx.');
                }
                if (item.ubicacion) {
                    html += '<br>Ubicación: ' + item.ubicacion;
                }
                if (item.observaciones) {
                    html += '<br>Detalle: ' + item.observaciones;
                }
                if (item.contacto_telefono || item.contacto_email) {
                    html += '<br>Contacto: ' + [item.contacto_telefono, item.contacto_email].filter(Boolean).join(' · ');
                }
                html += '        </div>';
                html += '      </div>';
                if (esNueva) {
                    html += '      <button type="button" class="btn btn-outline-primary btn-xs btn-apareamiento-marcar-leida"';
                    html += ' data-mascota="' + (item.mascota_destino_id || '') + '" data-solicitud="' + (item.id || '') + '">';
                    html += '        Marcar leída</button>';
                }
                html += '    </div>';
                html += '  </div>';
                html += '</div>';
            });

            $lista.html(html);
        }

        function cargarBuzonApareamiento()
        {
            if (!rutaApareamientoBuzon) return;

            $.ajax({
                url: rutaApareamientoBuzon,
                type: 'GET',
            })
            .done(function(data){
                if (data.estado != 1) return;
                actualizarBadgeApareamiento(data.nuevas || 0);
                renderBuzonApareamientoRecibidas(data.recibidas || []);
            });
        }

        function marcarApareamientoLeida(mascotaId, solicitudId)
        {
            if (!mascotaId || !solicitudId) return;

            $.ajax({
                url: rutaApareamientoLeida
                    .replace('__MASCOTA__', mascotaId)
                    .replace('__SOL__', solicitudId),
                type: 'POST',
                data: { _token: CSRF_TOKEN },
            })
            .done(function(){
                cargarBuzonApareamiento();
            });
        }

        function guardarSolicitudApareamiento()
        {
            var mascotaId = $('#apareamiento_mascota_id').val();
            var sexoBuscado = $('#apareamiento_sexo_buscado').val();
            var razaBuscada = ($('#apareamiento_raza_buscada').val() || '').trim();

            if (!mascotaId) {
                swal({ title: 'Seleccione mascota', text: 'Debe elegir la mascota para la solicitud.', icon: 'warning' });
                return;
            }
            if (!sexoBuscado || !razaBuscada) {
                var mensaje = !sexoBuscado
                    ? 'Registre el sexo de su mascota en la ficha antes de publicar la solicitud.'
                    : 'Indique la raza buscada.';
                swal({ title: 'Datos incompletos', text: mensaje, icon: 'warning' });
                return;
            }

            $.ajax({
                url: rutaApareamientoGuardar.replace('__ID__', mascotaId),
                type: 'POST',
                data: {
                    _token: CSRF_TOKEN,
                    sexo_buscado: sexoBuscado,
                    raza_buscada: razaBuscada,
                    edad_minima: $('#apareamiento_edad_minima').val(),
                    edad_maxima: $('#apareamiento_edad_maxima').val(),
                    tamano_id: $('#apareamiento_tamano_id').val(),
                    color_pelaje: $('#apareamiento_color_pelaje').val(),
                    pedigree: $('#apareamiento_pedigree').val(),
                    ubicacion: $('#apareamiento_ubicacion').val(),
                    observaciones: $('#apareamiento_observaciones').val(),
                    contacto_telefono: $('#apareamiento_contacto_telefono').val(),
                    contacto_email: $('#apareamiento_contacto_email').val(),
                },
            })
            .done(function(data){
                if (data.estado == 1) {
                    $('#modal_solicitud_apareamiento').modal('hide');
                    swal({
                        title: 'Solicitud publicada',
                        text: data.msj || 'Su solicitud de apareamiento fue registrada.',
                        icon: 'success',
                    });
                    cargarBuzonApareamiento();
                } else {
                    swal({
                        title: 'No se pudo publicar',
                        text: data.msj || 'Intente nuevamente.',
                        icon: 'error',
                    });
                }
            })
            .fail(function(jqXHR){
                var msj = (jqXHR.responseJSON && jqXHR.responseJSON.msj)
                    ? jqXHR.responseJSON.msj
                    : 'No se pudo guardar la solicitud.';
                swal({ title: 'Error', text: msj, icon: 'error' });
            });
        }

        function resetModalFallecimientoMascota()
        {
            $('#fallecimiento_mascota_id').val('');
            $('#fallecimiento_mascota_nombre').text('—');
            $('#fallecimiento_fecha').val('');
            $('#fallecimiento_causa').val('');
            $('#fallecimiento_mensaje').val('');
            $('#fallecimiento_incluir_fotos_actuales').prop('checked', true);
            $('#fallecimiento_album_json').val('[]');
            albumMemorialTemporal = [];
            if (dropzoneMemorialMascota && typeof dropzoneMemorialMascota.removeAllFiles === 'function') {
                dropzoneMemorialMascota.removeAllFiles(true);
            }
        }

        function abrirModalFallecimientoMascota(idMascota, event)
        {
            if (event && typeof event.preventDefault === 'function') {
                event.preventDefault();
            }

            if (!idMascota) {
                swal({
                    title: 'Mascota no seleccionada',
                    text: 'No se pudo identificar la mascota.',
                    icon: 'warning',
                });
                return false;
            }

            var mascota = obtenerMascotaPorId(idMascota);
            if (!mascota) {
                swal({
                    title: 'Mascota no encontrada',
                    text: 'Recargue la página e intente nuevamente.',
                    icon: 'warning',
                });
                return false;
            }

            if (mascotaEstaFallecida(mascota)) {
                swal({
                    title: 'Ya registrada',
                    text: 'Esta mascota ya figura como fallecida.',
                    icon: 'info',
                });
                return false;
            }

            if (!rutaFallecimientoGuardar) {
                swal({
                    title: 'Acción no disponible',
                    text: 'No se encontró la ruta para registrar el fallecimiento.',
                    icon: 'error',
                });
                return false;
            }

            var $modal = $('#modal_fallecimiento_mascota');
            if (!$modal.length) {
                swal({
                    title: 'Modal no disponible',
                    text: 'No se encontró el formulario de fallecimiento. Recargue la página e intente nuevamente.',
                    icon: 'error',
                });
                return false;
            }

            resetModalFallecimientoMascota();
            $('#fallecimiento_mascota_id').val(mascota.id);
            $('#fallecimiento_mascota_nombre').text(mascota.nombre || '—');
            $('#modal_detalle_mascota').modal('hide');

            if ($modal.parent().length && !$modal.parent().is('body')) {
                $modal.appendTo('body');
            }

            $modal.modal('show');
            return false;
        }

        function abrirModalFallecimientoDesdeDetalle(event)
        {
            if (event && typeof event.preventDefault === 'function') {
                event.preventDefault();
            }

            var idMascota = $('#modal_detalle_mascota').data('id') || $('#fallecimiento_mascota_id').val();
            return abrirModalFallecimientoMascota(idMascota, event);
        }

        function confirmarFallecimientoMascota()
        {
            var mascotaId = $('#fallecimiento_mascota_id').val();
            var fecha = $('#fallecimiento_fecha').val();
            if (!mascotaId) {
                swal({ title: 'Error', text: 'No se identificó la mascota.', icon: 'error' });
                return;
            }
            if (!fecha) {
                swal({ title: 'Fecha requerida', text: 'Indique la fecha de fallecimiento.', icon: 'warning' });
                return;
            }
            if (!rutaFallecimientoGuardar) {
                swal({ title: 'Error', text: 'Ruta de guardado no disponible.', icon: 'error' });
                return;
            }

            var album = [];
            try {
                album = JSON.parse($('#fallecimiento_album_json').val() || '[]');
            } catch (e) {
                album = [];
            }

            var $btn = $('#btn_confirmar_fallecimiento');
            if ($btn.data('guardando')) return;
            $btn.data('guardando', true).prop('disabled', true);

            var urlGuardar = String(rutaFallecimientoGuardar).replace('__MASCOTA__', mascotaId);

            $.ajax({
                url: urlGuardar,
                type: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': CSRF_TOKEN,
                },
                data: {
                    _token: CSRF_TOKEN,
                    fecha_fallecimiento: fecha,
                    causa: $('#fallecimiento_causa').val(),
                    mensaje: $('#fallecimiento_mensaje').val(),
                    album: JSON.stringify(album),
                    incluir_fotos_actuales: $('#fallecimiento_incluir_fotos_actuales').is(':checked') ? 1 : 0,
                },
            })
            .done(function(data, textStatus, jqXHR) {
                data = parsearRespuestaAjax(data);
                if (!data && jqXHR && jqXHR.responseText) {
                    data = parsearRespuestaAjax(jqXHR.responseText);
                }

                if (!esRespuestaExitosa(data)) {
                    var textoError = (data && data.msj) ? data.msj : 'No se pudo registrar el fallecimiento.';
                    if (data && data.error) {
                        Object.keys(data.error).forEach(function(campo) {
                            var mensajes = data.error[campo];
                            if (Array.isArray(mensajes)) {
                                mensajes.forEach(function(msg) { textoError += '\n' + msg; });
                            }
                        });
                    }
                    swal({ title: 'No se pudo registrar', text: textoError, icon: 'error' });
                    return;
                }

                $('#modal_fallecimiento_mascota').modal('hide');
                swal({
                    title: 'Registro guardado',
                    text: data.msj || 'Se registró el fallecimiento de la mascota.',
                    icon: 'success',
                }).then(function() {
                    if (data.memorial_path) {
                        window.location.href = urlAppRuta(data.memorial_path);
                    } else {
                        cargarDependientes();
                    }
                });
            })
            .fail(function(jqXHR, textStatus) {
                var data = jqXHR.responseJSON || parsearRespuestaAjax(jqXHR.responseText);

                if (esRespuestaExitosa(data)) {
                    $('#modal_fallecimiento_mascota').modal('hide');
                    swal({
                        title: 'Registro guardado',
                        text: data.msj || 'Se registró el fallecimiento de la mascota.',
                        icon: 'success',
                    }).then(function() {
                        if (data.memorial_path) {
                            window.location.href = urlAppRuta(data.memorial_path);
                        } else {
                            cargarDependientes();
                        }
                    });
                    return;
                }

                var texto = 'No se pudo registrar el fallecimiento.';
                if (jqXHR.status === 419) {
                    texto = 'La sesión expiró. Recargue la página e intente nuevamente.';
                } else if (data && data.msj) {
                    texto = data.msj;
                } else if (data && data.message) {
                    texto = data.message;
                } else if (textStatus === 'parsererror') {
                    texto = 'Respuesta inesperada del servidor. Recargue la página e intente nuevamente.';
                }

                swal({ title: 'Error', text: texto, icon: 'error' });
            })
            .always(function() {
                $btn.data('guardando', false).prop('disabled', false);
            });
        }
        window.abrirModalFallecimientoMascota = abrirModalFallecimientoMascota;
        window.abrirModalFallecimientoDesdeDetalle = abrirModalFallecimientoDesdeDetalle;
        window.confirmarFallecimientoMascota = confirmarFallecimientoMascota;

        $(document).on('shown.bs.modal', '#modal_fallecimiento_mascota', function() {
            initDropzoneMemorialMascota();
        });
        $(document).on('hidden.bs.modal', '#modal_fallecimiento_mascota', function() {
            resetModalFallecimientoMascota();
        });

        function eliminarMascota(idMascota)
        {
            var mascota = mascotasCache[idMascota];
            var nombre = mascota ? mascota.nombre : '';
            swal({
                title: "Eliminar mascota",
                text: nombre ? "¿Desea eliminar a " + nombre + "?" : "¿Desea eliminar esta mascota?",
                icon: "warning",
                buttons: ["Cancelar", "Eliminar"],
                dangerMode: true,
            }).then(function(confirmado){
                if(!confirmado) return;

                $.ajax({
                    url: rutaMascotasBase + '/' + idMascota,
                    type: "DELETE",
                    data: {_token: CSRF_TOKEN},
                })
                .done(function(data){
                    if(data.estado == 1)
                    {
                        $('#modal_detalle_mascota').modal('hide');
                        swal({
                            title: "Mascota eliminada.",
                            text:"Exito",
                            icon: "success",
                        });
                        cargarDependientes();
                    }
                    else
                    {
                        swal({
                            title: "Eliminar mascota.",
                            text: data.msj || "No se pudo eliminar la mascota.",
                            icon: "error",
                        });
                    }
                })
                .fail(function(jqXHR, ajaxOptions, thrownError) {
                    console.log(jqXHR, ajaxOptions, thrownError)
                });
            });
        }

        $(document).ready(function () {
            @if(($titulo ?? '') === 'Mascotas')
            $('#div_relaciones').hide();

            $('#modal_traspasar_mascota').on('hidden.bs.modal', function(){
                resetTraspasoModal();
            });

            $('#modal_traspasar_mascota').on('shown.bs.modal', function(){
                var $rut = $('#traspaso_rut_tutor');
                if ($.fn.rut && $rut.length && !$rut.data('traspaso-rut-init')) {
                    $rut.rut({
                        formatOn: 'keyup',
                        minimumLength: 2,
                        validateOn: 'change',
                        useThousandsSeparator: false
                    });
                    $rut.data('traspaso-rut-init', true);
                }
                sincronizarLabelsFlotantesTraspaso();
            });

            $('#traspaso_mascota_id').on('change', function(){
                traspasoTutorEncontrado = null;
                $('#traspaso_tutor_resultado').addClass('d-none');
                $('#traspaso_tutor_formulario').addClass('d-none');
                $('#traspaso_tutor_alerta').addClass('d-none').text('');
                $('#traspaso_tutor_error').addClass('d-none').text('');
                $('#traspaso_resumen_bloque').addClass('d-none');
                actualizarBotonTraspaso();
            });

            $('#traspaso_situacion').on('input', function(){
                actualizarBotonTraspaso();
            });

            $('#traspaso_tutor_formulario').on('input change', 'input', function(){
                if (traspasoTutorEncontrado && traspasoTutorEncontrado.requiere_formulario) {
                    $('#traspaso_resumen_bloque').addClass('d-none');
                }
                actualizarBotonTraspaso();
            });

            $('#btn_traspaso_buscar_tutor').on('click', function(){
                var mascotaId = $('#traspaso_mascota_id').val();

                if(!mascotaId)
                {
                    swal({
                        title: 'Seleccione mascota',
                        text: 'Primero debe seleccionar la mascota a traspasar.',
                        icon: 'warning',
                    });
                    return;
                }

                buscarTutorTraspaso();
            });

            $('#btn_traspaso_validar_tutor').on('click', function(){
                validarTutorTraspaso();
            });

            $('#btn_traspaso_confirmar').on('click', function(){
                ejecutarTraspasoMascota();
            });

            $('#modal_solicitud_apareamiento').on('hidden.bs.modal', function(){
                resetSolicitudApareamientoModal();
            });

            $('#apareamiento_mascota_id').on('change', function(){
                actualizarResumenMascotaApareamiento();
            });

            $('#btn_apareamiento_guardar').on('click', function(){
                guardarSolicitudApareamiento();
            });

            $(document).on('click', '.btn-apareamiento-marcar-leida', function(){
                marcarApareamientoLeida($(this).data('mascota'), $(this).data('solicitud'));
            });

            cargarBuzonApareamiento();
            @endif

            try {
                inicializarTablaFichaMascota();
            } catch (error) {
                console.error('No se pudo inicializar la tabla de ficha mascota', error);
            }
            $('#btn-agregar-dep').click(function (e) {
                e.preventDefault();
                limpiarFormularioMascota();
                $('#modal_agregar_dep_nuevo').modal('show');
            });
            $('#btn_editar_mascota').on('click', function(){
                var idMascota = $('#modal_detalle_mascota').data('id');
                if(idMascota) abrirEdicionMascota(idMascota);
            });
            $('#btn_eliminar_mascota').on('click', function(){
                var idMascota = $('#modal_detalle_mascota').data('id');
                if(idMascota) eliminarMascota(idMascota);
            });
            $('#btn_limpiar_fotos_actuales').on('click', function(){
                limpiarFotosActuales();
            });

            $("#modal_agregar_dep_input_rut").rut({
                formatOn: 'keyup',
                minimumLength: 2,
                validateOn: 'change',
                useThousandsSeparator : false
            });

            handleEspecieChange();
            toggleEsterilizacion();
            toggleChipInput();
            toggleFechaNacimientoDesconocida();
            registrarMascotasIniciales();
            if (Array.isArray(mascotasIniciales) && mascotasIniciales.length) {
                pintarTarjetasMascotas(mascotasIniciales);
                $('#card-lista-dependientes').removeClass('d-none');
            }
            cargarDependientes();

            $(document).on('click', '.btn-ver-mascota', function(e){
                e.stopPropagation();
                var idMascota = $(this).data('id') || $(this).closest('.card-mascota').data('id');
                mostrarDetalleMascota(idMascota);
            });

            $('#modal_ficha_mascota').on('shown.bs.modal', function() {
                var tablaFichaMascota = inicializarTablaFichaMascota();
                if (tablaFichaMascota && tablaFichaMascota.columns) {
                    tablaFichaMascota.columns.adjust().draw(false);
                }
            });

            $(document).on('click', '.btn-escritorio-mascota', function(e){
                e.stopPropagation();
                var idMascota = $(this).data('id') || $(this).closest('.card-mascota').data('id');
                if (mascotaEstaFallecida(mascotasCache[idMascota])) return;
                var escritorioUrl = "{{ route('paciente.dependiente.home', ['id_dependiente_activo' => ':id']) }}";
                window.location.href = escritorioUrl.replace(':id', idMascota);
            });

            $(document).on('click', '.card-mascota', function(e){
                if ($(e.target).closest('a, button').length) return;
                var idMascota = $(this).data('id');
                mostrarDetalleMascota(idMascota);
            });
        });

        function limpiarFormularioMascota()
        {
            setModoEdicion(null);
            limpiarFotosActuales();
            $('#modal_agregar_dep_nuevo_tiene_chip').val('0');
            toggleChipInput();
            $('#modal_agregar_dep_nuevo_rut').val('');
            $('#modal_agregar_dep_nuevo_nombres_paciente').val('');
            $('#espec_masc').val('0');
            handleEspecieChange();
            $('#modal_agregar_dep_nuevo_tamano').val('');
            $('#modal_agregar_dep_nuevo_raza').val('');
            $('#modal_agregar_dep_nuevo_fecha_nac').val('');
            $('#modal_agregar_dep_nuevo_fecha_nac_desconocida').prop('checked', false);
            $('#modal_agregar_dep_nuevo_sexo').val('0');
            $('#modal_agregar_dep_nuevo_esterilizado').val('');
            $('#modal_agregar_dep_nuevo_fecha_esterilizacion').val('');
            $('#modal_agregar_dep_nuevo_fecha_esterilizacion_desconocida').prop('checked', false);
            $('#modal_agregar_dep_nuevo_enfermedad_cronica').val('');
            $('#modal_agregar_dep_nuevo_dieta').val('');
            $('#modal_agregar_dep_nuevo_cirugias').val('');
            $('#modal_agregar_dep_nuevo_vacunas').val('');
            $('#modal_agregar_dep_nuevo_viajes').val('');
            $('#modal_agregar_dep_nuevo_ultima_desparasitacion').val('');
            $('#modal_agregar_dep_nuevo_producto_desparasitacion').val('');
            $('#modal_agregar_dep_nuevo_vive_con_animales').val('');
            toggleEsterilizacion();
            toggleFechaNacimientoDesconocida();
            toggleFechaEsterilizacionDesconocida();
            $('#imagenes_ven_pre').val('');
            $('#imagenes_ven_post').val('');
            $('#input_lista_ven_imagenes').val('');
            $('#obs_fotos_ven').val('');
            $('#btn_registrar').show();
            lista_ven_imagenes = {};
        }

        function renderizarFilasFichaMascota(registros)
        {
            var $tbody = $('#tabla_ficha_mascota tbody');
            $tbody.html('');

            if (!Array.isArray(registros) || registros.length === 0) {
                $tbody.append(construirFilaEstadoFichaMascota('Sin registros'));
                return;
            }

            $.each(registros, function(_, ficha) {
                var profesionalLugar = '<strong>' + (ficha.profesional || 'Sin profesional registrado') + '</strong><br>' + (ficha.lugar_atencion || 'Sin lugar registrado');
                var html = '';
                html += '<tr>';
                html += '  <td>' + (ficha.fecha || '-') + '</td>';
                html += '  <td>' + (ficha.diagnostico || '-') + '</td>';
                html += '  <td>' + (ficha.indicaciones || '-') + '</td>';
                html += '  <td>' + profesionalLugar + '</td>';
                html += '</tr>';
                $tbody.append(html);
            });
        }

        function cargarFichaMascota(idMascota)
        {
            var tablaFichaMascota = inicializarTablaFichaMascota();
            if (tablaFichaMascota && tablaFichaMascota.clear) {
                tablaFichaMascota.clear().draw();
            }

            renderizarFilasFichaMascota([]);
            $('#tabla_ficha_mascota tbody').html(construirFilaEstadoFichaMascota('Cargando registros...'));

            $.ajax({
                url: rutaMascotasBase + '/' + idMascota + '/fichas',
                type: 'GET',
            }).done(function(resp) {
                resp = normalizarRespuestaAjax(resp);
                renderizarFilasFichaMascota(resp.registros || []);

                var tabla = inicializarTablaFichaMascota();
                if (tabla && tabla.rows) {
                    tabla.rows().invalidate().draw(false);
                    tabla.columns.adjust().draw(false);
                }
            }).fail(function() {
                $('#tabla_ficha_mascota tbody').html(construirFilaEstadoFichaMascota('No fue posible cargar la ficha médica.', 'text-danger'));
            });
        }

        function normalizarRespuestaAjax(data)
        {
            if (typeof data === 'string') {
                try {
                    return JSON.parse(data);
                } catch (error) {
                    return {};
                }
            }

            return data || {};
        }

        function toggleChipInput()
        {
            var tiene_chip = $('#modal_agregar_dep_nuevo_tiene_chip').val();
            var mostrar = (tiene_chip === '1');

            $('#contenedor_numero_chip').toggle(mostrar);
            $('#modal_agregar_dep_nuevo_rut').prop('required', mostrar);
            if(mostrar)
            {
                $('#requerido_modal_agregar_dep_nuevo_rut').show();
            }
            else
            {
                $('#requerido_modal_agregar_dep_nuevo_rut').hide();
                $('#modal_agregar_dep_nuevo_rut').val('');
            }
        }

        function toggleEsterilizacion()
        {
            var esterilizado = $('#modal_agregar_dep_nuevo_esterilizado').val();
            var mostrar = (esterilizado === '1');
            $('#contenedor_fecha_esterilizacion').toggle(mostrar);
            $('#requerido_modal_agregar_dep_nuevo_fecha_esterilizacion').toggle(mostrar);
            if(!mostrar)
            {
                $('#modal_agregar_dep_nuevo_fecha_esterilizacion').val('');
                $('#modal_agregar_dep_nuevo_fecha_esterilizacion_desconocida').prop('checked', false);
            }
            toggleFechaEsterilizacionDesconocida();
        }

        function toggleFechaNacimientoDesconocida()
        {
            var desconocida = $('#modal_agregar_dep_nuevo_fecha_nac_desconocida').is(':checked');
            $('#modal_agregar_dep_nuevo_fecha_nac').prop('disabled', desconocida);
            if(desconocida)
            {
                $('#modal_agregar_dep_nuevo_fecha_nac').val('');
            }
        }

        function toggleFechaEsterilizacionDesconocida()
        {
            var esterilizado = $('#modal_agregar_dep_nuevo_esterilizado').val();
            var mostrar = (esterilizado === '1');
            var desconocida = $('#modal_agregar_dep_nuevo_fecha_esterilizacion_desconocida').is(':checked');
            $('#modal_agregar_dep_nuevo_fecha_esterilizacion').prop('disabled', mostrar && desconocida);
            $('#modal_agregar_dep_nuevo_fecha_esterilizacion').prop('required', mostrar && !desconocida);
            if(mostrar && desconocida)
            {
                $('#modal_agregar_dep_nuevo_fecha_esterilizacion').val('');
            }
        }

        function buscar_rut_dep()
        {
            let rut = $('#modal_agregar_dep_input_rut').val();
            if(rut != '')
            {
                let url = "{{ route('paciente.buscar_rut_paciente') }}";
                $.ajax({

                    url: url,
                    type: "get",
                    data: {
                        rut: rut,
                    },
                })
                .done(function(data) {


                    if (data !== 'null') {

                        data = JSON.parse(data);
                        if(data.tipo_paciente == 'SI')
                        {
                            $('#id_paciente_dependiente').val('');
                            $('#label_agregar_nombre_paciente').html('');
                            $('#label_agregar_apellido_paciente').html('');
                            $('#label_agregar_rut_paciente').html('');

                            console.log('rut encontrado');
                            console.log(data.fecha_nac);

                            var edad_temp = calcularEdad_valor(data.fecha_nac);
                            console.log(edad_temp);
                            console.log($('#dependencia').val());

                            if(edad_temp < 18 && $('#dependencia').val() == 1)
                            {
                                /** mascotas*/
                                $('#modal_agregar_dep_buscar').modal('hide');

                                cargar_select_relacion('agregar_relacion','agregar_tipo_dependencia');

                                $('#modal_agregar_dep_existente').modal('show');

                                $('#id_paciente_dependiente').val(data.id);
                                $('#label_agregar_nombre_paciente').html(data.nombres);
                                $('#label_agregar_apellido_paciente').html(data.apellido_uno + ' ' + data.apellido_dos);
                                $('#label_agregar_rut_paciente').html(data.rut);
                            }
                            else if(edad_temp >= 18 && $('#dependencia').val() == 2)
                            {
                                /** mayor edad */
                                $('#modal_agregar_dep_buscar').modal('hide');

                                cargar_select_relacion('agregar_relacion','agregar_tipo_dependencia');

                                $('#modal_agregar_dep_existente').modal('show');

                                $('#id_paciente_dependiente').val(data.id);
                                $('#label_agregar_nombre_paciente').html(data.nombres);
                                $('#label_agregar_apellido_paciente').html(data.apellido_uno + ' ' + data.apellido_dos);
                                $('#label_agregar_rut_paciente').html(data.rut);
                            }
                            else
                            {
                                var mensaje = '';
                                if(edad_temp < 18 && $('#dependencia').val() == 2)
                                    mensaje = 'Esta intentando registrar\n El Paciente '+data.apellido_uno + ' ' + data.apellido_dos+' que es Menor de Edad como Dependiente Mayor de Edad.';
                                else if(edad_temp >= 18 && $('#dependencia').val() == 1)
                                    mensaje = 'Esta intentando registrar\n El Paciente '+data.apellido_uno + ' ' + data.apellido_dos+' que es Mayor de Edad como Dependiente Menor de Edad.';

                                swal({
                                    title: "Busqueda de Paciente por RUT",
                                    text:mensaje,
                                    icon: "error",
                                    // buttons: "Aceptar",
                                    //SuccessMode: true,
                                });
                                return false;
                            }
                        }
                        else
                        {
                            $('#modal_agregar_dep_nuevo_nombres_paciente').val('');
                            $('#modal_agregar_dep_nuevo_apellido_uno').val('');
                            $('#modal_agregar_dep_nuevo_apellido_dos').val('');
                            $('#modal_agregar_dep_nuevo_fecha_nac').val('');
                            $('#modal_agregar_dep_nuevo_sexo').val('');
                            $('#modal_agregar_dep_nuevo_convenio').val('');
                            $('#modal_agregar_dep_nuevo_direccion').val('');
                            $('#modal_agregar_dep_nuevo_numero_dir').val('');
                            $('#modal_agregar_dep_nuevo_region').val('');
                            $('#modal_agregar_dep_nuevo_ciudad').val('');
                            $('#modal_agregar_dep_nuevo_correo').val('');
                            $('#modal_agregar_dep_nuevo_telefono_uno').val('');
                            $('#modal_agregar_dep_nuevo_relacion').val('');
                            $('#modal_agregar_dep_nuevo_tipo_dependencia').val('');
                            // $('#modal_agregar_dep_nuevo_fecha_inicio').val('');
                            $('#modal_agregar_dep_nuevo_fecha_termino').val('');
                            $('#modal_agregar_dep_nuevo_comentario').val('');

                            console.log('rut no encontrado');
                            $('#modal_agregar_dep_buscar').modal('hide');

                            cargar_select_relacion('modal_agregar_dep_nuevo_relacion','modal_agregar_dep_nuevo_tipo_dependencia');

                            $('#div_relaciones').show();
                            $('#btn_registrar').show();
                            $('#mensaje_edad').hide();
                            $('#mensaje_edad').html('');

                            $('#modal_agregar_dep_nuevo').modal('show');
                            $('#modal_agregar_dep_nuevo_rut').val(rut);
                        }

                    } else {
                        console.log('sin respuesta de consulta');
                    }

                })
                .fail(function(jqXHR, ajaxOptions, thrownError) {
                    console.log(jqXHR, ajaxOptions, thrownError)
                });
            }
            else
            {
                swal({
                    title: "Busqueda de Paciente por RUT",
                    text:"Debe ingresar un RUT para la busqueda.",
                    icon: "error",
                    // buttons: "Aceptar",
                    //SuccessMode: true,
                });
            }
        };

        function cargar_select_relacion(select, select_tipo_dependencia)
        {
            /* menor edad 1, mayor edad 2 */
            var dependencia = $('#dependencia').val();

            if(dependencia == '1')
            {
                var html = '';
                html += '<option data-tipo="1" value="Hijo(a)" selected>Hijo(a)</option>';
                html += '<option data-tipo="1" value="Sobrino(a)">Sobrino(a)</option>';
                html += '<option data-tipo="1" value="Nieto(a)">Nieto(a)</option>';
                html += '<option data-tipo="1" value="Hermano(a)">Hermano(a)</option>';
                html += '<option data-tipo="1" value="Primo(a)">Primo(a)</option>';
                $('#'+select).html(html);
            }
            else if(dependencia == '2')
            {
                var html = '';
                html += '<option data_tipo="2" value="Padre - Madre" selected>Padre - Madre</option>';
                html += '<option data_tipo="2" value="Esposo(a)">Esposo(a)</option>';
                html += '<option data_tipo="2" value="Hermano(a)">Hermano(a)</option>';
                html += '<option data-tipo="2" value="Nieto(a)">Nieto(a)</option>';
                html += '<option data_tipo="2" value="Primo(a)">Primo(a)</option>';
                html += '<option data-tipo="2" value="Sobrino(a)">Sobrino(a)</option>';
                $('#'+select).html(html);
            }
            cargar_tipo_dependencia(select, select_tipo_dependencia);
        }

        function cargar_tipo_dependencia(select_relacion, select)
        {
            @unless(Route::has('tipo_dependencia.lista'))
            return;
            @endunless
            var tipo_dependencias = $('#tipo_dependencias').val();
            var tipo = $('#'+select_relacion+' option:selected').data('tipo')
            let url = @json(Route::has('tipo_dependencia.lista') ? route('tipo_dependencia.lista') : '');
            if (!url) {
                return;
            }

            $.ajax({

                    url: url,
                    type: "GET",
                    data: {
                        tipo: tipo,
                        id: tipo_dependencias,
                    },
                })
                .done(function(data) {
                    var html = '';
                    if(data.estado  == 1)
                    {
                        $.each(data.registros, function (indexInArray, valueOfElement) {
                            var selected = '';
                            if(indexInArray == 0)
                                selected = 'selected';
                            else
                                selected = '';
                            html += '<option value="'+valueOfElement.id+'" '+selected+'>'+valueOfElement.nombre+'</option>';
                        });
                    }
                    else
                    {
                        html = '<option value="">Seleccione</option>';
                    }
                    $('#'+select).html(html);

                    evaluar_tipodependencia('modal_agregar_dep_nuevo_tipo_dependencia', 'modal_agregar_dep_nuevo_fechas', '2,4');
                })
                .fail(function(jqXHR, ajaxOptions, thrownError) {
                    console.log(jqXHR, ajaxOptions, thrownError)
                });
        }

        function evaluar_tipodependencia(select, div, option)
        {
            var valor = $('#'+select).val();

            var option = option.split(',');

            if(valor == 0)
            {
                $('#'+div).hide();
                $('#agregar_fecha_inicio').val('');
                $('#agregar_fecha_termino').val('');
            }
            else
            {
                console.log(valor);
                console.log(option);
                // if(valor == option)
                if($.inArray(valor, option) > -1)
                {
                    $('#'+div).show();
                    $('#agregar_fecha_inicio').val('{{ date("Y-m-d")}}');
                    $('#agregar_fecha_termino').val('');
                }
                else
                {
                    $('#'+div).hide();
                    $('#agregar_fecha_inicio').val('');
                    $('#agregar_fecha_termino').val('');
                }
            }
        }

        function registrar_dep_existente()
        {

            var id_paciente_dependiente = $('#id_paciente_dependiente').val();
            var relacion = $('#agregar_relacion').val();
            var tipo_dependencia = $('#agregar_tipo_dependencia').val();
            var comentario = $('#agregar_comentario').val();
            var fecha_inicio = $('#agregar_fecha_inicio').val();
            var fecha_termino = $('#agregar_fecha_termino').val();

            let url = "{{ route('paciente.dependientes.registro') }}";
            var datos = {};

            datos._token = CSRF_TOKEN;
            datos.id_paciente = id_paciente_dependiente;
            datos.relacion = relacion;
            datos.tipo_dependencia = tipo_dependencia;
            if(tipo_dependencia == 3)
            {
                datos.fecha_inicio = fecha_inicio;
                datos.fecha_termino = fecha_termino;
            }
            datos.comentario = comentario;
            datos.otro = '';

            $.ajax({

                url: url,
                type: "POST",
                data: datos,
            })
            .done(function(data) {
                if (data.estado == 1)
                {
                    $('#modal_agregar_dep_existente').modal('hide');

                    swal({
                        title: "Registro de Dependiente.",
                        text:"Exito",
                        icon: "success",
                        // buttons: "Aceptar",
                        //SuccessMode: true,
                    });
                }
                else
                {
                    swal({
                        title: "Registro de Dependiente.",
                        text:"Problemas al realizar el registro.\n"+data.msj,
                        icon: "error",
                        // buttons: "Aceptar",
                        //SuccessMode: true,
                    });
                }
                cargarDependientes();

            })
            .fail(function(jqXHR, ajaxOptions, thrownError) {
                console.log(jqXHR, ajaxOptions, thrownError)
            });

        }

        function buscar_ciudad(select_region, select_ciudad, id_ciudad=0) {

            let region = $('#'+select_region).val();
            let url = "{{ route('home.buscar_ciudad_region') }}";
            $.ajax({
                url: url,
                type: "get",
                data: {
                    region: region,
                },
            })
            .done(function(data) {
                if (data != null) {
                    data = JSON.parse(data);

                    let ciudades = $('#'+select_ciudad);

                    ciudades.find('option').remove();
                    ciudades.append('<option value="0">seleccione</option>');
                    $(data).each(function(i, v) { // indice, valor
                        ciudades.append('<option value="' + v.id + '">' + v.nombre + '</option>');
                    })

                    if(id_ciudad != 0)
                        ciudades.val(id_ciudad);

                } else {

                    swal({
                        title: "Error",
                        text: "Error al cargar las ciudades",
                        icon: "error",
                        buttons: "Aceptar",
                        DangerMode: true,
                    })
                    // alert('No se pudo Cargar las ciudades');
                }

            })
            .fail(function(jqXHR, ajaxOptions, thrownError) {
                console.log(jqXHR, ajaxOptions, thrownError)
            });
        };

        /** calculo de edad */
        var edad_actual = 0;
        function calcularEdad(input_fecha)
        {
            fecha = $('#'+input_fecha).val();
            var hoy = new Date();
            var cumpleanos = new Date(fecha);
            var edad = hoy.getFullYear() - cumpleanos.getFullYear();
            var m = hoy.getMonth() - cumpleanos.getMonth();

            if (m < 0 || (m === 0 && hoy.getDate() < cumpleanos.getDate()))
            {
                edad--;
            }
            // $('#age').val(edad);
            edad_actual = edad;
            return edad_actual;
        }

        function calcularEdad_valor(valor)
        {
            fecha = valor;
            var hoy = new Date();
            var cumpleanos = new Date(fecha);
            var edad = hoy.getFullYear() - cumpleanos.getFullYear();
            var m = hoy.getMonth() - cumpleanos.getMonth();

            if (m < 0 || (m === 0 && hoy.getDate() < cumpleanos.getDate()))
            {
                edad--;
            }
            // $('#age').val(edad);
            edad_actual = edad;
            return edad_actual;
        }

        function validar_requeridos(input_fecha)
        {
            $('#div_relaciones').show();
            $('#btn_registrar').show();
            $('#mensaje_edad').hide();
            var mensaje = '';

            if(calcularEdad(input_fecha)<18)
            {
                $('#requerido_modal_agregar_dep_nuevo_correo').hide();
                $('#requerido_modal_agregar_dep_nuevo_telefono_uno').hide();
            }
            else
            {
                $('#requerido_modal_agregar_dep_nuevo_correo').show();
                $('#requerido_modal_agregar_dep_nuevo_telefono_uno').show();
            }

            $('#mensaje_edad').html(mensaje);
            $('#btn_registrar').show();
        }

        function registrar_dep_nuevo()
        {
            var $btnRegistrar = $('#btn_registrar');
            if ($btnRegistrar.data('guardando')) {
                return;
            }

            var mascotaId = $('#mascota_editar_id').val();
            var tiene_chip = $('#modal_agregar_dep_nuevo_tiene_chip').val();
            var chip = $('#modal_agregar_dep_nuevo_rut').val();
            var nombre = $('#modal_agregar_dep_nuevo_nombres_paciente').val();
            var especie = $('#espec_masc').val();
            var razaSeleccionada = $('#modal_agregar_dep_nuevo_raza').val();
            var raza = (razaSeleccionada === 'sin') ? '' : razaSeleccionada;
            var otra_especie = $('#obs_espec_masc').val();
            var tamano = $('#modal_agregar_dep_nuevo_tamano').val();
            var esterilizado = $('#modal_agregar_dep_nuevo_esterilizado').val();
            var fecha_esterilizacion = $('#modal_agregar_dep_nuevo_fecha_esterilizacion').val();
            var enfermedad_cronica = $('#modal_agregar_dep_nuevo_enfermedad_cronica').val();
            var dieta = $('#modal_agregar_dep_nuevo_dieta').val();
            var cirugias = $('#modal_agregar_dep_nuevo_cirugias').val();
            var vacunas = $('#modal_agregar_dep_nuevo_vacunas').val();
            var viajes = $('#modal_agregar_dep_nuevo_viajes').val();
            var ultima_desparasitacion = $('#modal_agregar_dep_nuevo_ultima_desparasitacion').val();
            var producto_desparasitacion = $('#modal_agregar_dep_nuevo_producto_desparasitacion').val();
            var vive_con_animales = $('#modal_agregar_dep_nuevo_vive_con_animales').val();
            var fecha_nac = $('#modal_agregar_dep_nuevo_fecha_nac').val();
            var sexo = $('#modal_agregar_dep_nuevo_sexo').val();
            var fecha_nac_desconocida = $('#modal_agregar_dep_nuevo_fecha_nac_desconocida').is(':checked');
            var fecha_esterilizacion_desconocida = $('#modal_agregar_dep_nuevo_fecha_esterilizacion_desconocida').is(':checked');
            var foto_perfil = sincronizarFotoPerfilDesdeGaleria();
            var galeria = $('#input_lista_ven_imagenes').val();
            var observaciones = $('#obs_fotos_ven').val();

            var valido = 1;
            var mensaje = '';

            if(nombre == '')
            {
                valido = 0;
                mensaje += 'Nombre Mascota: requerido\n';
            }
            if(especie == '' || especie == '0')
            {
                valido = 0;
                mensaje += 'Especie: requerido\n';
            }
            if($('#modal_agregar_dep_nuevo_raza option').length > 1 && razaSeleccionada == '')
            {
                valido = 0;
                mensaje += 'Raza: requerido\n';
            }
            if(requiereDetalleEspecie(especie) && otra_especie == '')
            {
                valido = 0;
                mensaje += 'Debe detallar la especie.\n';
            }
            if(tamano == '')
            {
                valido = 0;
                mensaje += 'Tipo de mascota: requerido\n';
            }
            if(esterilizado === '')
            {
                valido = 0;
                mensaje += 'Esterilizado: requerido\n';
            }
            if(esterilizado === '1' && fecha_esterilizacion === '' && !fecha_esterilizacion_desconocida)
            {
                valido = 0;
                mensaje += 'Fecha de esterilización: requerido\n';
            }
            if(fecha_nac == '' && !fecha_nac_desconocida)
            {
                valido = 0;
                mensaje += 'Fecha Nacimiento: requerido\n';
            }
            if(sexo == '' || sexo == '0')
            {
                valido = 0;
                mensaje += 'Sexo: requerido\n';
            }
            if(tiene_chip === '1' && chip == '')
            {
                valido = 0;
                mensaje += 'Número de chip: requerido\n';
            }

            if(valido == 1)
            {
                let url = mascotaId ? rutaMascotasBase + '/' + mascotaId : rutaMascotasStore;
                var datos = {};

                datos._token = CSRF_TOKEN;
                if(mascotaId)
                {
                    datos._method = 'PUT';
                }
                datos.tiene_chip = tiene_chip;
                datos.chip = (tiene_chip === '1') ? chip : '';
                datos.nombre = nombre;
                datos.especie_id = especie;
                datos.raza_id = raza;
                datos.otra_especie = otra_especie;
                datos.tamano_id = tamano;
                datos.esterilizado = esterilizado;
                datos.fecha_esterilizacion_desconocida = fecha_esterilizacion_desconocida ? 1 : 0;
                datos.fecha_esterilizacion = (esterilizado === '1' && !fecha_esterilizacion_desconocida) ? fecha_esterilizacion : '';
                datos.enfermedad_cronica = enfermedad_cronica;
                datos.dieta = dieta;
                datos.cirugias = cirugias;
                datos.vacunas = vacunas;
                datos.viajes = viajes;
                datos.ultima_desparasitacion = ultima_desparasitacion;
                datos.producto_desparasitacion = producto_desparasitacion;
                datos.vive_con_animales = vive_con_animales;
                datos.fecha_nacimiento = fecha_nac_desconocida ? '' : fecha_nac;
                datos.sexo = sexo;
                datos.foto_perfil = foto_perfil;
                datos.galeria = galeria;
                datos.observaciones_fotos = observaciones;

                $btnRegistrar.data('guardando', true).prop('disabled', true);

                $.ajax({
                    url: url,
                    type: "POST",
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': CSRF_TOKEN,
                    },
                    data: datos,
                })
                .done(function(data, textStatus, jqXHR) {
                    data = parsearRespuestaAjax(data);
                    if (!data && jqXHR && jqXHR.responseText) {
                        data = parsearRespuestaAjax(jqXHR.responseText);
                    }
                    if (!data || typeof data !== 'object') {
                        swal({
                            title: "Registro de Mascota.",
                            text: "Respuesta inesperada del servidor.",
                            icon: "error",
                        });
                        return;
                    }

                    if (esRespuestaExitosa(data))
                    {
                        if (data.mascota) {
                            actualizarMascotasEnLista(data.mascota);
                        }

                        $('#modal_agregar_dep_nuevo').modal('hide');
                        setModoEdicion(null);
                        limpiarFormularioMascota();

                        swal({
                            title: mascotaId ? "Mascota actualizada" : "Mascota guardada",
                            text: data.msj || "La mascota se registró correctamente.",
                            icon: "success",
                        });
                        cargarDependientes();
                    }
                    else
                    {
                        var texto_error = data.msj || 'Problemas al realizar el registro.';
                        if(data.error)
                        {
                            if (typeof data.error === 'object') {
                                Object.keys(data.error).forEach(function(campo) {
                                    var mensajes = data.error[campo];
                                    if (Array.isArray(mensajes)) {
                                        mensajes.forEach(function(msg) {
                                            texto_error += '\n' + msg;
                                        });
                                    }
                                });
                            } else {
                                texto_error += '\n' + JSON.stringify(data.error);
                            }
                        }
                        swal({
                            title: "Registro de Mascota.",
                            text: texto_error,
                            icon: "error",
                        });
                    }

                })
                .fail(function(jqXHR, textStatus) {
                    var data = jqXHR.responseJSON || parsearRespuestaAjax(jqXHR.responseText);

                    if (esRespuestaExitosa(data)) {
                        if (data.mascota) {
                            actualizarMascotasEnLista(data.mascota);
                        }

                        $('#modal_agregar_dep_nuevo').modal('hide');
                        setModoEdicion(null);
                        limpiarFormularioMascota();
                        swal({
                            title: mascotaId ? "Mascota actualizada" : "Mascota guardada",
                            text: data.msj || "La mascota se registró correctamente.",
                            icon: "success",
                        });
                        cargarDependientes();
                        return;
                    }

                    var texto = 'No se pudo guardar la mascota.';

                    if (jqXHR.status === 419) {
                        texto = 'La sesión expiró. Recargue la página e intente nuevamente.';
                    } else if (jqXHR.status === 0) {
                        texto = 'No hubo respuesta del servidor. Verifique su conexión.';
                    } else if (data) {
                        if (data.msj) {
                            texto = data.msj;
                        } else if (data.message) {
                            texto = data.message;
                        }
                        var errores = data.error || data.errors;
                        if (errores) {
                            Object.keys(errores).forEach(function(campo) {
                                var mensajes = errores[campo];
                                if (Array.isArray(mensajes)) {
                                    mensajes.forEach(function(msg) {
                                        texto += '\n' + msg;
                                    });
                                } else if (typeof mensajes === 'string') {
                                    texto += '\n' + mensajes;
                                }
                            });
                        }
                    } else if (textStatus === 'parsererror') {
                        texto = 'El servidor respondió con un formato inesperado. Recargue la página e intente nuevamente.';
                        if (jqXHR.status) {
                            texto += ' (HTTP ' + jqXHR.status + ')';
                        }
                    } else if (jqXHR.status) {
                        texto += ' (HTTP ' + jqXHR.status + ')';
                    }
                    swal({
                        title: "Registro de Mascota.",
                        text: texto,
                        icon: "error",
                    });
                })
                .always(function() {
                    $btnRegistrar.data('guardando', false).prop('disabled', false);
                });
            }
            else
            {
                swal({
                    title: "Registro de Mascota. Campos Requeridos",
                    text: mensaje,
                    icon: "error",
                });
            }
        }
        window.registrar_dep_nuevo = registrar_dep_nuevo;

        function pintarTarjetasMascotas(registros)
        {
            var html = '';
            var img_m = '{{ asset('images/iconos/paciente-m.svg') }}';
            var img_f = '{{ asset('images/iconos/paciente-f.svg') }}';
            var $lista = $('#card-lista-dependientes');

            if (!Array.isArray(registros) || !registros.length) {
                $lista.html('<h4 class="">Sin Mascotas Registradas</h4>');
                return;
            }

            $.each(registros, function (key, value) {
                registrarMascotaCache(value);
                var img = '';

                if (value.foto_url) {
                    img = value.foto_url;
                } else if (value.foto_perfil) {
                    img = normalizarRutaImagen(value.foto_perfil);
                } else if (value.galeria && value.galeria.ven_pre && value.galeria.ven_pre.length > 0 && value.galeria.ven_pre[0][0]) {
                    img = normalizarRutaImagen(value.galeria.ven_pre[0][0]);
                } else if (value.sexo == 'M') {
                    img = img_m;
                } else {
                    img = img_f;
                }

                var especie_label = obtenerLabelEspecie(value.especie_id || value.especie, value.otra_especie);
                var genealogia_url = urlMascotaDesdePlantilla(plantillaRutaGenealogia, value.id);
                var carnet_sanitario_url = urlMascotaDesdePlantilla(plantillaRutaCarnet, value.id);
                var fvu_url = urlMascotaDesdePlantilla(plantillaRutaFvu, value.id);
                var memorial_url = urlMascotaDesdePlantilla(rutaMemorialMascota, value.id);
                var esFallecida = mascotaEstaFallecida(value);
                var cardClass = 'card card-mascota' + (esFallecida ? ' card-mascota-fallecida' : '');

                html += '<div class="col">';
                html += '    <div class="'+cardClass+'" data-id="'+value.id+'">';
                html += '        <div class="card-body text-center" style="cursor:pointer">';
                html += '            <img class="wid-60 text-center mt-1 rounded-circle" src="'+img+'">';
                html += '            <h5 class="mt-2 mb-0">'+value.nombre+'</h5>';
                html += '            <p class="mb-0">'+especie_label+'</p>';
                if (esFallecida) {
                    html += '            <span class="mascota-badge-memoria">En memoria</span>';
                }
                html += '            <div class="mt-2 d-flex justify-content-center flex-wrap">';
                if (esFallecida) {
                    html += '                <a class="btn btn-secondary btn-xxs btn-memorial-mascota" href="'+memorial_url+'"><i class="feather icon-image"></i> Álbum de recuerdos</a>';
                    html += '                <button type="button" class="btn btn-info btn-xxs mr-1 btn-ver-mascota" data-id="'+value.id+'"><i class="feather icon-eye"></i> Ver ficha</button>';
                    html += '                <a class="btn btn-primary mr-1 btn-xxs btn-ver-ficha" href="'+fvu_url+'" title="Ficha Veterinaria Única"><i class="feather icon-file-plus"></i> FVU</a>';
                    html += '                <a class="btn btn-warning btn-xxs mr-1" href="'+carnet_sanitario_url+'"><i class="fas fa-syringe"></i> Carné vacunas/desp.</a>';
                    html += '                <a class="btn btn-warning btn-xxs ml-1" href="'+genealogia_url+'"><i class="fas fa-sitemap"></i> Mi genealogía</a>';
                } else {
                    html += '                <a class="btn btn-warning btn-xxs mr-1" href="'+carnet_sanitario_url+'"><i class="fas fa-syringe"></i> Carné vacunas/desp.</a>';
                    html += '                <button type="button" class="btn btn-info btn-xxs mr-1 btn-ver-mascota" data-id="'+value.id+'"><i class="feather icon-eye"></i> Ficha mascota</button>';
                    html += '                <a class="btn btn-primary mr-1 btn-xxs btn-ver-ficha" href="'+fvu_url+'" title="Ficha Veterinaria Única"><i class="feather icon-file-plus"></i> FVU</a>';
                    html += '                <button type="button" class="btn btn-purple btn-xxs btn-escritorio-mascota" data-id="'+value.id+'"><i class="feather icon-monitor"></i> Escritorio</button>';
                    html += '                <a class="btn btn-warning btn-xxs ml-1" href="'+genealogia_url+'"><i class="fas fa-sitemap"></i> Mi genealogía</a>';
                    html += '                <button type="button" class="btn btn-light btn-xxs btn-registrar-fallecimiento" data-id="'+value.id+'" onclick="return abrirModalFallecimientoMascota('+value.id+', event);"><i class="feather icon-cloud"></i> Registrar fallecimiento</button>';
                }
                html += '            </div>';
                html += '        </div>';
                html += '    </div>';
                html += '</div>';
            });

            $lista.html(html);
        }

        function cargarDependientes()
        {

            let url  = rutaMascotasLista;
            var datos = {};
            var $lista = $('#card-lista-dependientes');

            $.ajax({
                url: url,
                type: "get",
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                data: datos,
            })
            .done(function(data, textStatus, jqXHR) {
                data = parsearRespuestaAjax(data);
                if (!data && jqXHR && jqXHR.responseText) {
                    data = parsearRespuestaAjax(jqXHR.responseText);
                }
                mascotasCache = {};

                if (esRespuestaExitosa(data) && data.registros && data.registros.length) {
                    mascotasIniciales = data.registros;
                    pintarTarjetasMascotas(data.registros);
                } else if (Array.isArray(mascotasIniciales) && mascotasIniciales.length) {
                    pintarTarjetasMascotas(mascotasIniciales);
                } else {
                    pintarTarjetasMascotas([]);
                }

                @if(($titulo ?? '') === 'Mascotas')
                if(typeof poblarSelectTraspasoMascotas === 'function')
                {
                    poblarSelectTraspasoMascotas();
                }
                if(typeof poblarSelectApareamientoMascotas === 'function')
                {
                    poblarSelectApareamientoMascotas();
                }
                if(typeof cargarBuzonApareamiento === 'function')
                {
                    cargarBuzonApareamiento();
                }
                @endif

            })
            .fail(function(jqXHR, textStatus) {
                console.log('Error cargando mascotas:', textStatus, jqXHR);
                if (Array.isArray(mascotasIniciales) && mascotasIniciales.length) {
                    pintarTarjetasMascotas(mascotasIniciales);
                } else {
                    pintarTarjetasMascotas([]);
                }
            })
            .always(function() {
                $lista.removeClass('d-none');
            });
        }
        function abrirModalSeleccionDefuncion(event)
        {
            if (event && typeof event.preventDefault === 'function') {
                event.preventDefault();
            }

            var $select = $('#defuncion_mascota_id');
            $select.html('<option value="">Seleccione una mascota</option>');

            (Array.isArray(mascotasIniciales) ? mascotasIniciales : []).forEach(function(mascota) {
                if (!mascota || !mascota.id || mascotaEstaFallecida(mascota)) return;
                $select.append($('<option></option>').val(mascota.id).text(mascota.nombre || ('Mascota #' + mascota.id)));
            });

            if ($select.find('option').length <= 1) {
                swal({
                    title: 'Sin mascotas disponibles',
                    text: 'No existen mascotas activas para registrar una defunción.',
                    icon: 'info'
                });
                return false;
            }

            $('#modal_seleccionar_defuncion').appendTo('body').modal('show');
            return false;
        }

        function continuarRegistroDefuncion(event)
        {
            if (event && typeof event.preventDefault === 'function') {
                event.preventDefault();
            }

            var mascotaId = $('#defuncion_mascota_id').val();
            if (!mascotaId) {
                swal({ title: 'Seleccione una mascota', icon: 'warning' });
                return false;
            }

            $('#modal_seleccionar_defuncion').one('hidden.bs.modal', function() {
                abrirModalFallecimientoMascota(mascotaId);
            }).modal('hide');

            return false;
        }

        window.abrirModalSeleccionDefuncion = abrirModalSeleccionDefuncion;
        window.continuarRegistroDefuncion = continuarRegistroDefuncion;
    </script>
@endsection
