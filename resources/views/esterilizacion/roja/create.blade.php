@extends('layouts.app')

@section('title', 'Area Roja')

@section('content')
<h1>Iniciar proceso - Area Roja</h1>

<section class="panel">
    <form method="POST" action="{{ route('esterilizacion.roja.store') }}" class="grid">
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
            <label for="material_id">Material o instrumental</label>
            <select id="material_id" name="material_id" data-old="{{ old('material_id') }}" required>
                <option value="">Seleccione un servicio</option>
            </select>
        </div>
        <div>
            <label for="cantidad_piezas">Cantidad de piezas</label>
            <input id="cantidad_piezas" type="number" name="cantidad_piezas" min="1" value="{{ old('cantidad_piezas') }}" required>
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
            <label for="equipo_biomedico_id">Equipo biomedico</label>
            <select id="equipo_biomedico_id" name="equipo_biomedico_id" required>
                <option value="">Seleccione</option>
                @foreach($equipos as $equipo)
                    <option value="{{ $equipo->id }}" @selected(old('equipo_biomedico_id') == $equipo->id)>{{ $equipo->nombre ?? $equipo->descripcion ?? 'Equipo '.$equipo->id }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="operador_id">Operador responsable</label>
            <select id="operador_id" name="operador_id" required>
                <option value="">Seleccione</option>
                @foreach($usuarios as $usuario)
                    <option value="{{ $usuario->id }}" @selected(old('operador_id') == $usuario->id)>{{ $usuario->nombre_completo }}</option>
                @endforeach
            </select>
        </div>
        <div style="display:flex; align-items:end;">
            <button class="btn primary" type="submit">Guardar Area Roja</button>
        </div>
    </form>
</section>
@endsection

@push('scripts')
<script>
const servicio = document.getElementById('servicio_id');
const material = document.getElementById('material_id');
async function cargarMateriales() {
    material.innerHTML = '<option value="">Cargando...</option>';
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
servicio.addEventListener('change', cargarMateriales);
if (servicio.value) cargarMateriales();
</script>
@endpush
