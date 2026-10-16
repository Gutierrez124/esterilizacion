@extends('layouts.app')

@section('title', 'Procesos de esterilizacion')

@section('content')
    <div class="detail-actions"><h1 class="no-margin">Procesos de esterilizacion</h1><a class="btn primary" href="{{ route('esterilizacion.roja.create') }}">Nuevo proceso</a></div>

    <section class="panel">
        <form method="GET" action="{{ route('esterilizacion.procesos.index') }}" class="grid filters-grid">
            <div>
                <label for="fecha_desde">Desde</label>
                <input id="fecha_desde" type="date" name="fecha_desde" value="{{ request('fecha_desde') }}">
            </div>
            <div>
                <label for="fecha_hasta">Hasta</label>
                <input id="fecha_hasta" type="date" name="fecha_hasta" value="{{ request('fecha_hasta') }}">
            </div>
            <div>
                <label for="servicio_id">Servicio</label>
                <select id="servicio_id" name="servicio_id">
                    <option value="">Todos</option>
                    @foreach($servicios as $servicio)
                        <option value="{{ $servicio->id }}" @selected(request('servicio_id') == $servicio->id)>{{ $servicio->nombre ?? $servicio->descripcion ?? 'Servicio '.$servicio->id }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="turno">Turno</label>
                <select id="turno" name="turno">
                    <option value="">Todos</option>
                    <option value="DIURNO" @selected(request('turno') === 'DIURNO')>DIURNO</option>
                    <option value="NOCTURNO" @selected(request('turno') === 'NOCTURNO')>NOCTURNO</option>
                </select>
            </div>
            <div>
                <label for="estado_actual">Area actual</label>
                <select id="estado_actual" name="estado_actual">
                    <option value="">Todas</option>
                    @foreach($estados as $estado)
                        <option value="{{ $estado }}" @selected(request('estado_actual') === $estado)>{{ $estado }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field-actions">
                <button class="btn primary" type="submit">Filtrar</button>
                <a class="btn" href="{{ route('esterilizacion.procesos.index') }}">Limpiar</a>
            </div>
        </form>
    </section>

    @include('esterilizacion.partials.procesos-table', ['procesos' => $procesos])
@endsection
