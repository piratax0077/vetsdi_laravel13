@php
    $pacientePeriodontal = $mascota ?? $paciente ?? null;
    $especiePeriodontal = \App\Support\DenticionVeterinaria::especie($mascota);
    $esFelinoPeriodontal = $especiePeriodontal === 'felino';
    $tipoPeriodontal = ucfirst($especiePeriodontal);
    $arcadasPeriodontales = \App\Support\DenticionVeterinaria::arcadas($especiePeriodontal);
    $piezasSuperioresPeriodontal = $arcadasPeriodontales['superior'];
    $piezasInferioresPeriodontal = $arcadasPeriodontales['inferior'];
    $mascotaIdPeriodontal = (int) (request('id_mascota') ?: optional($pacientePeriodontal)->id);
    $fichaIdPeriodontal = (int) ($id_ficha_atencion ?? request('id_ficha_atencion') ?? 0);
    $registrosPeriodontales = collect();
    if ($mascotaIdPeriodontal && \Illuminate\Support\Facades\Schema::hasTable('periodontogramas_mascotas')) {
        $consultaPeriodontal = \Illuminate\Support\Facades\DB::table('periodontogramas_mascotas')
            ->where('mascota_id', $mascotaIdPeriodontal);
        if ($fichaIdPeriodontal) {
            $consultaPeriodontal->where('ficha_atencion_id', $fichaIdPeriodontal);
        }
        $registrosPeriodontales = $consultaPeriodontal->get()->keyBy('pieza');
    }
@endphp

