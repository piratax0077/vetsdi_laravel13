{{-- Pantalla que abre el enlace del correo de tutoría compartida.
     Recibe $invitacion con: tutor, mascota, mascota_foto, rol ('co_tutor' | 'cuidador') y
     estado ('pendiente', 'activa', 'rechazada', 'expirada' o 'cancelada'), más las rutas de los formularios. --}}
@php
    $invitacion = (object) ($invitacion ?? []);
    $tutor = $invitacion->tutor ?? '';
    $mascota = $invitacion->mascota ?? '';
    $esCuidador = ($invitacion->rol ?? 'co_tutor') === 'cuidador';
    $rolTexto = $esCuidador ? 'cuidador' : 'co-tutor';
    $estado = session('invitacion_estado', $invitacion->estado ?? 'pendiente');
    $conSesion = $conSesion ?? auth()->check();
    $foto = !empty($invitacion->mascota_foto) ? $invitacion->mascota_foto : asset('images/iconos/mascotas.svg');
    $permisos = $esCuidador
        ? ['Ver la ficha, vacunas y citas', 'Agendar citas', 'Llevarla al veterinario o de paseo']
        : ['Ver y editar la ficha, vacunas y citas', 'Agendar citas', 'Mantener su información al día'];
@endphp
<!DOCTYPE html>
<html lang="es">

<head>
    <title>VET-SDI</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="author" content="SDI" />
    <link rel="icon" href="{{ asset('images/favicon.ico') }}" type="image/x-icon">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/login.css') }}?t={{ time() }}">
</head>
<style type="text/css">
    .auth-wrapper {
        background-size: cover;
        background-image: url("{{ asset('images/background_1.jpg') }}");
        background-position: center center;
        background-repeat: no-repeat;
        background-attachment: fixed;
    }
</style>

<body>
    <div class="auth-wrapper">
        <div class="auth-content">
            <div class="card text-center invitacion-tutoria">
                <div class="card-body">
                    <span class="invitacion-tutoria-foto">
                        <img src="{{ $foto }}" alt="Foto de {{ $mascota }}">
                    </span>

                    @if($estado === 'activa')
                        <h5 class="mb-2">Listo. Ahora eres {{ $rolTexto }} de {{ $mascota }}.</h5>
                        <p class="text-muted">Ya puedes verla en Mis mascotas junto a tus otras mascotas.</p>
                        <a href="{{ $rutaMisMascotas ?? route('paciente.mis_mascotas') }}" class="btn btn-info btn-block">Ir a Mis mascotas</a>

                    @elseif($estado === 'rechazada')
                        <h5 class="mb-2">Rechazaste la invitación</h5>
                        <p class="text-muted mb-0">Le avisaremos a {{ $tutor }}. Si cambias de opinión, pídele que te invite de nuevo.</p>

                    @elseif(in_array($estado, ['expirada', 'cancelada'], true))
                        <h5 class="mb-2">Esta invitación ya no está disponible</h5>
                        <p class="text-muted mb-0">
                            {{ $estado === 'expirada' ? 'Pasaron más de 7 días desde que se envió.' : $tutor . ' canceló la invitación.' }}
                            Si quieres cuidar de {{ $mascota }}, pídele a {{ $tutor }} que te envíe una nueva.
                        </p>

                    @else
                        <h5 class="mb-2">Te invitaron a cuidar de {{ $mascota }}</h5>
                        <p class="text-muted">{{ $tutor }} quiere compartir la tutoría contigo.</p>

                        <div class="invitacion-tutoria-rol">
                            <span class="invitacion-tutoria-rol-titulo">Como {{ $rolTexto }} podrás:</span>
                            <ul>
                                @foreach($permisos as $permiso)
                                    <li><i class="feather icon-check"></i> {{ $permiso }}</li>
                                @endforeach
                            </ul>
                        </div>

                        @if(session('mensaje_error'))
                            <span class="alert alert-warning d-block" role="alert">{{ session('mensaje_error') }}</span>
                        @endif

                        @if($conSesion)
                            <div class="invitacion-tutoria-acciones">
                                <form method="POST" action="{{ $rutaRechazar ?? '#' }}">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-dark btn-block">Rechazar</button>
                                </form>
                                <form method="POST" action="{{ $rutaAceptar ?? '#' }}">
                                    @csrf
                                    <button type="submit" class="btn btn-info btn-block">Aceptar</button>
                                </form>
                            </div>
                        @else
                            <p class="ingreso-ayuda text-center mb-3">
                                Para aceptar, ingresa con tu cuenta o créala con el mismo correo que recibió la invitación.
                            </p>
                            <div class="invitacion-tutoria-acciones">
                                <a href="{{ $rutaIngreso ?? route('home.ingreso') }}" class="btn btn-outline-dark btn-block">Ingresar</a>
                                <a href="{{ $rutaRegistro ?? route('home.ingreso') }}" class="btn btn-info btn-block">Crear cuenta</a>
                            </div>
                        @endif

                        <p class="ingreso-ayuda text-center mt-3 mb-0">
                            Si no conoces a {{ $tutor }} o no esperabas esta invitación, puedes ignorarla.
                        </p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('js/jquery.js') }}"></script>
    <script src="{{ asset('js/plugins/bootstrap.min.js') }}"></script>
</body>

</html>
