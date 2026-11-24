@extends('template.profesional.template')

@section('content')
<div class="pcoded-main-container">
    <div class="pcoded-content">
        <!--Header-->
        <div class="arv-encabezado mb-3">
            <h5 class="titulo-modulo f-26 d-inline mb-0">Aranceles</h5>
            <button type="button" id="arvAbrirConfig" class="btn btn-info"><i class="feather icon-settings"></i> Configuración</button>
        </div>
        <!--Cierre: Header-->

        <div class="pill-moderno pill-moderno-white arv-tabs mb-4" role="tablist">
            <a href="#" class="arv-tab active" role="tab" aria-selected="true" data-catalogo="aranceles">Aranceles / Prestaciones <span class="arv-tab-contador"></span></a>
            <a href="#" class="arv-tab" role="tab" aria-selected="false" data-catalogo="examenes">Exámenes <span class="arv-tab-contador"></span></a>
            <a href="#" class="arv-tab" role="tab" aria-selected="false" data-catalogo="procedimientos">Procedimientos <span class="arv-tab-contador"></span></a>
        </div>

        <div class="card arv-card">
            <div class="card-header encabezado-card">
                <h5 class="encabezado-card-titulo"><span class="encabezado-card-icono"><i class="feather icon-tag" id="arvIconoCard"></i></span><span id="arvTituloCard">Aranceles / Prestaciones</span></h5>
            </div>

            <div class="card-body">
                <div class="arv-toolbar">
                    <div class="arv-buscador">
                        <i class="feather icon-search"></i>
                        <input type="search" id="arvBuscar" class="form-control form-control-sm" placeholder="Buscar por código o nombre" aria-label="Buscar por código o nombre">
                    </div>
                    <select id="arvFiltroEstado" class="form-control form-control-sm" aria-label="Filtrar por estado">
                        <option value="todos">Todos los estados</option>
                        <option value="activos">Solo activos</option>
                        <option value="inactivos">Solo inactivos</option>
                    </select>
                    <label class="arv-filtro-varia" for="arvSoloVaria">
                        <input type="checkbox" id="arvSoloVaria"> Solo los que varían por tamaño
                    </label>
                    <button type="button" id="arvImportarNavegador" class="btn btn-outline-secondary" hidden>Importar mis aranceles del navegador</button>
                    <button type="button" id="arvCatalogoBase" class="btn btn-outline-info">Catálogo de cirugías y procedimientos</button>
                    <button type="button" id="arvNuevo" class="btn btn-info"><i class="feather icon-plus"></i> <span class="js-texto">Nuevo arancel</span></button>
                </div>

                <div class="arv-tabla-contenedor">
                    <table class="table arv-tabla mb-0" id="arvTabla">
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Nombre</th>
                                <th id="arvColTipo">Categoría</th>
                                <th>Precio</th>
                                <th>Estado</th>
                                <th class="text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
                <div class="arv-resumen" id="arvResumen"></div>
            </div>
        </div>
    </div>
</div>

@include('app.profesional.aranceles.modal_catalogo')
@include('app.profesional.aranceles.modal_item')
@include('app.profesional.aranceles.modal_configuracion')
@endsection

@section('page-script')
<script>window.arancelesVeterinariosConfig={listar:@json(route('profesional.aranceles_vet.listar')),guardar:@json(route('profesional.aranceles_vet.guardar')),csrf:@json(csrf_token()),catalogo:@json(config('catalogo_prestaciones_veterinarias'))};</script>
<script src="{{ asset('js/aranceles.js') }}?v=20261019-1"></script>
@endsection
