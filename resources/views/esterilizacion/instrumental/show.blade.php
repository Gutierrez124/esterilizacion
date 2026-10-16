@extends('layouts.app')

@section('title', 'Conjunto de instrumental')

@section('content')
<h1>{{ $conjunto->nombre }}</h1>
<p class="muted">Servicio: {{ $conjunto->servicio?->nombre ?? 'Sin servicio' }} · Estado:
    <span class="badge {{ $conjunto->activo ? 'VERDE' : 'SISTEMA' }}">{{ $conjunto->activo ? 'Activo' : 'Inactivo' }}</span>
</p>

<section class="panel">
    <h2>Editar conjunto</h2>
    <form method="POST" action="{{ route('esterilizacion.instrumental.update', $conjunto) }}" class="grid grid-1-1-auto">
        @csrf
        @method('PUT')
        <div>
            <label for="nombre">Nombre</label>
            <input id="nombre" type="text" name="nombre" value="{{ old('nombre', $conjunto->nombre) }}" required>
        </div>
        <div>
            <label for="servicio_id">Servicio</label>
            <select id="servicio_id" name="servicio_id" required>
                @foreach($servicios as $servicio)
                    <option value="{{ $servicio->id }}" @selected($conjunto->servicio_id == $servicio->id)>{{ $servicio->nombre ?? $servicio->descripcion ?? 'Servicio '.$servicio->id }}</option>
                @endforeach
            </select>
        </div>
        <div class="field-actions-end">
            <button type="submit" class="btn primary">Guardar</button>
        </div>
    </form>
    <form method="POST" action="{{ route('esterilizacion.instrumental.estado', $conjunto) }}" class="mt-12">
        @csrf
        @method('PUT')
        <input type="hidden" name="activo" value="{{ $conjunto->activo ? 0 : 1 }}">
        <button type="submit" class="btn {{ $conjunto->activo ? 'red' : 'green' }}">{{ $conjunto->activo ? 'Desactivar conjunto' : 'Activar conjunto' }}</button>
    </form>
</section>

<section class="panel">
    <h2>Anadir pieza al conjunto</h2>
    <form method="POST" action="{{ route('esterilizacion.instrumental.agregar', $conjunto) }}" class="grid grid-2-2-1-auto">
        @csrf
        <div>
            <label for="instrumento_id">Instrumento existente</label>
            <select id="instrumento_id" name="instrumento_id">
                <option value="">Seleccione (o cree uno nuevo)</option>
                @foreach($instrumentos as $instrumento)
                    <option value="{{ $instrumento->id }}">{{ $instrumento->nombre }}@if($instrumento->codigo) ({{ $instrumento->codigo }})@endif</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="nuevo_instrumento">Nuevo instrumento</label>
            <input id="nuevo_instrumento" type="text" name="nuevo_instrumento" value="{{ old('nuevo_instrumento') }}" placeholder="Nombre de la pieza nueva">
        </div>
        <div>
            <label for="nuevo_codigo">Codigo (opcional)</label>
            <input id="nuevo_codigo" type="text" name="nuevo_codigo" value="{{ old('nuevo_codigo') }}" placeholder="Ej. 15-0121">
        </div>
        <div>
            <label for="cantidad_predeterminada">Cantidad</label>
            <input id="cantidad_predeterminada" type="number" name="cantidad_predeterminada" min="1" step="1" value="1" required>
        </div>
        <div class="field-actions-end">
            <button type="submit" class="btn green">Anadir</button>
        </div>
    </form>
</section>

<section class="panel">
    <h2>Piezas del conjunto ({{ $conjunto->instrumentos->count() }})</h2>
    <table>
        <thead>
            <tr>
                <th class="col-150">Cantidad</th>
                <th>Instrumento</th>
                <th>Codigo</th>
                <th class="col-230">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($conjunto->instrumentos as $instrumento)
                <tr>
                    <td>
                        <form method="POST" action="{{ route('esterilizacion.instrumental.actualizar-pieza', [$conjunto, $instrumento]) }}" class="qty-form">
                            @csrf
                            @method('PUT')
                            <input type="number" name="cantidad_predeterminada" min="1" step="1" value="{{ $instrumento->pivot->cantidad_predeterminada }}" class="input-qty">
                            <button type="submit" class="btn sm primary">Guardar</button>
                        </form>
                    </td>
                    <td>{{ $instrumento->nombre }} @if(!$instrumento->activo)<span class="badge SISTEMA">Inactivo</span>@endif</td>
                    <td>{{ $instrumento->codigo ?? '—' }}</td>
                    <td>
                        <form method="POST" action="{{ route('esterilizacion.instrumental.quitar-pieza', [$conjunto, $instrumento]) }}" onsubmit="return confirm('Quitar esta pieza del conjunto? El instrumento seguira en el catalogo global.');" class="inline-form">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn sm red">Quitar del conjunto</button>
                        </form>
                        <form method="POST" action="{{ route('esterilizacion.instrumentos.estado', $instrumento) }}" class="inline-form">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="activo" value="{{ $instrumento->activo ? 0 : 1 }}">
                            <button type="submit" class="btn sm">{{ $instrumento->activo ? 'Desactivar' : 'Activar' }}</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" class="muted">Este conjunto no tiene piezas. Anade la primera.</td></tr>
            @endforelse
        </tbody>
    </table>
</section>

<a href="{{ route('esterilizacion.instrumental.index') }}" class="btn">Volver a conjuntos</a>
@endsection

