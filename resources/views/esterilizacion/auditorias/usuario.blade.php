@extends('layouts.app')

@section('title', 'Estadisticas de '.$usuarioDetalle->nombre_completo)

@section('content')
<h1>Estadisticas de {{ $usuarioDetalle->nombre_completo }}</h1>

<div class="detail-actions">
    <a href="{{ route('esterilizacion.auditorias.index') }}" class="btn">Volver al resumen</a>
    <span class="muted">{{ $usuarioDetalle->email }} · {{ $usuarioDetalle->rol ?? 'Usuario' }}</span>
</div>

<section class="panel">
    <h2>Resumen del usuario</h2>
    <div class="summary">
        <div>
            <span>Procesos iniciados</span>
            <strong>{{ number_format($resumen['procesos_iniciados']) }}</strong>
        </div>
        <div>
            <span>Recepciones registradas</span>
            <strong>{{ number_format($resumen['recepciones']) }}</strong>
        </div>
        <div>
            <span>Entregas registradas</span>
            <strong>{{ number_format($resumen['entregas']) }}</strong>
        </div>
        <div>
            <span>Registros Area Azul</span>
            <strong>{{ number_format($resumen['area_azul']) }}</strong>
        </div>
        <div>
            <span>Registros Area Verde</span>
            <strong>{{ number_format($resumen['area_verde']) }}</strong>
        </div>
        <div>
            <span>Piezas recibidas</span>
            <strong>{{ number_format($resumen['piezas_recibidas']) }}</strong>
        </div>
    </div>
</section>

<section class="panel">
    <h2>Filtros</h2>
    <form id="filtros-auditorias" method="GET" action="{{ route('esterilizacion.auditorias.usuarios.show', $usuarioDetalle) }}" class="grid filters-grid">
        <input type="hidden" name="usuario_id" value="{{ $usuarioDetalle->id }}">
        <div>
            <label for="periodo">Periodo</label>
            <select id="periodo" name="periodo">
                <option value="">Todo el historial</option>
                <option value="dia" @selected($filtros['periodo'] === 'dia')>Hoy</option>
                <option value="semana" @selected($filtros['periodo'] === 'semana')>Semana actual</option>
                <option value="mes" @selected($filtros['periodo'] === 'mes')>Mes actual</option>
            </select>
        </div>
        <div>
            <label for="fecha_desde">Desde</label>
            <input id="fecha_desde" type="date" name="fecha_desde" value="{{ $filtros['fecha_desde'] }}">
        </div>
        <div>
            <label for="fecha_hasta">Hasta</label>
            <input id="fecha_hasta" type="date" name="fecha_hasta" value="{{ $filtros['fecha_hasta'] }}">
        </div>
        <div>
            <label for="accion">Tipo de operacion</label>
            <select id="accion" name="accion">
                <option value="">Todas</option>
                @foreach($acciones as $accion)
                    <option value="{{ $accion }}" @selected($filtros['accion'] === $accion)>{{ $accion }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="area">Area</label>
            <select id="area" name="area">
                <option value="">Todas</option>
                @foreach($areas as $area)
                    <option value="{{ $area }}" @selected($filtros['area'] === $area)>{{ $area }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="servicio_id">Servicio</label>
            <select id="servicio_id" name="servicio_id">
                <option value="">Todos</option>
                @foreach($servicios as $servicio)
                    <option value="{{ $servicio->id }}" @selected((int) $filtros['servicio_id'] === $servicio->id)>{{ $servicio->nombre }}</option>
                @endforeach
            </select>
        </div>
        <div class="field-actions">
            <button type="submit" class="btn primary">Filtrar</button>
            <button type="button" class="btn green" onclick="descargarAuditorias()">Descargar Excel</button>
            <a href="{{ route('esterilizacion.auditorias.usuarios.show', $usuarioDetalle) }}" class="btn">Limpiar</a>
        </div>
    </form>
    <p class="muted">Las fechas tienen prioridad sobre el periodo. La descarga Excel respeta todos los filtros de esta pagina.</p>
</section>

<section class="panel">
    <h2>Operaciones del usuario ({{ $operaciones->total() }})</h2>
    <div class="table-responsive audit-table">
        <table>
            <thead>
                <tr>
                    <th class="col-150">Fecha</th>
                    <th class="col-90">Proceso</th>
                    <th>Servicio</th>
                    <th class="col-130">Area</th>
                    <th class="col-210">Accion</th>
                    <th>Responsable</th>
                    <th>Descripcion</th>
                </tr>
            </thead>
            <tbody>
                @forelse($operaciones as $operacion)
                    <tr>
                        <td>{{ $operacion->created_at?->format('Y-m-d H:i') }}</td>
                        <td>{{ $operacion->proceso_id ? '#'.$operacion->proceso_id : '—' }}</td>
                        <td>{{ $operacion->proceso?->servicio?->nombre ?? '—' }}</td>
                        <td><span class="badge {{ $operacion->area }}">{{ $operacion->area }}</span></td>
                        <td><strong>{{ $operacion->accion }}</strong></td>
                        <td>{{ $operacion->responsable?->nombre_completo ?? '—' }}</td>
                        <td>{{ $operacion->descripcion }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="muted">No hay operaciones registradas con esos filtros.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="pagination">{{ $operaciones->links() }}</div>
</section>

<script>
function descargarAuditorias() {
    const form = document.getElementById('filtros-auditorias');
    form.action = @json(route('esterilizacion.auditorias.excel'));
    form.submit();
}
</script>
@endsection
