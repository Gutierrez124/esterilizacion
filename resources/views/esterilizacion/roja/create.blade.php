@extends('layouts.app')

@section('title', 'Área Roja')

@section('content')
<div class="detail-actions area-page-header area-roja-header">
    <div class="title-with-back">
        <a class="btn" href="{{ route('esterilizacion.procesos.index') }}" title="Volver al listado" aria-label="Volver al listado"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg></a>
        <h1 class="no-margin">Iniciar proceso - Área Roja</h1>
    </div>
</div>

<section class="panel area-form-card">
    <form method="POST" action="{{ route('esterilizacion.roja.store') }}" class="grid area-form area-roja-form" id="formRoja">
        @csrf
        <div>
            <label for="servicio_id">Servicio</label>
            <select id="servicio_id" required>
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
            <select id="material_id" required>
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
            <label for="equipo_biomedico">Equipo biomédico</label>
            <select id="equipo_biomedico" name="equipo_biomedico" required>
                <option value="">Seleccione</option>
                @foreach(\App\Models\ProcesoEsterilizacion::EQUIPOS_BIOMEDICOS_ROJA as $valor => $etiqueta)
                    <option value="{{ $valor }}" @selected(old('equipo_biomedico') === $valor)>{{ $etiqueta }}</option>
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
            <label>Total de piezas (calculado)</label>
            <input id="totalVisible" type="number" value="0" readonly>
        </div>
        <div class="field-actions-end">
            <button class="btn red" type="submit">Guardar Área Roja</button>
        </div>
    </form>
</section>
<section class="panel area-form-card"><h2 class="no-margin">Materiales confirmados</h2><div id="materialesConfirmados" class="mt-16"><p class="muted">Aún no hay materiales confirmados.</p></div></section>

<div id="modalPiezas" class="modal">
    <div class="panel modal-box modal-area-form">
        <div class="modal-header">
            <h2 class="no-margin">Piezas de <span id="modalConjuntoNombre"></span></h2>
            <button type="button" class="btn sm" id="btnCerrarModal" aria-label="Cerrar">×</button>
        </div>
        <p class="muted modal-intro modal-help">Edita la cantidad real recibida de cada pieza. El total se recalcula automáticamente.</p>
        <div class="modal-table">
        <table class="pieces-table">
            <thead>
                <tr>
                    <th class="col-90">Cantidad real</th>
                    <th>Instrumento</th>
                    <th>Código</th>
                    <th class="col-110">Predeterminada</th>
                </tr>
            </thead>
            <tbody id="modalBody">
                <tr><td colspan="4" class="muted">Cargando piezas...</td></tr>
            </tbody>
        </table>
        </div>
        <div class="modal-footer-actions">
            <div class="pieces-total">Total real <span id="modalTotal">0</span></div>
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
const listaConfirmados = document.getElementById('materialesConfirmados');
let piezasActuales = [];
let materialAbierto = null;
let confirmados = [];

