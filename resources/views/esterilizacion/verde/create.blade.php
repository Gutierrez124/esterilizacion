@extends('layouts.app')

@section('title', 'Registrar Area Verde')

@section('content')
<h1>Registrar Area Verde - Proceso #{{ $proceso->getKey() }}</h1>

<section class="panel">
    <div class="section-title service">Proceso recibido desde Area Azul</div>
    <div class="summary">
        <div><span>Fecha</span>{{ optional($proceso->fecha)->format('d/m/Y') ?? $proceso->fecha }}</div>
        <div><span>Servicio</span>{{ $proceso->servicio?->nombre ?? $proceso->servicio?->descripcion ?? $proceso->servicio_id }}</div>
        <div><span>Material</span>{{ $proceso->material?->nombre ?? $proceso->material?->descripcion ?? $proceso->material_id }}</div>
        <div><span>Cantidad</span>{{ $proceso->cantidad_piezas }}</div>
        <div><span>Estado</span><span class="badge {{ $proceso->estado_actual }}">{{ $proceso->estado_actual }}</span></div>
    </div>
</section>

<form method="POST" action="{{ route('esterilizacion.verde.store', $proceso) }}">
    @csrf
    <section class="panel">
        <div class="section-title green">Area Verde</div>
        <div class="grid two">
            <div>
                <label for="fecha_entrega">Fecha de entrega</label>
                <input id="fecha_entrega" type="date" name="fecha_entrega" value="{{ old('fecha_entrega', now()->toDateString()) }}" required>
            </div>
            <div>
                <label for="hora_entrega">Hora de entrega</label>
                <input id="hora_entrega" type="time" name="hora_entrega" value="{{ old('hora_entrega') }}" required>
            </div>
            <div>
                <label for="hora_descarga">Hora de descarga</label>
                <input id="hora_descarga" type="time" name="hora_descarga" value="{{ old('hora_descarga') }}" required>
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
                <label for="persona_recibe_id">Persona que recibe</label>
                <select id="persona_recibe_id" name="persona_recibe_id" required>
                    <option value="">Seleccione</option>
                    @foreach($usuarios as $usuario)
                        <option value="{{ $usuario->id }}" @selected(old('persona_recibe_id') == $usuario->id)>{{ $usuario->nombre_completo }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </section>

    <section class="panel">
        <div class="section-title observation">Observacion</div>
        <div class="grid two">
            <div>
                <label for="observacion">Observacion final</label>
                <textarea id="observacion" name="observacion" maxlength="1000">{{ old('observacion') }}</textarea>
            </div>
            <div class="form-actions">
                <button class="btn green" type="submit">Guardar Area Verde</button>
            </div>
        </div>
    </section>
</form>
@endsection
