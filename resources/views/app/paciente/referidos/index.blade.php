@extends('template.usuario.template')

@section('content')
@php
    $tipos = [
        'tutor' => 'Tutor',
        'profesional' => 'Veterinario',
        'centro' => 'Centro veterinario',
        'alimentacion' => 'Alimentación',
        'farmacia' => 'Farmacia veterinaria',
    ];

    $coloresEstado = [
        'enviada' => 'warning',
        'visitada' => 'warning',
        'registrada' => 'primary',
        'activada' => 'success',
        'bonificada' => 'success',
        'cancelada' => 'secondary',
    ];
@endphp
<div class="pcoded-main-container">
    <div class="pcoded-content">
        <div class="referidos">

            <section class="referidos-portada">
                <div class="referidos-portada-texto">
                    <a href="{{ route('paciente.home') }}" class="referidos-volver">
                        <i class="feather icon-arrow-left" aria-hidden="true"></i> Volver a mi escritorio
                    </a>
                    <h3><i class="fa fa-gift" aria-hidden="true"></i> Invita y gana con VET SDI</h3>
                    <p>Invita a tutores, veterinarios, centros, comercios de alimentación o farmacias veterinarias. Comparte por correo o WhatsApp y sigue tus puntos.</p>
                </div>
                <div class="referidos-codigo">
                    <span>Tu código personal</span>
                    <strong id="codigo_personal">VET-{{ strtoupper(substr(hash('sha256', Auth::id().'|'.Auth::user()->email), 0, 8)) }}</strong>
                    <button type="button" class="btn btn-light btn-sm" id="copiar_codigo">
                        <i class="feather icon-copy" aria-hidden="true"></i> <span>Copiar</span>
                    </button>
                </div>
            </section>

            @if (session('success'))
                <div class="alert alert-success" role="alert">{{ session('success') }}</div>
            @endif
            @if (session('warning'))
                <div class="alert alert-warning" role="alert">{{ session('warning') }}</div>
            @endif
            @if ($errors->any())
                <div class="alert alert-danger" role="alert">Revisa los datos de la invitación antes de enviarla.</div>
            @endif

            <div class="row">
                <div class="col-6 col-lg-3">
                    <div class="card referidos-dato">
                        <span class="referidos-dato-icono"><i class="feather icon-mail" aria-hidden="true"></i></span>
                        <strong>{{ $resumen['enviadas'] }}</strong>
                        <span>Invitaciones</span>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="card referidos-dato">
                        <span class="referidos-dato-icono"><i class="feather icon-user-check" aria-hidden="true"></i></span>
                        <strong>{{ $resumen['registradas'] }}</strong>
                        <span>Registrados</span>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="card referidos-dato">
                        <span class="referidos-dato-icono"><i class="feather icon-check-circle" aria-hidden="true"></i></span>
                        <strong>{{ $resumen['activadas'] }}</strong>
                        <span>Activados</span>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="card referidos-dato">
                        <span class="referidos-dato-icono"><i class="feather icon-award" aria-hidden="true"></i></span>
                        <strong>{{ $resumen['puntos'] }}</strong>
                        <span>Puntos obtenidos</span>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-7">
                    <div class="card referidos-tarjeta">
                        <div class="card-header">
                            <h5>Nueva invitación</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('paciente.referidos.store') }}" class="referidos-formulario" id="formulario_referido">
                                @csrf
                                <p class="referidos-ayuda">
                                    <i class="feather icon-info" aria-hidden="true"></i>
                                    Completa los datos de la persona o local que quieres invitar. Le llegará un correo con tu invitación.
                                </p>
                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label class="floating-label-activo-sm" for="tipo_referido">Quiero invitar</label>
                                        <select class="form-control form-control-sm @error('tipo') is-invalid @enderror" name="tipo" id="tipo_referido" required>
                                            <option value="tutor" @selected(old('tipo') === 'tutor')>A otro tutor de mascotas</option>
                                            <option value="profesional" @selected(old('tipo') === 'profesional')>A un veterinario</option>
                                            <option value="centro" @selected(old('tipo') === 'centro')>A un centro veterinario</option>
                                            <option value="alimentacion" @selected(old('tipo') === 'alimentacion')>A un comercio de alimentación</option>
                                            <option value="farmacia" @selected(old('tipo') === 'farmacia')>A una farmacia veterinaria</option>
                                        </select>
                                        @error('tipo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label class="floating-label-activo-sm" for="nombre_invitado">Nombre del contacto</label>
                                        <input type="text" class="form-control form-control-sm @error('nombre_invitado') is-invalid @enderror" name="nombre_invitado" id="nombre_invitado" value="{{ old('nombre_invitado') }}" maxlength="150" placeholder="Ej.: Camila Rojas" autocomplete="off" required>
                                        @error('nombre_invitado')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label class="floating-label-activo-sm" for="email_invitado">Correo electrónico</label>
                                        <input type="email" class="form-control form-control-sm @error('email_invitado') is-invalid @enderror" name="email_invitado" id="email_invitado" value="{{ old('email_invitado') }}" maxlength="190" placeholder="Ej.: nombre@correo.cl" autocomplete="off" required>
                                        @error('email_invitado')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label class="floating-label-activo-sm" for="telefono_numero">WhatsApp (opcional)</label>
                                        <div class="input-group input-group-sm referidos-telefono">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">+569</span>
                                            </div>
                                            <input type="tel" class="form-control form-control-sm @error('telefono_invitado') is-invalid @enderror" id="telefono_numero" inputmode="numeric" maxlength="8" pattern="[0-9]{8}" title="Escribe los 8 dígitos que van después del +569" placeholder="12345678" autocomplete="off">
                                            @error('telefono_invitado')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                        {{-- Lo que se envía es el número completo, con el +569 --}}
                                        <input type="hidden" name="telefono_invitado" id="telefono_invitado" value="{{ old('telefono_invitado') }}">
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="form-group col-md-6 d-none" id="grupo_nombre_centro">
                                        <label class="floating-label-activo-sm" for="nombre_centro">Nombre del centro</label>
                                        <input type="text" class="form-control form-control-sm @error('nombre_centro') is-invalid @enderror" name="nombre_centro" id="nombre_centro" value="{{ old('nombre_centro') }}" maxlength="190" autocomplete="off">
                                        @error('nombre_centro')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="referidos-formulario-pie">
                                    <button type="submit" class="btn btn-info">
                                        <i class="feather icon-mail mr-1" aria-hidden="true"></i>Registrar y enviar correo
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-lg-5">
                    <div class="card referidos-tarjeta">
                        <div class="card-header">
                            <h5>Esquema de incentivo</h5>
                        </div>
                        <div class="card-body">
                            <ol class="referidos-pasos">
                                <li>
                                    <strong>Registro verificado</strong>
                                    <small>100 puntos cuando el invitado crea y verifica su cuenta.</small>
                                </li>
                                <li>
                                    <strong>Primera activación</strong>
                                    <small>200 puntos adicionales cuando empieza a utilizar VET SDI.</small>
                                </li>
                                <li>
                                    <strong>Comercio o centro activo</strong>
                                    <small>500 puntos si el centro, comercio de alimentación o farmacia completa su primera operación.</small>
                                </li>
                            </ol>
                            <div class="alert alert-light border mb-0">
                                <strong>Conversión sugerida:</strong> 100 puntos = $1.000 de crédito, aplicable hasta al 50% del plan mensual.
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card referidos-tarjeta">
                <div class="card-header">
                    <h5>Seguimiento de invitaciones</h5>
                </div>
                <div class="card-body">
                    @if ($referidos->isEmpty())
                        <div class="referidos-vacio">
                            <i class="feather icon-inbox" aria-hidden="true"></i>
                            <p>Aún no has enviado invitaciones.</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover referidos-tabla">
                                <thead>
                                    <tr>
                                        <th>Invitado</th>
                                        <th>Tipo</th>
                                        <th>Código</th>
                                        <th>Fecha</th>
                                        <th>Estado</th>
                                        <th>Puntos</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($referidos as $referido)
                                        @php
                                            $whatsapp = preg_replace('/\D+/', '', (string) $referido->telefono_invitado);
                                            $mensaje = 'Hola '.$referido->nombre_invitado.', te invito a VET SDI: '.route('referidos.aceptar', $referido->token).' Código: '.$referido->codigo;
                                            $cancelable = ! in_array($referido->estado, ['activada', 'bonificada', 'cancelada']);
                                        @endphp
                                        <tr>
                                            <td>
                                                <strong>{{ $referido->nombre_invitado }}</strong>
                                                <small>{{ $referido->email_invitado }}</small>
                                                @if ($referido->telefono_invitado)
                                                    <small>{{ $referido->telefono_invitado }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                {{ $tipos[$referido->tipo] ?? ucfirst($referido->tipo) }}
                                                @if ($referido->nombre_centro)
                                                    <small>{{ $referido->nombre_centro }}</small>
                                                @endif
                                            </td>
                                            <td>{{ $referido->codigo }}</td>
                                            <td>{{ optional($referido->fecha_envio)->format('d-m-Y') }}</td>
                                            <td>
                                                <span class="badge badge-light-{{ $coloresEstado[$referido->estado] ?? 'secondary' }}">{{ ucfirst($referido->estado) }}</span>
                                            </td>
                                            <td>{{ $referido->puntos_otorgados }} / {{ $referido->puntos_propuestos }}</td>
                                            <td class="referidos-acciones">
                                                @if ($whatsapp)
                                                    <a class="btn btn-success btn-sm" href="https://wa.me/{{ $whatsapp }}?text={{ urlencode($mensaje) }}" target="_blank" rel="noopener">
                                                        <i class="feather icon-message-circle" aria-hidden="true"></i> WhatsApp
                                                    </a>
                                                @endif
                                                @if ($cancelable)
                                                    <form method="POST" action="{{ route('paciente.referidos.cancelar', $referido) }}" class="formulario-cancelar-referido">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit" class="btn btn-outline-danger btn-sm">Cancelar</button>
                                                    </form>
                                                @endif
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
@endsection

@section('page-script')
<script src="{{ asset('js/paciente/referidos.js') }}?t={{ time() }}"></script>
@endsection
