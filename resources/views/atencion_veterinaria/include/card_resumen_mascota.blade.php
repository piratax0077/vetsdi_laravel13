{{-- Card con los datos clave de la mascota que se está atendiendo; vive en el menú lateral de la ficha --}}
@php
    $resumenMascota = ($mascota ?? null) instanceof \App\Models\Mascota
        ? \App\Support\ResumenMascotaFicha::para($mascota)
        : null;
@endphp

@if ($resumenMascota)
@php
    // Si hay algo que revisar (enfermedad crónica, vacunas o desparasitación atrasadas) el indicador del menú cerrado lo avisa en rojo
    $resumenConAlerta = $resumenMascota['enfermedad_cronica']
        || $resumenMascota['vacunas'] === 'atrasado'
        || $resumenMascota['desparasitacion'] === 'atrasado';
@endphp
{{-- Con el menú cerrado solo se ve este indicador; al pasar el mouse o abrir con ☰ aparece la card completa --}}
<div class="resumen-mascota-mini" title="Datos de {{ $resumenMascota['nombre'] }}: pasa el mouse para verlos">
    <span class="resumen-mascota-mini__foto">
        <img src="{{ $resumenMascota['foto'] }}" alt="Foto de {{ $resumenMascota['nombre'] }}"
             onerror="this.onerror=null;this.src='{{ asset('images/iconos/mascotas.svg') }}';">
        <span class="resumen-mascota-mini__insignia {{ $resumenConAlerta ? 'resumen-mascota-mini__insignia--alerta' : '' }}">
            <i class="feather {{ $resumenConAlerta ? 'icon-alert-circle' : 'icon-info' }}"></i>
        </span>
    </span>
    <span class="resumen-mascota-mini__nombre">{{ $resumenMascota['nombre'] }}</span>
</div>

<div class="resumen-mascota" aria-label="Datos de la mascota">
    <div class="resumen-mascota__cabecera">
        <img class="resumen-mascota__foto" src="{{ $resumenMascota['foto'] }}" alt="Foto de {{ $resumenMascota['nombre'] }}"
             onerror="this.onerror=null;this.src='{{ asset('images/iconos/mascotas.svg') }}';">

        <div class="resumen-mascota__identidad">
            <span class="resumen-mascota__nombre" title="{{ $resumenMascota['nombre'] }}">{{ $resumenMascota['nombre'] }}</span>
            <span class="resumen-mascota__especie" title="{{ $resumenMascota['especie'] }} · {{ $resumenMascota['raza'] }}">
                {{ $resumenMascota['especie'] }} · {{ $resumenMascota['raza'] }}
            </span>
        </div>
    </div>

    <ul class="resumen-mascota__datos">
        <li class="resumen-mascota__dato">
            <span class="resumen-mascota__etiqueta">Edad</span>
            <span class="resumen-mascota__valor">{{ $resumenMascota['edad'] }}</span>
        </li>
        <li class="resumen-mascota__dato">
            <span class="resumen-mascota__etiqueta">Sexo</span>
            <span class="resumen-mascota__valor">{{ $resumenMascota['sexo'] }}</span>
        </li>
        <li class="resumen-mascota__dato">
            <span class="resumen-mascota__etiqueta">N° Chip</span>
            <span class="resumen-mascota__valor" title="{{ $resumenMascota['chip'] }}">{{ $resumenMascota['chip'] }}</span>
        </li>
        <li class="resumen-mascota__dato">
            <span class="resumen-mascota__etiqueta">Enf. crónicas</span>
            <span class="resumen-mascota__valor">
                @if ($resumenMascota['enfermedad_cronica'])
                    <button type="button" class="resumen-mascota__estado resumen-mascota__estado--alerta resumen-mascota__estado--accion"
                            data-toggle="modal" data-target="#modal_fvu_cronicas" title="Ver enfermedades crónicas">
                        Sí <i class="feather icon-eye"></i>
                    </button>
                @else
                    <span class="resumen-mascota__estado resumen-mascota__estado--ok">No</span>
                @endif
            </span>
        </li>
        <li class="resumen-mascota__dato">
            <span class="resumen-mascota__etiqueta">Desparasitación</span>
            <span class="resumen-mascota__valor">
                @if ($resumenMascota['desparasitacion'] === 'sin_registro')
                    <span class="resumen-mascota__estado resumen-mascota__estado--neutro">Sin registro</span>
                @else
                    @php
                        $claseDesparasitacion = $resumenMascota['desparasitacion'] === 'atrasado' ? 'alerta' : 'ok';
                        $textoDesparasitacion = $resumenMascota['desparasitacion'] === 'atrasado' ? 'Atrasado' : 'Al día';
                    @endphp
                    @if ($resumenMascota['desparasitacion_con_historial'])
                        <button type="button" class="resumen-mascota__estado resumen-mascota__estado--{{ $claseDesparasitacion }} resumen-mascota__estado--accion"
                                data-toggle="modal" data-target="#modal_fvu_desparasitaciones" title="Ver registro de desparasitación">
                            {{ $textoDesparasitacion }} <i class="feather icon-eye"></i>
                        </button>
                    @else
                        <span class="resumen-mascota__estado resumen-mascota__estado--{{ $claseDesparasitacion }}">{{ $textoDesparasitacion }}</span>
                    @endif
                @endif
            </span>
        </li>
        <li class="resumen-mascota__dato">
            <span class="resumen-mascota__etiqueta">Vacunas</span>
            <span class="resumen-mascota__valor">
                @if ($resumenMascota['vacunas'] === 'al_dia')
                    <button type="button" class="resumen-mascota__estado resumen-mascota__estado--ok resumen-mascota__estado--accion"
                            data-toggle="modal" data-target="#modal_fvu_vacunas" title="Ver registro de vacunas">
                        Al día <i class="feather icon-eye"></i>
                    </button>
                @elseif ($resumenMascota['vacunas'] === 'atrasado')
                    <button type="button" class="resumen-mascota__estado resumen-mascota__estado--alerta resumen-mascota__estado--accion"
                            data-toggle="modal" data-target="#modal_fvu_vacunas" title="Ver registro de vacunas">
                        Atrasado <i class="feather icon-eye"></i>
                    </button>
                @else
                    <span class="resumen-mascota__estado resumen-mascota__estado--neutro">Sin registro</span>
                @endif
            </span>
        </li>
        <li class="resumen-mascota__dato">
            <span class="resumen-mascota__etiqueta">Comportamiento</span>
            <span class="resumen-mascota__valor" title="{{ $resumenMascota['comportamiento'] }}">{{ $resumenMascota['comportamiento'] }}</span>
        </li>
    </ul>
</div>
@endif
