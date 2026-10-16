@extends('layouts.app')

@section('title', 'Area Roja')

@section('content')
<h1>Iniciar proceso - Area Roja</h1>

<section class="panel">
    <form method="POST" action="{{ route('esterilizacion.roja.store') }}" class="grid" id="formRoja">
        @csrf
        <div>
            <label for="servicio_id">Servicio</label>
            <select id="servicio_id" name="servicio_id" required>
                <option value="">Seleccione</option>
                @foreach($servicios as $servicio)
                    <option value="{{ $servicio->id }}" @selected(old('servicio_id') == $servicio->id)>{{ $servicio->nombre ?? $servicio->descripcion ?? 'Servicio '.$servicio->id }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="fecha">Fecha</label>
            <input id="fecha" type="date" name="fecha" value="{{ old('fecha', now()->toDateString()) }}" required>
        </div>
        <div>
            <label for="turno">Turno</label>
            <select id="turno" name="turno" required>
                <option value="DIURNO" @selected(old('turno') === 'DIURNO')>DIURNO</option>
                <option value="NOCTURNO" @selected(old('turno') === 'NOCTURNO')>NOCTURNO</option>
            </select>
        </div>
        <div>
            <label for="hora">Hora</label>
            <input id="hora" type="time" name="hora" value="{{ old('hora') }}" required>
        </div>
        <div>
            <label for="persona_recibe_id">Persona que recibe</label>
            <select id="persona_recibe_id" name="persona_recibe_id" required>
                <option value="">Seleccione</option>
                @foreach($usuarios as $usuario)
                    <option value="{{ $usuario->id }}" @selected(old('persona_recibe_id') == $usuario->id)>{{ $usuario->nombre_completo }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="persona_entrega_id">Persona que entrega</label>
            <select id="persona_entrega_id" name="persona_entrega_id" required>
                <option value="">Seleccione</option>
                @foreach($usuarios as $usuario)
                    <option value="{{ $usuario->id }}" @selected(old('persona_entrega_id') == $usuario->id)>{{ $usuario->nombre_completo }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="material_id">Conjunto de instrumental</label>
            <select id="material_id" name="material_id" data-old="{{ old('material_id') }}" required>
                <option value="">Seleccione un servicio</option>
            </select>
        </div>
        <div>
            <label>Piezas del conjunto</label>
            <button type="button" class="btn blue" id="btnAbrirPiezas" disabled>Abrir detalle de piezas</button>
            <div class="muted mt-8" id="piezasResumen">Seleccione un conjunto para ver sus piezas.</div>
        </div>
        <div>
            <label for="tipo_lavado">Tipo de lavado</label>
            <select id="tipo_lavado" name="tipo_lavado" required>
                @foreach(['ULTRASONICA','MANUAL','LAVADORA_DESCONTAMINADORA','NO_CORRESPONDE'] as $tipo)
                    <option value="{{ $tipo }}" @selected(old('tipo_lavado') === $tipo)>{{ $tipo }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="equipo_biomedico">Equipo biomedico</label>
            <select id="equipo_biomedico" name="equipo_biomedico" required>
                @foreach(['AUTOCLAVE'] as $equipo)
                    <option value="{{ $equipo }}" @selected(old('equipo_biomedico') === $equipo)>{{ $equipo }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="operador_id">Operador responsable (efectua el lavado)</label>
            <select id="operador_id" name="operador_id" required>
                <option value="">Seleccione</option>
                @foreach($usuarios as $usuario)
                    <option value="{{ $usuario->id }}" @selected(old('operador_id') == $usuario->id)>{{ $usuario->nombre_completo }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="totalVisible">Total de piezas (calculado)</label>
            <input id="totalVisible" type="number" value="0" readonly>
        </div>
        <div class="field-actions-end">
            <button class="btn red" type="submit">Guardar Area Roja</button>
        </div>
    </form>
</section>

<div id="modalPiezas" class="modal">
    <div class="panel modal-box">
        <div class="modal-header">
            <h2 class="no-margin">Piezas de <span id="modalConjuntoNombre"></span></h2>
            <button type="button" class="btn sm" id="btnCerrarModal" aria-label="Cerrar">✕ Cerrar</button>
        </div>
        <p class="muted modal-intro">Edita la cantidad real recibida de cada pieza. El total se recalcula automaticamente.</p>
        <div class="modal-table">
        <table>
            <thead>
                <tr>
                    <th class="col-90">Cantidad real</th>
                    <th>Instrumento</th>
                    <th>Codigo</th>
                    <th class="col-110">Predeterminada</th>
                </tr>
            </thead>
            <tbody id="modalBody">
                <tr><td colspan="4" class="muted">Cargando piezas...</td></tr>
            </tbody>
            <tfoot>
                <tr><th colspan="4" class="text-right">Total real: <span id="modalTotal">0</span></th></tr>
            </tfoot>
        </table>
        </div>
        <div class="mt-16 text-right">
            <button type="button" class="btn primary sm" id="btnConfirmarPiezas">Confirmar cantidades</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const servicio = document.getElementById('servicio_id');
const material = document.getElementById('material_id');
const btnAbrir = document.getElementById('btnAbrirPiezas');
const resumen = document.getElementById('piezasResumen');
const totalVisible = document.getElementById('totalVisible');
const modal = document.getElementById('modalPiezas');
const modalBody = document.getElementById('modalBody');
const modalTotal = document.getElementById('modalTotal');
const modalNombre = document.getElementById('modalConjuntoNombre');
let piezasActuales = [];

async function cargarMateriales() {
    material.innerHTML = '<option value="">Cargando...</option>';
    btnAbrir.disabled = true;
    piezasActuales = [];
    totalVisible.value = 0;
    resumen.textContent = 'Seleccione un conjunto para ver sus piezas.';
    if (!servicio.value) {
        material.innerHTML = '<option value="">Seleccione un servicio</option>';
        return;
    }
    const response = await fetch(`{{ url('/esterilizacion/servicios') }}/${servicio.value}/materiales`);
    const materiales = await response.json();
    const oldValue = material.dataset.old;
    material.innerHTML = '<option value="">Seleccione</option>';
    materiales.forEach(item => {
        const option = document.createElement('option');
        option.value = item.id;
        option.textContent = item.nombre;
        option.selected = oldValue && oldValue == item.id;
        material.appendChild(option);
    });
}

function abrirModal() {
    modal.classList.add('is-open');
    document.body.classList.add('modal-abierto');
}

function cerrarModal() {
    modal.classList.remove('is-open');
    document.body.classList.remove('modal-abierto');
}

document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && modal.classList.contains('is-open')) cerrarModal();
});

async function abrirModalPiezas() {
    if (!material.value) return;
    modalNombre.textContent = material.options[material.selectedIndex].text;
    modalBody.innerHTML = '<tr><td colspan="4" class="muted">Cargando piezas...</td></tr>';
    abrirModal();
    const response = await fetch(`{{ url('/esterilizacion/conjuntos') }}/${material.value}/piezas`);
    const data = await response.json();
    const previas = {};
    piezasActuales.forEach(p => { previas[p.instrumento_id] = p.cantidad_real; });
    piezasActuales = data.piezas.map(p => ({
        instrumento_id: p.instrumento_id,
        nombre: p.nombre,
        codigo: p.codigo,
        cantidad_predeterminada: p.cantidad_predeterminada,
        cantidad_real: previas[p.instrumento_id] !== undefined ? previas[p.instrumento_id] : p.cantidad_predeterminada,
    }));
    modalBody.innerHTML = '';
    piezasActuales.forEach((pieza, idx) => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td><input type="number" min="0" step="1" value="${pieza.cantidad_real}" data-idx="${idx}" class="inpCant"></td>
            <td>${pieza.nombre}</td>
            <td>${pieza.codigo ?? '<span class="muted">sin codigo</span>'}</td>
            <td>${pieza.cantidad_predeterminada}</td>`;
        modalBody.appendChild(tr);
    });
    document.querySelectorAll('.inpCant').forEach(inp => {
        inp.addEventListener('input', () => {
            const idx = inp.dataset.idx;
            let val = parseInt(inp.value, 10);
            if (isNaN(val) || val < 0) val = 0;
            piezasActuales[idx].cantidad_real = val;
            modalTotal.textContent = piezasActuales.reduce((s, p) => s + p.cantidad_real, 0);
        });
    });
    modalTotal.textContent = piezasActuales.reduce((s, p) => s + p.cantidad_real, 0);
}

function confirmarPiezas() {
    const total = piezasActuales.reduce((s, p) => s + p.cantidad_real, 0);
    totalVisible.value = total;
    resumen.textContent = `${piezasActuales.length} piezas confirmadas. Total real: ${total}.`;
    cerrarModal();
}

servicio.addEventListener('change', cargarMateriales);
material.addEventListener('change', () => { btnAbrir.disabled = !material.value; });
btnAbrir.addEventListener('click', abrirModalPiezas);
document.getElementById('btnCerrarModal').addEventListener('click', cerrarModal);
document.getElementById('btnConfirmarPiezas').addEventListener('click', confirmarPiezas);
modal.addEventListener('click', (e) => { if (e.target === modal) cerrarModal(); });
if (servicio.value) cargarMateriales();

document.getElementById('formRoja').addEventListener('submit', function (e) {
    if (!piezasActuales.length) {
        e.preventDefault();
        alert('Debes abrir y confirmar las piezas del conjunto antes de guardar.');
        return;
    }
    this.querySelectorAll('input[name^="piezas"]').forEach(n => n.remove());
    piezasActuales.forEach(pieza => {
        const idInp = document.createElement('input');
        idInp.type = 'hidden';
        idInp.name = 'piezas[' + pieza.instrumento_id + '][instrumento_id]';
        idInp.value = pieza.instrumento_id;
        this.appendChild(idInp);
        const cantInp = document.createElement('input');
        cantInp.type = 'hidden';
        cantInp.name = 'piezas[' + pieza.instrumento_id + '][cantidad_real]';
        cantInp.value = pieza.cantidad_real;
        this.appendChild(cantInp);
    });
});
</script>
@endpush

