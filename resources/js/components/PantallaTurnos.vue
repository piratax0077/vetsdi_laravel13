<template>
    <div>
        <!-- Header -->
        <header class="header bg-white py-3">
            <div class="row g-0 align-items-center">
                <div class="col-lg-8 d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center" style="height: 100px;">
                        <img :src="logoInsi" alt="Logo" class="img-fluid" style="max-height: 100px;">
                    </div>
                    <div class="hora-header text-end">{{ horaActual }}</div>
                </div>
                <div class="col-lg-4 piso-header">
                    PISO {{ piso }}
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="main-container container-fluid py-3">
            <div class="row g-3">
                <!-- Tabla de turnos -->
                <div class="col-lg-8">
                    <div class="turnos-container d-flex flex-column p-4">
                        <div class="table-header row g-0 text-white p-3 fs-8 rounded">
                            <div class="col-3 th-col-uno">LLAMADO</div>
                            <div class="col-6 th-col-dos">TURNO</div>
                            <div class="col-3 th-col-tres">BOX</div>
                        </div>

                        <div class="table-body flex-grow-1">
                            <div v-for="(turno, index) in turnos" :key="turno.id"
                                class="table-row row g-0 text-white p-3 fs-8">
                                <div class="col-3 tr-col-uno">{{ turno.minutos }} MIN.</div>
                                <div class="col-6 tr-col-dos">{{ turno.nombre_paciente }}</div>
                                <div class="col-3 tr-col-tres">{{ turno.nombre_box }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Turno actual -->
                <div class="col-lg-4">
                    <div class="current-turn p-5" :class="{ 'flash-turn': flashActive }">
                        <div class="turn-title text-center mb-5 fs-10">SU TURNO</div>
                        <div class="patient-name text-center mb-5">{{ nombrePaciente }}</div>
                        <div class="box-info text-center fs-11">BOX</div>
                        <div class="box-info2 text-center">{{ nombreBox }}</div>
                    </div>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="footer py-3">
            <div class="container-fluid">
                <div class="row align-items-center">
                    <div class="col-md-8 text-start fs-8">
                        SE PARTE DE SDI, REGÍSTRATE EN WWW.MED-SDI.CL
                    </div>
                    <div class="col-md-2 text-end footer-right fs-7">
                        <img :src="logoPais" alt="" style="height: 120px;">
                    </div>
                </div>
            </div>
        </footer>

        <!-- Modal de bienvenida -->
        <div class="modal fade" tabindex="-1" :class="{ show: showModal }" style="display: block;" v-if="showModal">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Mensaje Inicial</h5>
                        <button type="button" class="close" @click="cerrarModal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <p>Bienvenido a la pantalla de turnos del sistema SDI. Aquí podrás ver los turnos actuales y el
                            turno que te corresponde.</p>
                        <p>Para más información, visita nuestro sitio web o contacta con el soporte técnico.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" @click="cerrarModal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>

        <audio ref="dingSound" :src="dingSoundSrc"></audio>

    </div>
</template>

<script>
export default {
    props: {
        idTelevisor: {
            type: Number,
            required: true
        },
        piso: {
            type: [String, Number],
            required: true,
            default: 'Piso 1'
        },
        logoInsi: {
            type: String,
            default: '/images/logo_instituciones/logo_institucion_generico.jpg'
        },
    },

    data() {
        return {
            horaActual: '--:--',
            turnos: [],
            nombrePaciente: '-',
            nombreBox: '-',
            flashActive: false,
            // logoInsi: '/images/logo_instituciones/logo_institucion_generico.jpg',
            logoPais: '/images/logo_pais_horizontal.svg',
            dingSoundSrc: '/sounds/new-notification-022-370046.mp3',
            intervalHora: null,
            intervalTurnos: null,
            echoChannel: null,
            ultimoPaciente: '',
            ultimoCantidadLlamados: '',
            showModal: true,
            sonidoHabilitado: false,
        };
    },

    mounted() {
        this.actualizarHora();
        this.intervalHora = setInterval(this.actualizarHora, 1000);
        this.cargarTurnos();
        this.intervalTurnos = setInterval(this.cargarTurnos, 5000); // Polling de respaldo
        this.iniciarEcho();
    },

    beforeDestroy() {
        clearInterval(this.intervalHora);
        clearInterval(this.intervalTurnos);
        if (this.echoChannel) {
            window.Echo.leave(this.echoChannel);
        }
    },

    methods: {
        actualizarHora() {
            const ahora = new Date();
            this.horaActual = ahora.toLocaleTimeString('es-CL', {
                hour: '2-digit',
                minute: '2-digit',
                hour12: false
            });
        },

        async cargarTurnos() {
            try {
                const response = await axios.get(`/api/llamados/televisor/${this.idTelevisor}`);
                this.procesarTurnos(response.data.turnos);
            } catch (error) {
                console.error('Error al cargar turnos:', error);
            }
        },

        iniciarEcho() {
            if (!window.Echo) {
                console.error('Echo no está disponible');
                return;
            }
            this.echoChannel = window.Echo.channel(`llamados.${this.idTelevisor}`)
                .listen('NuevoLlamado', (data) => {
                    // this.procesarTurnos([data.llamado, ...this.turnos]);
                    this.procesarTurnos(data.turnos);
                });
        },

        procesarTurnos(turnos) {
            this.turnos = turnos.slice(0, 10);

            if (this.turnos.length > 0) {
                const ultimo = this.turnos[0];
                this.nombrePaciente = ultimo.nombre_paciente;
                this.nombreBox = ultimo.nombre_box;

                // Detecta si hay un nuevo paciente o un nuevo llamado
                if (this.ultimoPaciente !== ultimo.nombre_paciente || this.ultimoCantidadLlamados !== ultimo.cantidad_llamados) {
                    this.ultimoPaciente = ultimo.nombre_paciente;
                    this.ultimoCantidadLlamados = ultimo.cantidad_llamados;

                    this.activarFlash();
                    this.$refs.dingSound.play();

                    // Lectura automática del nombre y box
                    if ('speechSynthesis' in window) {
                        let textoBox = ultimo.nombre_box ? `Box ${ultimo.nombre_box}` : '';
                        let textoLectura = `${ultimo.nombre_paciente} ${textoBox}`;
                        let utter = new SpeechSynthesisUtterance(textoLectura);
                        utter.lang = 'es-ES';
                        utter.rate = 0.6;
                        window.speechSynthesis.speak(utter);
                    }
                }
            } else {
                this.nombrePaciente = 'ESPERANDO PACIENTE';
                this.nombreBox = '-';
                this.ultimoPaciente = '';
                this.ultimoCantidadLlamados = '';
            }
        },

        activarFlash() {
            this.flashActive = true;
            setTimeout(() => {
                this.flashActive = false;
            }, 1600);
        },

        cerrarModal() {
            this.showModal = false;
            this.sonidoHabilitado = true;
            // Reproduce un sonido para desbloquear el audio
            this.$refs.dingSound.play().catch(() => { });
        },
        procesarTurnos(turnos) {
            this.turnos = turnos.slice(0, 10);
            if (this.turnos.length > 0) {
                const ultimo = this.turnos[0];
                this.nombrePaciente = ultimo.nombre_paciente;
                this.nombreBox = ultimo.nombre_box;
                if (this.ultimoPaciente !== ultimo.nombre_paciente || this.ultimoCantidadLlamados !== ultimo.cantidad_llamados) {
                    this.ultimoPaciente = ultimo.nombre_paciente;
                    this.ultimoCantidadLlamados = ultimo.cantidad_llamados;
                    this.activarFlash();
                    if (this.sonidoHabilitado) this.$refs.dingSound.play();
                    // Lectura automática
                    if (this.sonidoHabilitado && 'speechSynthesis' in window) {
                        let textoBox = ultimo.nombre_box ? `Box ${ultimo.nombre_box}` : '';
                        let textoLectura = `${ultimo.nombre_paciente} ${textoBox}`;
                        let utter = new SpeechSynthesisUtterance(textoLectura);
                        utter.lang = 'es-ES';
                        utter.rate = 0.6;
                        window.speechSynthesis.speak(utter);
                    }
                }
            } else {
                this.nombrePaciente = 'ESPERANDO PACIENTE';
                this.nombreBox = '-';
                this.ultimoPaciente = '';
                this.ultimoCantidadLlamados = '';
            }
        },
    }
};
</script>

<style scoped>
/* Tus estilos CSS existentes */
</style>
