<p><strong>Datos de la clínica</strong><br>
    {{ $lugar?->nombre ?? 'Sin registro' }}<br>
    Dirección: {{ trim(($lugar?->Direccion?->direccion ?? '').' '.($lugar?->Direccion?->numero_dir ?? '')) ?: 'Sin registro' }}<br>
    Teléfono: {{ $lugar?->telefono ?? $lugar?->telefono_uno ?? 'Sin registro' }}<br>
    Profesional: {{ $profesional->nombreCompleto() }}@if($mostrarRegistroProfesional ?? true) · Registro profesional: {{ $datos['registro_profesional'] ?? 'Sin registro' }}@endif
</p>
<p><strong>Fecha y hora:</strong> {{ $fecha }} · <strong>Ficha clínica:</strong> {{ $ficha->id }}</p>
<p><strong>Datos de la mascota</strong><br>
    Nombre: {{ $mascota->nombre }} · Especie: {{ $mascota->especieMascota?->nombre ?? $mascota->otra_especie ?? 'Sin registro' }}
    · Raza: {{ $mascota->razaMascota?->nombre ?? 'Sin registro' }}<br>
    Sexo: {{ ['M' => 'Macho', 'F' => 'Hembra'][$mascota->sexo] ?? 'Sin registro' }}
    · Edad: {{ $mascota->fecha_nacimiento?->diffForHumans(now('America/Santiago'), true, false, 2) ?? 'Sin registro' }}
    · Peso: {{ isset($datos['peso']) ? $datos['peso'].' kg' : ($ficha->peso ? $ficha->peso.' kg' : 'Sin registro') }}
    · Microchip: {{ $mascota->chip ?: 'Sin registro' }}
</p>
<p><strong>Datos del tutor</strong><br>
    Nombre: {{ trim($tutor->nombres.' '.$tutor->apellido_uno.' '.$tutor->apellido_dos) }}
    · RUT: {{ mb_strtoupper(trim($tutor->rut ?? '')) ?: 'Sin registro' }} · Teléfono: {{ $tutor->telefono_uno ?: ($tutor->telefono_dos ?: 'Sin registro') }}
</p>
