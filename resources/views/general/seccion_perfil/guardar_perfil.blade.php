<script>
window.perfilUsuarioConfig = {
    tipo: @json(isset($asistente) ? 'asistente' : 'profesional'),
    csrf: @json(csrf_token()),
    personales: @json(isset($asistente) ? route('asistentes.perfil.personales') : route('profesional.editar_datos_personales_perfil')),
    contacto: @json(isset($asistente) ? route('asistentes.perfil.contacto') : route('profesional.editar_datos_contacto_perfil')),
    direccion: @json(isset($asistente) ? route('asistentes.perfil.direccion') : route('profesional.editar_datos_residencia_perfil')),
    contrasena: @json(route('perfil.usuario.contrasena'))
};
</script>
<script src="{{ asset('js/perfil-usuario.js') }}"></script>
