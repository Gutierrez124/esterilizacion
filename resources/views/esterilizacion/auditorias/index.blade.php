@extends('layouts.app')

@section('title', 'Auditorias')

@section('content')
<div class="detail-actions">
    <div class="title-with-back">
        <a class="btn" href="{{ route('esterilizacion.perfil.show') }}" title="Volver a mi perfil" aria-label="Volver a mi perfil"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg></a>
        <h1 class="no-margin">Auditorías</h1>
    </div>
    <a class="btn green" href="{{ route('esterilizacion.auditorias.excel', request()->query()) }}">Descargar Excel</a>
</div>

<p class="muted">
    Historial personal: tus inicios y cierres de sesión y cada acción que realizaste.
    Filtra por acción, área o fecha.
</p>

<section class="panel">
    <form method="GET" action="{{ route('esterilizacion.auditorias.index') }}" class="grid filters-grid">
        <div>
            <label for="filtro_accion">Accion</label>
            <select id="filtro_accion" name="accion">
                <option value="">Todas</option>
                @foreach($acciones as $accion)
                    <option value="{{ $accion }}" @selected($filtros['accion'] === $accion)>{{ $accion }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="filtro_area">Area</label>
            <select id="filtro_area" name="area">
                <option value="">Todas</option>
                @foreach($areas as $area)
                    <option value="{{ $area }}" @selected($filtros['area'] === $area)>{{ $area }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="filtro_desde">Desde</label>
            <input id="filtro_desde" type="date" name="desde" value="{{ $filtros['desde'] }}">
        </div>
        <div>
            <label for="filtro_hasta">Hasta</label>
            <input id="filtro_hasta" type="date" name="hasta" value="{{ $filtros['hasta'] }}">
        </div>
        <div class="field-actions">
            <button class="btn primary" type="submit">Filtrar</button>
            <a class="btn" href="{{ route('esterilizacion.auditorias.index') }}">Limpiar</a>
        </div>
    </form>
</section>

<p class="muted">{{ $auditorias->total() }} registro(s) encontrado(s).</p>

<div class="table-responsive audit-table">
    <table>
        <thead>
            <tr>
                <th>Fecha y hora</th>
                <th>Usuario</th>
                <th>Accion</th>
                <th>Area</th>
                <th>Responsable</th>
                <th>Proceso</th>
                <th>Descripcion</th>
                <th>Detalle</th>
            </tr>
        </thead>
        <tbody>
            @forelse($auditorias as $auditoria)
                @php
                    $datos = $auditoria->datos_adicionales;
                    $detalle = is_array($datos) && $datos !== []
                        ? collect($datos)->map(fn ($valor, $clave) => $clave.': '.(is_scalar($valor) ? (string) $valor : json_encode($valor, JSON_UNESCAPED_UNICODE)))->implode(' | ')
                        : '—';
                @endphp
                <tr>
                    <td>{{ $auditoria->created_at?->format('d/m/Y H:i') ?? '—' }}</td>
                    <td>{{ $auditoria->usuario?->nombre_completo ?? 'Sistema' }}</td>
                    <td>{{ $auditoria->accion }}</td>
                    <td><span class="badge {{ $auditoria->area }}">{{ $auditoria->area ?? '—' }}</span></td>
                    <td>{{ $auditoria->responsable?->nombre_completo ?? '—' }}</td>
                    <td>{{ $auditoria->proceso_id ? '#'.$auditoria->proceso_id : '—' }}</td>
                    <td>{{ $auditoria->descripcion }}</td>
                    <td class="muted">{{ $detalle }}</td>
                </tr>
            @empty
                <tr><td colspan="8" class="muted">No hay auditorías registradas con los filtros seleccionados.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="pagination">{{ $auditorias->links() }}</div>
@endsection
