
<header>
    <div class="contenido-encabezado-uno">
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="width: 22%; vertical-align: middle; padding: 8px 16px 8px 8px;">
                    <img
                        style="width: 120px; height: auto; padding: 0; margin: 0;"
                        src="data:image/svg+xml;base64,{{ base64_encode(file_get_contents(public_path('images/pdf/sdi-logo.svg'))) }}"
                        alt="Vet SDI"
                    >
                </td>
                <td style="width: 78%; vertical-align: top; padding: 8px 8px 8px 14px; border-left: 4px solid #3366CC; font-size: 10px; line-height: 1.25;">
                    <div><strong>{{ Str::upper($receta->profesional->nombre.' '.$receta->profesional->apellido_uno) }}</strong></div>
                    <div>{{ $receta->profesional->nombre_especialidades .' '.
                            ((!empty($receta->profesional->nombre_tipo_especialidad) ? strtoupper($receta->profesional->nombre_tipo_especialidad) : '')) .' '.
                            ((!empty($receta->profesional->nombre_sub_tipo_especialidad) ? strtoupper($receta->profesional->nombre_sub_tipo_especialidad) : ''))
                        }}</div>
                    <div>Rut: {{ $receta->profesional->rut }}</div>
                    <div>RCV: {{ $receta->profesional->num_colegio ?: 'No informado' }}</div>
                    <div>{{ $receta->profesional->direccion }} #{{ $receta->profesional->numero_dir }}, {{ $receta->profesional->comuna->nombre }}</div>
                    <div>{{ $receta->profesional->region->nombre }}</div>
                </td>
            </tr>
        </table>
    </div>
    <div class="contenido-encabezado-dos">
        <h2 class="text-blue centrar mb-1">{{ $titulo }}</h2>
        <table>
            <tbody>
                <tr>
                    <td style="padding: 0px;"><strong>Paciente:</strong></td>
                    <td style="padding-top: 8px;">{{ $receta->paciente->nombres .' '.$receta->paciente->apellido_uno .' '.$receta->paciente->apellido_dos }}</td>
                    <td style="padding: 0px;"><strong>Rut:</strong></td>
                    <td style="padding-top: 8px;">{{ $receta->paciente->rut }}</td>
                    <td style="padding: 0px;"><strong>Sexo:</strong></td>
                    <td style="padding-top: 8px;">{{ $receta->paciente->sexo }}</td>
                    <td style="padding: 0px;"><strong>Edad:</strong></td>
                    <td style="padding-top: 8px;">{{ \Carbon\Carbon::parse($receta->paciente->fecha_nac)->age }}</td>
                </tr>
                <tr>
                    <td><strong>Dirección:</strong></td>
                    <td>{{
                        $receta->paciente->direccion .' #'. $receta->paciente->numero_dir .'; '. $receta->paciente->ciudad_nombre .'; '. $receta->paciente->region_nombre }}</td>
                    <!--Calle, Nº, Comuna. Región-->
                </tr>
                <tr>
                    <td><strong>Fecha:</strong></td>
                    <td>{{ date('d-m-Y') }}</td>
                    <!--Calle, Nº, Comuna. Región-->
                </tr>
            </tbody>
        </table>
    </div>
    <hr class="">
</header>
