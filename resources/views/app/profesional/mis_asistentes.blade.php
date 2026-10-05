@extends('template.profesional.template')

@section('content')

    <!--****Container Completo****-->

    <div class="pcoded-main-container">

        <div class="pcoded-content m-top">
            <div class="row">
                <div class="col-md-12 mb-2 encabezado-pagina">
                    <h5 class="f-26 d-inline">Mis asistentes</h5>
                    <div class="d-inline-flex flex-wrap justify-content-end">
                        @if($es_odontologia_veterinaria)
                            <a href="{{ route('profesional.tons') }}" class="btn btn-sm btn-info mr-2 mb-1">
                                <i class="feather icon-user-plus"></i> Agregar TONS / técnico en odontología
                            </a>
                        @endif
                        <button type="button" class="btn btn-sm btn-info mb-1" data-toggle="modal" data-target="#nuevo_asistente">
                            <i class="feather icon-plus"></i> Agregar secretaria
                        </button>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12">
                    <div class="card">
                        <div class="card-body">

                            <div style="overflow-x:auto;">

                                <div class="table-responsive">

                                    <table class="display table table-sm dt-responsive nowrap"

                                        style="width:100%">

                                        <thead>

                                            <tr>

                                                <th>Lugar Atención</th>

                                                <th>Rut</th>

                                                <th>Nombre</th>

                                                <th>Contacto</th>

                                                <th>Contacto Emergencia</th>

                                                <th>Dirección</th>

                                                <th>Desasociar</th>

                                            </tr>

                                        </thead>

                                        <tbody>



                                            {{--  @if (isset($asistentes))

                                                @foreach ($asistentes as $a)

                                                    <tr>

                                                        <input type="hidden" name="id_asistente" id="id_asistente" value="{{ $a->id }}">

                                                        <td>-</td>

                                                        <td>{{ $a->rut }}</td>

                                                        <td>

                                                            {{ $a->nombres . ' ' . $a->apellido_uno . ' ' . $a->apellido_dos }}

                                                        </td>

                                                        <td>

                                                            <span>{{ $a->email }}</span><br>

                                                            <span>{{ $a->telefono_uno }}</span>

                                                        </td>

                                                        <td>

                                                            <span></span><br>

                                                            <span></span><br>

                                                            <span></span>

                                                        </td>

                                                        <td>

                                                            @if(issset($a->id_direccion))

                                                                {{ $a->Direccion()->first()->direccion }}<br>

                                                                {{ $a->Direccion()->first()->Ciudad()->first()->nombre }}

                                                            @else

                                                                NO DISPONIBLE

                                                            @endif

                                                        </td>

                                                        <td class="text-center">

                                                            <button onclick="desasociar_asistente({{ $a->id }});"

                                                                type="button" class="btn btn-danger btn-sm btn-icon"

                                                                data-toggle="tooltip" data-placement="top" title="Desasociar">

                                                                <i class="feather icon-x"></i>

                                                            </button>

                                                        </td>

                                                    </tr>

                                                @endforeach

                                            @endif  --}}



                                            @if (isset($asistentes_lugar_atencion))

                                                @foreach ($asistentes_lugar_atencion as $asis_la)



                                                    <tr>

                                                        <input type="hidden" name="id_asistente" id="id_asistente"

                                                            value="{{ $asis_la->id }}">

                                                        <td>{{ $asis_la->LugarAtencion()->first()->nombre }}</td>

                                                        <td>

                                                            {{ $asis_la->Asistentes()->first()->rut }}</td>

                                                        <td>

                                                            {{ $asis_la->Asistentes()->first()->nombres .' ' .$asis_la->Asistentes()->first()->apellido_uno .' ' .$asis_la->Asistentes()->first()->apellido_dos }}

                                                        </td>

                                                        <td>

                                                            <span>{{ $asis_la->Asistentes()->first()->email }}</span><br>

                                                            <span>{{ $asis_la->Asistentes()->first()->telefono_uno }}</span>

                                                        </td>

                                                        <td class="align-middle">

                                                            <span></span><br>

                                                            <span></span><br>

                                                            <span></span>

                                                        </td>

                                                        <td class="align-middle">

                                                            @if(isset($asis_la->Asistentes()->first()->id_direccion))

                                                                {{ $asis_la->Asistentes()->first()->Direccion()->first()->direccion }}<br>

                                                                {{ $asis_la->Asistentes()->first()->Direccion()->first()->Ciudad()->first()->nombre }}

                                                            @else

                                                                NO DISPONIBLE

                                                            @endif



                                                        </td>

                                                        <td class="align-middle text-center">

                                                            <button type="button"

                                                                onclick="desasociar_asistente({{ $asis_la->Asistentes()->first()->id }})"

                                                                class="btn btn-danger btn-sm btn-icon" data-toggle="tooltip"

                                                                data-placement="top" title="Desasociar"><i

                                                                    class="feather icon-x"></i></button>

                                                        </td>

                                                    </tr>

                                                @endforeach

                                            @endif



                                        </tbody>

                                    </table>

                                </div>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>


@endsection

@include('app.profesional.modales.nuevo_asistente')

{{-- @include('app.contabilidad.modals.datos_profesional') --}}

