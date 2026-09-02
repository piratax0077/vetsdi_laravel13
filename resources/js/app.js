require('./bootstrap');

window.Vue = require('vue').default;

// Registrar componente globalmente
Vue.component('pantalla-turnos', require('./components/PantallaTurnos.vue').default);

// Inicializar Vue
const app = new Vue({
    el: '#app',
});

// import Alpine from 'alpinejs';
// window.Alpine = Alpine;
// Alpine.start();
