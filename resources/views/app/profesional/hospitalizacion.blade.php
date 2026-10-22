@extends('template.profesional.template')
@section('content')
    @php
        $search = $search ?? '';
        $lugaresHospitalizacion = [1 => 'Clínica', 2 => 'Hospital', 3 => 'Otro'];
    @endphp

    <!--Container Completo-->
    <div class="pcoded-main-container">
        <div class="pcoded-content">

            <div class="row">
                <div class="col-md-12 mb-3 encabezado-pagina">
                    <div>
                        <h5 class="f-26 d-block mb-0">Hospitalización</h5>
                        <span class="text-muted">Mascotas con orden de hospitalización vigente</span>
                    </div>
                    <div class="ml-auto">
                        <span class="badge badge-purple-light f-14">{{ $hospitalizados->count() }} {{ $hospitalizados->count() == 1 ? 'hospitalizada' : 'hospitalizadas' }}</span>
                    </div>
                </div>
            </div>

            <!--Las mascotas llegan acá cuando se guarda la orden de hospitalización en la ficha de atención-->
            <div class="row">
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-body">
                            <form method="GET" action="{{ route('profesional.hospitalizacion') }}" class="mb-4">
                                <div class="form-row align-items-end">
                                    <div class="col-10 col-md-6 mb-2">
                                        <label class="floating-label-activo-sm mb-0">Buscador</label>
                                        <input type="text" name="q" value="{{ $search }}" class="form-control form-control-sm"
                                            placeholder="Buscar por mascota, tutor o RUT">
                                    </div>
                                    <div class="col-2 col-md-2 mb-2">
                                        <button class="btn btn-icon btn-info js-tooltip-accion" type="submit" title="Buscar" aria-label="Buscar">
                                            <i class="feather icon-search"></i>
                                        </button>
                                    </div>
                                </div>
                            </form>
                            @if ($hospitalizados->isEmpty())
                                <div class="text-center py-4">
                                    @if ($search !== '')
                                        No hay mascotas hospitalizadas que coincidan con la búsqueda.
                                    @else
                                        No hay mascotas hospitalizadas.<br>
                                        <span class="text-muted">Aparecerán acá cuando se genere la orden de hospitalización desde la ficha de atención.</span>
                                    @endif
                                </div>
                            @else
                            <div class="table-responsive">
                                <table class="display table table-striped nowrap table-xs w-100">
                                    <thead>
                                        <tr>
                                            <th>Mascota</th>
                                            <th>Tutor</th>
                                            <th>Ingreso</th>
                                            <th>Lugar</th>
                                            <th>Motivo</th>
                                            <th>Observaciones</th>
                                            <th>Acción</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($hospitalizados as $hospitalizado)
                                            @php
                                                $mascota = $hospitalizado->mascota;
                                                $tutor = $hospitalizado->paciente;
                                                $nombreTutor = trim(collect([
                                                    optional($tutor)->nombres,
                                                    optional($tutor)->apellido_uno,
                                                    optional($tutor)->apellido_dos,
                                                ])->filter()->implode(' '));
                                                $telefonoTutor = optional($tutor)->telefono_uno ?: optional($tutor)->telefono_dos;
                                                $especie = optional(optional($mascota)->especieMascota)->nombre;
                                                $raza = optional(optional($mascota)->razaMascota)->nombre ?: optional($mascota)->otra_especie;
                                                $fotoRespaldo = optional($mascota)->sexo === 'F'
                                                    ? asset('images/iconos/paciente-f.svg')
                                                    : asset('images/iconos/paciente-m.svg');
                                                $fotoMascota = optional($mascota)->foto_url ?: asset('images/iconos/mascotas.svg');
                                                $ingreso = $hospitalizado->created_at;
                                                $diasHospitalizado = $ingreso ? (int) $ingreso->copy()->startOfDay()->diffInDays(now()->startOfDay()) : null;
                                                $lugar = $hospitalizado->obs_hospen ?: ($lugaresHospitalizacion[$hospitalizado->hospen] ?? null);
                                                $centro = $hospitalizado->nom_inst ?: optional(optional($hospitalizado->fichaAtencion)->LugarAtencion)->nombre;
                                            @endphp
                                            <tr id="fila_hospitalizado_{{ $hospitalizado->id }}">
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <span class="traspaso-mascota-foto mr-2">
                                                            <img src="{{ $fotoMascota }}" alt="Foto de la mascota" onerror="this.onerror=null;this.src='{{ $fotoRespaldo }}';">
                                                        </span>
                                                        <div>
                                                            <strong class="d-block">{{ optional($mascota)->nombre ?: 'Sin mascota registrada' }}</strong>
                                                            <span class="text-muted">{{ collect([$especie, $raza])->filter()->implode(' · ') ?: '-' }}</span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    {{ $nombreTutor ?: '-' }}
                                                    @if (optional($tutor)->rut)
                                                        <br>{{ $tutor->rut }}
                                                    @endif
                                                    @if ($telefonoTutor)
                                                        <br><a class="text-reset" href="tel:{{ preg_replace('/[^0-9+]/', '', $telefonoTutor) }}">{{ $telefonoTutor }}</a>
                                                    @endif
                                                </td>
                                                <td>
                                                    {{ $ingreso ? $ingreso->format('d-m-Y') : '-' }}
                                                    @if (!is_null($diasHospitalizado))
                                                        <br><span class="badge badge-purple-light">{{ $diasHospitalizado == 0 ? 'Ingresó hoy' : ($diasHospitalizado == 1 ? '1 día' : $diasHospitalizado . ' días') }}</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    {{ $centro ?: '-' }}
                                                    <br><span class="text-muted">{{ collect([$lugar, $hospitalizado->obs_hosp_enserv])->filter()->implode(' · ') }}</span>
                                                </td>
                                                <td>{{ $hospitalizado->obs_motivo_hosp ?: '-' }}</td>
                                                <td class="text-wrap">{{ $hospitalizado->obs_hospitalizar ?: '-' }}</td>
                                                <td>
                                                    <div class="d-flex flex-nowrap">
                                                        @if ($mascota)
                                                            <a href="{{ route('profesional.mascota.ficha_veterinaria', ['mascota' => $mascota->id]) }}"
                                                                class="btn btn-icon btn-purple mr-1 js-tooltip-accion"
                                                                title="Ver ficha veterinaria" aria-label="Ver ficha veterinaria">
                                                                <i class="feather icon-file-text"></i>
                                                            </a>
                                                        @endif
                                                        @if ($tutor)
                                                            <a href="{{ route('profesional.atenciones_previas_paciente', array_filter(['id' => $tutor->id, 'id_mascota' => optional($mascota)->id])) }}"
                                                                class="btn btn-icon btn-primary mr-1 js-tooltip-accion"
                                                                title="Revisar consultas anteriores" aria-label="Revisar consultas anteriores">
                                                                <i class="feather icon-clock"></i>
                                                            </a>
                                                        @endif
                                                        <button type="button" class="btn btn-icon btn-success js-tooltip-accion"
                                                            onclick="dar_alta_hospitalizacion({{ $hospitalizado->id }}, @js(optional($mascota)->nombre ?: $nombreTutor))"
                                                            title="Dar de alta" aria-label="Dar de alta">
                                                            <i class="feather icon-check"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--Cierre: Container Completo-->
