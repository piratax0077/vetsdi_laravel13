
<header>
    <div class="contenido-encabezado-uno">
        <div class="contenido-logo">
            <img class="logo" src="{{ public_path('images/pdf/sdi-logo.svg') }}" alt="VET SDI">
        </div>
        <div class="contenido-infoprof">
            <p><strong>{{ $cuerpo['array_profesional']['nombre'] }}</strong></p>
            <p>{{ $cuerpo['array_profesional']['especialidad'] }}</p>
            <p>Rut: {{ $cuerpo['array_profesional']['rut'] }}</p>
            @if($cuerpo['array_profesional']['id_especialidad'] == 2)
                <p>ICD: {{ $cuerpo['array_profesional']['num_colegio'] }}</p>
            @else
                <p>RCM: {{ $cuerpo['array_profesional']['num_colegio'] }}</p>
            @endif
            <p>{{ $cuerpo['array_lugar_atencion']['direccion'] }} </p>
            <p>{{ $cuerpo['array_lugar_atencion']['region'] }}</p>
        </div>
    </div>
    <div class="contenido-encabezado-dos">
        <h2 class="text-blue centrar mb-1" style="text-transform: uppercase;">{{ isset($titulo) ? $titulo : 'Exámen' }}</h2>
        <table>
            <tbody>
                <tr>
                    <td style="padding: 0px;"><strong>{{ !empty($cuerpo['array_mascota']) ? 'Tutor:' : 'Paciente:' }}</strong></td>
                    <td style="padding-top: 8px;">{{ $cuerpo['array_paciente']['nombre'] }}</td>
                    <td style="padding: 0px;"><strong>Rut:</strong></td>
                    <td style="padding-top: 8px;">{{ $cuerpo['array_paciente']['rut'] }}</td>
                    <td style="padding: 0px;"><strong>Sexo:</strong></td>
                    <td style="padding-top: 8px;">{{ $cuerpo['array_paciente']['sexo'] }}</td>
                    <td style="padding: 0px;"><strong>Edad:</strong></td>
                    <td style="padding-top: 8px;">{{ \Carbon\Carbon::parse($cuerpo['array_paciente']['fecha_nac'])->age }}</td>
                </tr>
                @if(!empty($cuerpo['array_mascota']))
                    <tr>
                        <td><strong>Mascota:</strong></td>
                        <td>{{ $cuerpo['array_mascota']['nombre'] }}</td>
                        <td><strong>Especie:</strong></td>
                        <td>{{ $cuerpo['array_mascota']['especie'] ?: '-' }}</td>
                        <td><strong>Raza:</strong></td>
                        <td>{{ $cuerpo['array_mascota']['raza'] ?: '-' }}</td>
                        <td><strong>Chip:</strong></td>
                        <td>{{ $cuerpo['array_mascota']['chip'] ?: 'Sin registro' }}</td>
                    </tr>
                @endif
                <tr>
                    <td><strong>Dirección:</strong></td>
                    <td>{{ $cuerpo['array_paciente']['direccion'] }}</td>
                    <!--Calle, Nº, Comuna. Región-->
                </tr>
            </tbody>
        </table>
    </div>
    <hr class="">
    <div class="fecha"><strong>Fecha:</strong> {{ $cuerpo['array_ficha_atencion']['created_at'] }}</div>
</header>
