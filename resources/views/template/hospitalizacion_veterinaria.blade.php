<!DOCTYPE html>
<html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="csrf-token" content="{{ csrf_token() }}"><title>Hospitalización veterinaria</title>
<link rel="stylesheet" href="{{ asset('css/style.css') }}"><link rel="stylesheet" href="{{ asset('css/formulario_sm.css') }}"><link rel="stylesheet" href="{{ asset('css/escritorio_laboratorio.css') }}">
<style>.pcoded-main-container{margin-left:0!important;padding-top:0!important}.barra-hospital{display:flex;justify-content:space-between;align-items:center;padding:16px 24px;background:white;gap:16px}.barra-hospital form{margin:0}</style>
</head><body>
<nav class="barra-hospital" aria-label="Servicio hospitalario"><a href="{{ route('vet.hospital.servicio') }}">Hospitalización veterinaria</a><div><a class="btn btn-outline-info btn-sm" href="{{ route('cuenta.seleccion') }}">Cambiar perfil</a></div><form method="post" action="{{ route('logout') }}">@csrf<button class="btn btn-outline-dark btn-sm">Cerrar sesión</button></form></nav>
@yield('content')
<script src="{{ asset('js/vendor-all.min.js') }}"></script><script src="{{ asset('js/plugins/bootstrap.min.js') }}"></script><script src="{{ asset('js/centro-formularios.js') }}"></script>
@yield('page-script')
</body></html>