<style>
    .periodonto-vet { --pv-teal:#159c98; --pv-navy:#263b55; --pv-line:#dbe5eb; }
    .periodonto-vet .pv-hero { background:linear-gradient(120deg,#edfafa,#f8fbfd); border:1px solid #d7eceb; border-radius:14px; padding:18px 20px; }
    .periodonto-vet .pv-title { color:var(--pv-navy); font-size:1.35rem; font-weight:700; margin:0; }
    .periodonto-vet .pv-species { background:#d9f4f1; color:#087f79; border-radius:999px; padding:6px 12px; font-weight:700; }
    .periodonto-vet .pv-arch { background:#fff; border:1px solid var(--pv-line); border-radius:14px; padding:15px; box-shadow:0 4px 14px rgba(35,55,75,.06); }
    .periodonto-vet .pv-arch-title { color:#607286; font-size:.78rem; font-weight:700; letter-spacing:.08em; text-transform:uppercase; margin-bottom:10px; }
    .periodonto-vet .pv-teeth { display:grid; grid-template-columns:repeat(auto-fit,minmax(55px,1fr)); gap:7px; }
    .periodonto-vet .pv-tooth { min-height:62px; border:1px solid #c9d9e2; border-radius:10px; color:#415a70; background:#f8fbfc; font-weight:700; transition:.15s ease; }
    .periodonto-vet .pv-tooth i { display:block; color:#8baab7; font-size:1.15rem; margin-bottom:3px; }
    .periodonto-vet .pv-tooth:hover,.periodonto-vet .pv-tooth.active { border-color:var(--pv-teal); background:#e5f7f5; color:#087f79; transform:translateY(-1px); }
    .periodonto-vet .pv-tooth.has-data { box-shadow:inset 0 -4px 0 var(--pv-teal); }
    .periodonto-vet .pv-editor { background:#fff; border:1px solid var(--pv-line); border-radius:14px; padding:18px; }
    .periodonto-vet .pv-selected { color:var(--pv-teal); font-weight:800; }
    .periodonto-vet .pv-label { color:#53677b; font-size:.78rem; font-weight:700; margin-bottom:4px; }
    .periodonto-vet .pv-switch { border:1px solid var(--pv-line); border-radius:10px; min-height:61px; padding:17px 12px 8px; }
    @media (max-width:767px) { .periodonto-vet .pv-teeth { grid-template-columns:repeat(4,1fr); } }
</style>

<section class="periodonto-vet py-3" id="periodonto-veterinario">
    <div class="pv-hero d-flex flex-wrap align-items-center justify-content-between mb-3">
        <div>
            <h3 class="pv-title"><i class="fas fa-tooth mr-2"></i>Evaluación periodontal veterinaria</h3>
            <div class="text-muted mt-1">Registre los hallazgos clínicos seleccionando cada pieza dental.</div>
        </div>
        <span class="pv-species mt-2 mt-md-0">Dentición {{ $tipoPeriodontal }}</span>
    </div>

    <div class="pv-arch mb-3">
        <div class="pv-arch-title">Arcada superior</div>
        <div class="pv-teeth">
            @foreach($piezasSuperioresPeriodontal as $pieza)
                <button type="button" class="pv-tooth {{ $registrosPeriodontales->has($pieza) ? 'has-data' : '' }}" data-pieza="{{ $pieza }}">
                    <i class="fas fa-tooth"></i>{{ $pieza }}
                </button>
            @endforeach
        </div>
    </div>
    <div class="pv-arch mb-3">
        <div class="pv-arch-title">Arcada inferior</div>
        <div class="pv-teeth">
            @foreach($piezasInferioresPeriodontal as $pieza)
                <button type="button" class="pv-tooth {{ $registrosPeriodontales->has($pieza) ? 'has-data' : '' }}" data-pieza="{{ $pieza }}">
                    <i class="fas fa-tooth"></i>{{ $pieza }}
                </button>
            @endforeach
        </div>
    </div>

    <div class="pv-editor">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h4 class="mb-0">Registro de pieza <span class="pv-selected" id="pv-pieza-label">—</span></h4>
            <small class="text-muted">Los campos corresponden a medición periodontal veterinaria.</small>
        </div>
        <form id="pv-form">
            @csrf
            <input type="hidden" name="mascota_id" value="{{ $mascotaIdPeriodontal }}">
            <input type="hidden" name="ficha_atencion_id" value="{{ $fichaIdPeriodontal ?: '' }}">
            <input type="hidden" name="pieza" id="pv-pieza" value="">
            <div class="row">
                <div class="col-6 col-md-2 mb-3"><label class="pv-label">Sondaje (mm)</label><input type="number" min="0" max="15" name="profundidad" id="pv-profundidad" class="form-control" value="0" required></div>
                <div class="col-6 col-md-2 mb-3"><label class="pv-label">Recesión (mm)</label><input type="number" min="0" max="15" name="recesion" id="pv-recesion" class="form-control" value="0" required></div>
                <div class="col-6 col-md-2 mb-3"><label class="pv-label">Inserción clínica</label><input type="number" id="pv-insercion" class="form-control" value="0" readonly></div>
                <div class="col-6 col-md-2 mb-3"><label class="pv-label">Movilidad</label><select name="movilidad" id="pv-movilidad" class="form-control"><option value="0">0</option><option value="1">1</option><option value="2">2</option><option value="3">3</option></select></div>
                <div class="col-6 col-md-2 mb-3"><label class="pv-label">Furcación</label><select name="furcacion" id="pv-furcacion" class="form-control"><option value="0">0</option><option value="1">1</option><option value="2">2</option><option value="3">3</option></select></div>
                <div class="col-6 col-md-1 mb-3"><div class="pv-switch"><label><input type="checkbox" name="sangrado" id="pv-sangrado" value="1"> Sangrado</label></div></div>
                <div class="col-6 col-md-1 mb-3"><div class="pv-switch"><label><input type="checkbox" name="placa" id="pv-placa" value="1"> Placa</label></div></div>
                <div class="col-12 mb-3"><label class="pv-label">Observaciones de la pieza</label><textarea name="observaciones" id="pv-observaciones" class="form-control" rows="2" maxlength="3000"></textarea></div>
            </div>
            <div class="text-right"><button type="submit" class="btn btn-info" id="pv-guardar" disabled><i class="fas fa-save mr-1"></i> Guardar evaluación periodontal</button></div>
        </form>
    </div>
</section>

<script>
(function () {
    const registros = @json($registrosPeriodontales);
    const form = document.getElementById('pv-form');
    if (!form) return;
    const campo = id => document.getElementById(id);
    const calcular = () => campo('pv-insercion').value = Number(campo('pv-profundidad').value || 0) + Number(campo('pv-recesion').value || 0);
    let guardando = null;
    ['pv-profundidad','pv-recesion'].forEach(id => campo(id).addEventListener('input', calcular));
    form.addEventListener('input', () => form.dataset.modificado = '1');
    form.addEventListener('change', () => form.dataset.modificado = '1');
    document.querySelectorAll('#periodonto-veterinario .pv-tooth').forEach(button => button.addEventListener('click', async function () {
        if (guardando || campo('pv-pieza').value === this.dataset.pieza) return;
        if (form.dataset.modificado === '1') { try { await guardar(); } catch (_) { return; } }
        document.querySelectorAll('#periodonto-veterinario .pv-tooth').forEach(item => item.classList.remove('active'));
        this.classList.add('active');
        const pieza = String(this.dataset.pieza), dato = registros[pieza] || {};
        campo('pv-pieza').value = pieza;
        campo('pv-pieza-label').textContent = pieza;
        campo('pv-profundidad').value = dato.profundidad ?? 0;
        campo('pv-recesion').value = dato.recesion ?? 0;
        campo('pv-movilidad').value = dato.movilidad ?? 0;
        campo('pv-furcacion').value = dato.furcacion ?? 0;
        campo('pv-sangrado').checked = Number(dato.sangrado || 0) === 1;
        campo('pv-placa').checked = Number(dato.placa || 0) === 1;
        campo('pv-observaciones').value = dato.observaciones || '';
        campo('pv-guardar').disabled = false;
        form.dataset.modificado = '0'; calcular();
    }));
    function guardar() {
        if (guardando) return guardando;
        if (!campo('pv-pieza').value || !form.reportValidity()) return Promise.reject(new Error('Seleccione una pieza y revise los datos periodontales.'));
        const datos = new FormData(form), pieza = campo('pv-pieza').value;
        const valores = Object.fromEntries(datos.entries());
        valores.sangrado = campo('pv-sangrado').checked ? 1 : 0;
        valores.placa = campo('pv-placa').checked ? 1 : 0;
        Array.from(form.elements).forEach(control => control.disabled = true);
        guardando = (async function () {
            try {
                const response = await fetch(@json(route('veterinaria.odontologia.periodonto.guardar')), {method:'POST', headers:{Accept:'application/json','X-Requested-With':'XMLHttpRequest'},body:datos});
                const data = await response.json();
                if (!response.ok || Number(data.estado) !== 1) throw new Error(Object.values(data.errors || {}).flat().join('\n') || data.message || 'No fue posible guardar.');
                registros[pieza] = valores; form.dataset.modificado = '0';
                document.querySelector('#periodonto-veterinario .pv-tooth[data-pieza="'+pieza+'"]').classList.add('has-data');
                return data;
            } catch (error) {
                const aviso = document.getElementById('vet-dental-error');
                if (aviso) {aviso.textContent=error.message;aviso.hidden=false;} else alert(error.message);
                throw error;
            } finally { Array.from(form.elements).forEach(control => control.disabled = false); }
        })();
        window.odontologiaVetPendientes ||= new Set();
        window.odontologiaVetPendientes.add(guardando);
        const actual=guardando;
        actual.then(() => {window.odontologiaVetPendientes.delete(actual);guardando=null;}, () => {window.odontologiaVetPendientes.delete(actual);guardando=null;});
        return actual;
    }
    window.guardarPeriodontoVeterinario = guardar;
    form.addEventListener('submit', event => {event.preventDefault();guardar().catch(() => {});});
})();
</script>
