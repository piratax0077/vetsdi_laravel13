<script>
document.addEventListener('DOMContentLoaded', function () {
    var input = document.getElementById('foto-asistente-input');
    var imagen = document.getElementById('foto-asistente');
    if (!input || !imagen) return;
    var ocupado = false;
    async function enviar(eliminar) {
        if (ocupado || (!eliminar && !input.files.length)) return;
        ocupado = true;
        input.disabled = true;
        try {
            var data = new FormData();
            if (!eliminar) data.append('foto_perfil', input.files[0]);
            var respuesta = await fetch(eliminar ? @json(route('asistentes.foto.eliminar')) : @json(route('asistentes.foto.guardar')), {
                method: eliminar ? 'DELETE' : 'POST',
                headers: {'Accept':'application/json','X-CSRF-TOKEN':@json(csrf_token())},
                body: eliminar ? undefined : data
            });
            var resultado = await respuesta.json();
            if (!respuesta.ok || !resultado.success) throw new Error(Object.values(resultado.errors || {}).flat().join('\n') || resultado.message || 'No fue posible guardar la foto.');
            imagen.src = eliminar ? @json(asset('images/iconos/usuario_asistente.svg')) : resultado.foto_url;
        } catch (error) {
            if (window.Swal) Swal.fire({icon:'error',text:error.message}); else alert(error.message);
        } finally { ocupado=false; input.disabled=false; input.value=''; }
    }
    input.addEventListener('change', function () { enviar(false); });
    document.getElementById('eliminar-foto-asistente').addEventListener('click', function (event) { event.preventDefault(); enviar(true); });
});
</script>
