@extends('layouts.app')

@section('title', 'Registrar Area Verde')

@section('content')
<div class="detail-actions">
    <div class="title-with-back">
        <a class="btn" href="{{ route('esterilizacion.procesos.show', $proceso) }}" title="Volver al proceso" aria-label="Volver al proceso"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg></a>
        <h1 class="no-margin">Registrar Area Verde - Proceso #{{ $proceso->getKey() }}</h1>
    </div>
</div>

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
                <label for="descarga_id">Descarga</label>
                                <select id="descarga_id" name="descarga_id" required>
                    <option value="">Seleccione</option>
                    @foreach($usuarios as $usuario)
                        <option value="{{ $usuario->id }}" @selected(old('descarga_id') == $usuario->id)>{{ $usuario->nombre_completo }}</option>
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
                <textarea id="observacion" name="observacion" maxlength="1000" class="upper">{{ old('observacion') }}</textarea>
            </div>
            <div class="form-actions">
                <button class="btn green" type="submit">Guardar Area Verde</button>
            </div>
        </div>
    </section>
</form>
@endsection
