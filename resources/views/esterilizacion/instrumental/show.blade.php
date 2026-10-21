@extends('layouts.app')

@section('title', 'Conjunto de instrumental')

@section('content')
<h1>{{ $conjunto->nombre }}</h1>
<div class="title-meta-row instrumental-detail-header">
    <a href="{{ route('esterilizacion.instrumental.index') }}" class="btn" title="Volver a conjuntos" aria-label="Volver a conjuntos"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg></a>
</div>

<section class="panel instrumental-panel instrumental-edit">
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
        <button type="submit" class="btn {{ $conjunto->activo ? 'red' : 'primary' }}">{{ $conjunto->activo ? 'Desactivar conjunto' : 'Activar conjunto' }}</button>
    </form>
</section>

<section class="panel instrumental-panel instrumental-add">
    <h2>Añadir pieza al conjunto</h2>
    <form method="POST" action="{{ route('esterilizacion.instrumental.agregar', $conjunto) }}" class="grid grid-2-2-1-auto">
        @csrf
        <div>
            <label for="nuevo_instrumento">Nuevo instrumento</label>
            <input id="nuevo_instrumento" type="text" name="nuevo_instrumento" value="{{ old('nuevo_instrumento') }}" placeholder="Nombre del instrumento" required>
            <small class="muted">Si ya existe en el catálogo, escribe el mismo nombre y código para reutilizarlo. No se puede agregar dos veces al mismo material.</small>
        </div>
        <div>
            <label for="nuevo_codigo">Código (opcional)</label>
            <input id="nuevo_codigo" type="text" name="nuevo_codigo" value="{{ old('nuevo_codigo') }}" placeholder="Ej. 15-0121">
        </div>
        <div>
            <label for="cantidad_predeterminada">Cantidad</label>
            <input id="cantidad_predeterminada" type="number" name="cantidad_predeterminada" min="1" step="1" value="1" required>
        </div>
        <div class="field-actions-end">
            <button type="submit" class="btn primary">Añadir</button>
        </div>
    </form>
</section>

<section class="panel instrumental-panel instrumental-pieces">
    <div class="title-meta-row instrumental-pieces-heading"><h2 class="no-margin">Piezas del conjunto</h2><div class="instrumental-totals-inline"><span><strong>Total de tipos de piezas:</strong> {{ $totalTiposPiezas }}</span><span><strong>Cantidad total de instrumentos:</strong> {{ $cantidadTotalInstrumentos }}</span></div></div>
    <table>
        <thead>
            <tr>
                <th class="col-150">Cantidad</th>
                <th>Instrumento</th>
                <th>Código</th>
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
                    <td>{{ $instrumento->codigo ?? 'â€”' }}</td>
                    <td>
                        <form method="POST" action="{{ route('esterilizacion.instrumental.quitar-pieza', [$conjunto, $instrumento]) }}" class="inline-form form-eliminar">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn sm red" title="Quitar del conjunto" aria-label="Quitar del conjunto"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg></button>
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
                <tr><td colspan="4" class="muted">Este conjunto no tiene piezas. Añade la primera.</td></tr>
            @endforelse
        </tbody>
    </table>
</section>

@endsection

