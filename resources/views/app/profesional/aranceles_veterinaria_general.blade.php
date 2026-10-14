@extends('template.profesional.template')

@section('content')
<div class="pcoded-main-container arv-page">
    <div class="pcoded-content m-top">
        <div class="page-header">
            
        </div>

        <section class="arv-hero mb-4">
            <div>
                <h3 class="text-white mb-1">Aranceles</h3>
                <div>Prestaciones, exámenes y procedimientos de tu consulta, con sus valores.</div>
            </div>
            <div class="arv-hero-acciones">
                <button type="button" id="arvAbrirConfig" class="btn btn-light"><i class="feather icon-settings"></i> Configuración</button>
                <a href="{{ route('profesional.configuracion') }}" class="btn btn-light"><i class="feather icon-arrow-left"></i> Volver</a>
            </div>
        </section>

        <div class="card arv-card">
            <div class="card-header">
                <div class="arv-tabs" role="tablist">
                    <button type="button" class="arv-tab active" role="tab" aria-selected="true" data-catalogo="aranceles">Aranceles / Prestaciones <span class="arv-tab-contador"></span></button>
                    <button type="button" class="arv-tab" role="tab" aria-selected="false" data-catalogo="examenes">Exámenes <span class="arv-tab-contador"></span></button>
                    <button type="button" class="arv-tab" role="tab" aria-selected="false" data-catalogo="procedimientos">Procedimientos <span class="arv-tab-contador"></span></button>
                </div>
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

@include('app.profesional.aranceles.modal_item')
@include('app.profesional.aranceles.modal_configuracion')
@endsection

@section('page-script')
<script src="{{ asset('js/aranceles.js') }}?v=20261004-2"></script>
@endsection
