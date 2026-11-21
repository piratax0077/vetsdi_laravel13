document.addEventListener('DOMContentLoaded', function () {
    const lista = document.getElementById('productos-compra');
    if (!lista) return;
    const agregar = document.getElementById('agregar-producto-compra');
    function modalidad(bloque) {
        const individual = bloque.querySelector('[name$="[modalidad_consumo]"]').value === 'individual';
        const cantidad = bloque.querySelector('[name$="[cantidad_inicial]"]');
        cantidad.step = individual ? '1' : '0.001';
        cantidad.min = individual ? '1' : '0.001';
    }
    function ordenar() {
        const bloques = lista.querySelectorAll('.producto-compra');
        bloques.forEach((bloque, indice) => {
            modalidad(bloque);
            bloque.querySelector('.numero-producto').textContent = indice + 1;
            bloque.querySelector('.quitar-producto').disabled = bloques.length === 1;
            bloque.querySelectorAll('[name]').forEach(campo => {
                campo.name = campo.name.replace(/productos\[\d+\]/, 'productos[' + indice + ']');
            });
            bloque.querySelectorAll('[id], label[for]').forEach(elemento => {
                for (const atributo of ['id', 'for']) {
                    if (elemento.hasAttribute(atributo)) {
                        elemento.setAttribute(atributo, elemento.getAttribute(atributo).replace(/-\d+$/, '-' + indice));
                    }
                }
            });
        });
        agregar.disabled = bloques.length >= 40;
    }
    agregar.addEventListener('click', function () {
        if (lista.children.length >= 40) return;
        const copia = lista.firstElementChild.cloneNode(true);
        copia.querySelectorAll('input').forEach(campo => campo.value = '');
        copia.querySelectorAll('select').forEach(campo => campo.selectedIndex = 0);
        lista.append(copia);
        ordenar();
        copia.querySelector('select, input').focus();
    });
    lista.addEventListener('change', function (evento) {
        if (evento.target.matches('[name$="[modalidad_consumo]"]')) modalidad(evento.target.closest('.producto-compra'));
    });
    lista.addEventListener('click', function (evento) {
        const boton = evento.target.closest('.quitar-producto');
        if (boton && lista.children.length > 1) {
            boton.closest('.producto-compra').remove();
            ordenar();
        }
    });
    ordenar();
});
