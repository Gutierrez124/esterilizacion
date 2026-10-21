@extends('layouts.app')

@section('title', 'Auditorías')

@section('content')
<div class="detail-actions audit-page-header">
    <div class="title-with-back">
        <a class="btn" href="{{ route('esterilizacion.perfil.show') }}" title="Volver a mi perfil" aria-label="Volver a mi perfil"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg></a>
    </div>
    <a class="btn primary" href="{{ route('esterilizacion.auditorias.excel', request()->query()) }}">Descargar Excel</a>
</div>

<p class="muted">
    Historial personal: tus inicios y cierres de sesión y cada acción que realizaste.
    Filtra por acción, área o fecha.
</p>

<section class="panel audit-filters">
    <form method="GET" action="{{ route('esterilizacion.auditorias.index') }}" class="grid filters-grid">
        <div>
            <label for="filtro_accion">Acción</label>
            <select id="filtro_accion" name="accion">
                <option value="">Todas</option>
                @foreach($acciones as $accion)
                    <option value="{{ $accion }}" @selected($filtros['accion'] === $accion)>{{ $accion === 'INSTRUMENTAL_ELIMINADO_DEL_CONJUNTO' ? 'PIEZA ELIMINADA DEL CONJUNTO' : $accion }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="filtro_area">Área</label>
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

<p class="muted audit-count">{{ $auditorias->total() }} registro(s) encontrado(s).</p>

<div class="table-responsive audit-table audit-results-table">
    <table>
        <thead>
            <tr>
                <th>Fecha y hora</th>
                <th>Usuario</th>
                <th>Acción</th>
                <th>Área</th>
                <th>Responsable</th>
                <th>Proceso</th>
                <th>Descripción</th>
                <th>Detalle</th>
            </tr>
        </thead>
        <tbody>
            @forelse($auditorias as $auditoria)
                <tr>
                    <td>{{ $auditoria->created_at?->format('d/m/Y H:i') ?? '—' }}</td>
                    <td>{{ $auditoria->usuario?->nombre_completo ?? 'Sistema' }}</td>
                    <td>{{ $auditoria->accion_etiqueta }}</td>
                    <td>@if($auditoria->area_etiqueta === '—')—@else<span class="badge {{ $auditoria->area }}">{{ $auditoria->area_etiqueta }}</span>@endif</td>
                    <td>{{ $auditoria->responsable_nombre }}</td>
                    <td>{{ $auditoria->proceso_id ? '#'.$auditoria->proceso_id : '—' }}</td>
                    <td>{{ $auditoria->descripcion }}</td>
                    <td class="muted">{{ $detalles[$auditoria->getKey()] ?? '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="8" class="muted">No hay auditorías registradas con los filtros seleccionados.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="pagination audit-pagination">{{ $auditorias->links('vendor.pagination.auditorias') }}</div>
@endsection
