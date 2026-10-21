@extends('layouts.app')

@section('title', 'Instrumentos por material')

@section('content')


<section class="panel instrumental-panel instrumental-filters">
    <h2>Filtros y búsqueda</h2>
    <form method="GET" action="{{ route('esterilizacion.instrumental.index') }}" class="grid filters-grid">
        <div>
            <label for="buscar">Buscar por nombre</label>
            <input id="buscar" type="text" name="buscar" value="{{ request('buscar') }}" placeholder="Nombre del conjunto">
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
        <div class="field-actions">
            <button type="submit" class="btn primary">Buscar</button>
            <a href="{{ route('esterilizacion.instrumental.index') }}" class="btn">Limpiar</a>
        </div>
    </form>
</section>

<section class="panel instrumental-panel instrumental-create">
    <h2>Crear nuevo conjunto</h2>
    <form method="POST" action="{{ route('esterilizacion.instrumental.store') }}" class="grid grid-1-1-auto">
        @csrf
        <div>
            <label for="nuevo_nombre">Nombre del conjunto</label>
            <input id="nuevo_nombre" type="text" name="nombre" value="{{ old('nombre') }}" placeholder="Ej. CIRUGIA MENOR I" required>
        </div>
        <div>
            <label for="nuevo_servicio">Servicio</label>
            <select id="nuevo_servicio" name="servicio_id" required>
                <option value="">Seleccione un servicio</option>
                @foreach($servicios as $servicio)
                    <option value="{{ $servicio->id }}" @selected(old('servicio_id') == $servicio->id)>{{ $servicio->nombre ?? $servicio->descripcion ?? 'Servicio '.$servicio->id }}</option>
                @endforeach
            </select>
        </div>
        <div class="field-actions-end">
            <button type="submit" class="btn primary">Crear conjunto</button>
        </div>
    </form>
</section>

<section class="panel instrumental-panel instrumental-list">
    <h2>Conjuntos de instrumental ({{ $conjuntos->total() }})</h2>
    <div class="table-responsive instrumental-table">
        <table>
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Servicio</th>
                    <th class="col-110">Cantidad total de instrumentos</th>
                    <th class="col-110">Estado</th>
                    <th class="col-120">Acción</th>
                </tr>
            </thead>
            <tbody>
                @forelse($conjuntos as $conjunto)
                    <tr>
                        <td><strong>{{ $conjunto->nombre }}</strong></td>
                        <td>{{ $conjunto->servicio?->nombre ?? $conjunto->servicio?->descripcion ?? 'Sin servicio' }}</td>
                        <td>{{ $conjunto->cantidad_total_instrumentos ?? 0 }}</td>
                        <td><span class="badge {{ $conjunto->activo ? 'VERDE' : 'SISTEMA' }}">{{ $conjunto->activo ? 'Activo' : 'Inactivo' }}</span></td>
                        <td><a href="{{ route('esterilizacion.instrumental.show', $conjunto) }}" class="btn sm primary">Administrar</a></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="muted">No hay conjuntos que coincidan con la búsqueda.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="pagination instrumental-pagination">{{ $conjuntos->links('vendor.pagination.instrumental') }}</div>
</section>
@endsection