@endsection

@section('page-script')
<script>
    // Tooltips de los botones de icono: en pantallas táctiles se muestran al tocar y se ocultan solos
    $(function () {
        var $botones = $('.js-tooltip-accion');
        $botones.tooltip({ container: 'body', trigger: 'hover focus', boundary: 'window' });
        $botones.on('touchstart', function () {
            var $boton = $(this);
            $botones.not($boton).tooltip('hide');
            $boton.tooltip('show');
            clearTimeout($boton.data('temporizadorTooltip'));
            $boton.data('temporizadorTooltip', setTimeout(function () { $boton.tooltip('hide'); }, 2500));
        });
        $botones.on('click', function () { $(this).tooltip('hide'); });
    });

    function dar_alta_hospitalizacion(id, nombre) {
        swal({
            title: "Dar de alta",
            text: "¿Confirma el alta de " + nombre + "? Dejará de aparecer en el listado de hospitalización.",
            icon: "warning",
            buttons: ["Cancelar", "Dar de alta"],
        }).then(function (confirmado) {
            if (!confirmado) return;

            $.ajax({
                url: "{{ route('profesional.hospitalizacion.alta') }}",
                type: "post",
                dataType: "json",
                data: { id: id, _token: "{{ csrf_token() }}" },
            })
            .done(function (respuesta) {
                if (respuesta.success) {
                    location.reload();
                } else {
                    swal({ title: "Dar de alta", text: respuesta.mensaje || "No se pudo registrar el alta.", icon: "error", buttons: "Aceptar" });
                }
            })
            .fail(function () {
                swal({ title: "Dar de alta", text: "No se pudo registrar el alta. Intente nuevamente.", icon: "error", buttons: "Aceptar" });
            });
        });
    }
</script>
@endsection
