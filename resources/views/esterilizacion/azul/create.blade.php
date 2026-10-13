@extends('layouts.app')

@section('title', 'Registrar Area Azul')

@section('content')
<h1>Registrar Area Azul - Proceso #{{ $proceso->getKey() }}</h1>

<section class="panel">
    <div class="summary">
        <div><span>Fecha</span>{{ optional($proceso->fecha)->format('d/m/Y') ?? $proceso->fecha }}</div>
        <div><span>Servicio</span>{{ $proceso->servicio?->nombre ?? $proceso->servicio?->descripcion ?? $proceso->servicio_id }}</div>
        <div><span>Material</span>{{ $proceso->material?->nombre ?? $proceso->material?->descripcion ?? $proceso->material_id }}</div>
        <div><span>Cantidad</span>{{ $proceso->cantidad_piezas }}</div>
        <div><span>Estado</span><span class="badge {{ $proceso->estado_actual }}">{{ $proceso->estado_actual }}</span></div>
    </div>
</section>

<section class="panel">
    <form method="POST" action="{{ route('esterilizacion.azul.store', $proceso) }}" class="grid">
        @csrf
        <div>
            <label for="hora">Hora</label>
            <input id="hora" type="time" name="hora" value="{{ old('hora') }}" required>
        </div>
        <div>
            <label for="empaque">Empaque</label>
            <input id="empaque" name="empaque" value="{{ old('empaque') }}" maxlength="100" required>
        </div>
        <div>
            <label for="tipo_carga">Tipo de carga</label>
            <select id="tipo_carga" name="tipo_carga" required>
                @foreach(['INSTRUMENTAL','TEXTIL','GOMA'] as $tipo)
                    <option value="{{ $tipo }}" @selected(old('tipo_carga') === $tipo)>{{ $tipo }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="numero_carga">Numero de carga</label>
            <input id="numero_carga" name="numero_carga" value="{{ old('numero_carga') }}" maxlength="50" required>
        </div>
        <div>
            <label for="ib">IB</label>
            <input id="ib" name="ib" value="{{ old('ib') }}" maxlength="50" required>
        </div>
        <div>
            <label for="operador_id">Operador</label>
            <select id="operador_id" name="operador_id" required>
                <option value="">Seleccione</option>
                @foreach($usuarios as $usuario)
                    <option value="{{ $usuario->id }}" @selected(old('operador_id') == $usuario->id)>{{ $usuario->nombre_completo }}</option>
                @endforeach
            </select>
        </div>
        <div style="display:flex; align-items:end;">
            <button class="btn blue" type="submit">Guardar Area Azul</button>
        </div>
    </form>
</section>
@endsection