async function cargarMateriales() {
    material.innerHTML = '<option value="">Cargando...</option>';
    btnAbrir.disabled = true;
    piezasActuales = [];
    resumen.textContent = 'Seleccione un conjunto para ver sus piezas.';
    if (!servicio.value) { material.innerHTML = '<option value="">Seleccione un servicio</option>'; return; }
    const response = await fetch(`{{ url('/esterilizacion/servicios') }}/${servicio.value}/materiales`);
    const materiales = await response.json();
    material.innerHTML = '<option value="">Seleccione</option>';
    materiales.forEach(item => { const option=document.createElement('option'); option.value=item.id; option.textContent=item.nombre; material.appendChild(option); });
}
function abrirModal() { modal.classList.add('is-open'); document.body.classList.add('modal-abierto'); }
function cerrarModal() { modal.classList.remove('is-open'); document.body.classList.remove('modal-abierto'); }
document.addEventListener('keydown', e => { if(e.key==='Escape' && modal.classList.contains('is-open')) cerrarModal(); });
async function abrirModalPiezas() {
    if (!material.value) return;
    materialAbierto = { servicio_id: Number(servicio.value), servicio_nombre: servicio.options[servicio.selectedIndex].text, material_id: Number(material.value), material_nombre: material.options[material.selectedIndex].text };
    modalNombre.textContent = materialAbierto.material_nombre;
    modalBody.innerHTML = '<tr><td colspan="4" class="muted">Cargando piezas...</td></tr>';
    abrirModal();
    const response = await fetch(`{{ url('/esterilizacion/conjuntos') }}/${material.value}/piezas`);
    const data = await response.json();
    const previas = {};
    const grupoPrevio = confirmados.find(g => g.material_id === materialAbierto.material_id);
    (grupoPrevio?.piezas || []).forEach(p => previas[p.instrumento_id]=p.cantidad_real);
    piezasActuales = data.piezas.map(p => ({ instrumento_id:p.instrumento_id, nombre:p.nombre, codigo:p.codigo, cantidad_predeterminada:p.cantidad_predeterminada, cantidad_real:previas[p.instrumento_id] ?? p.cantidad_predeterminada }));
    modalBody.innerHTML='';
    piezasActuales.forEach((pieza,idx) => { const tr=document.createElement('tr'); tr.innerHTML=`<td><input type="number" min="0" step="1" value="${pieza.cantidad_real}" data-idx="${idx}" class="inpCant"></td><td>${pieza.nombre}</td><td>${pieza.codigo ?? '<span class="muted">sin codigo</span>'}</td><td>${pieza.cantidad_predeterminada}</td>`; modalBody.appendChild(tr); });
    document.querySelectorAll('.inpCant').forEach(inp => inp.addEventListener('input', () => { let v=parseInt(inp.value,10); if(isNaN(v)||v<0)v=0; piezasActuales[inp.dataset.idx].cantidad_real=v; modalTotal.textContent=piezasActuales.reduce((sum,p)=>sum+p.cantidad_real,0); }));
    modalTotal.textContent=piezasActuales.reduce((sum,p)=>sum+p.cantidad_real,0);
}
function renderConfirmados() {
    if (!confirmados.length) { listaConfirmados.innerHTML='<p class="muted">Aún no hay materiales confirmados.</p>'; }
    else { listaConfirmados.innerHTML=''; confirmados.forEach((g,i)=>{ const section=document.createElement('div'); section.className='panel'; const header=document.createElement('div'); header.className='detail-actions'; header.innerHTML=`<strong>${g.servicio_nombre} · ${g.material_nombre}</strong><button type="button" class="btn red sm" data-quitar="${i}">Quitar</button>`; section.appendChild(header); const table=document.createElement('table'); table.innerHTML='<thead><tr><th>Instrumento</th><th>Código</th><th>Predeterminada</th><th>Real</th></tr></thead>'; const body=document.createElement('tbody'); g.piezas.forEach(p=>{const row=document.createElement('tr'); row.innerHTML=`<td>${p.nombre}</td><td>${p.codigo ?? '—'}</td><td>${p.cantidad_predeterminada}</td><td>${p.cantidad_real}</td>`; body.appendChild(row);}); table.appendChild(body); section.appendChild(table); listaConfirmados.appendChild(section); }); }
    listaConfirmados.querySelectorAll('[data-quitar]').forEach(b=>b.addEventListener('click',()=>{confirmados.splice(Number(b.dataset.quitar),1); renderConfirmados(); actualizarTotal();}));
}
function actualizarTotal(){ totalVisible.value=confirmados.reduce((sum,g)=>sum+g.piezas.reduce((s,p)=>s+p.cantidad_real,0),0); }
function confirmarPiezas() {
    if (!materialAbierto || !piezasActuales.length) { mostrarToast('Este material no tiene instrumentos para confirmar.', 'warning'); return; }
    const grupo={...materialAbierto,piezas:piezasActuales.map(p=>({...p}))};
    const idx=confirmados.findIndex(g=>g.material_id===grupo.material_id);
    if(idx>=0)confirmados[idx]=grupo;else confirmados.push(grupo);
    renderConfirmados();
    actualizarTotal();
    resumen.textContent=`${grupo.material_nombre} confirmado: ${grupo.piezas.length} instrumentos.`;
    cerrarModal();
}
servicio.addEventListener('change', cargarMateriales);
material.addEventListener('change',()=>{btnAbrir.disabled=!material.value; piezasActuales=[]; materialAbierto=null;});
btnAbrir.addEventListener('click',abrirModalPiezas);

document.getElementById('btnCerrarModal').addEventListener('click',cerrarModal);
document.getElementById('btnConfirmarPiezas').addEventListener('click',confirmarPiezas);
modal.addEventListener('click',e=>{if(e.target===modal)cerrarModal();});
document.getElementById('formRoja').addEventListener('submit',function(e){
    if(!confirmados.length){e.preventDefault();mostrarToast('Debes confirmar al menos un material y sus piezas.','warning');return;}
    this.querySelectorAll('input[name^="materiales_confirmados"]').forEach(n=>n.remove());
    confirmados.forEach((g,gi)=>{
        const add=(name,value)=>{const inp=document.createElement('input');inp.type='hidden';inp.name=name;inp.value=value;this.appendChild(inp);};
        add(`materiales_confirmados[${gi}][servicio_id]`,g.servicio_id); add(`materiales_confirmados[${gi}][material_id]`,g.material_id);
        g.piezas.forEach((p,pi)=>{add(`materiales_confirmados[${gi}][piezas][${pi}][instrumento_id]`,p.instrumento_id);add(`materiales_confirmados[${gi}][piezas][${pi}][cantidad_real]`,p.cantidad_real);});
    });
});
</script>
@endpush

