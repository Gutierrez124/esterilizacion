@extends('layouts.app')

@section('title', 'Auditorias y estadisticas')

@section('content')
<h1>Auditorias y estadisticas</h1>

<section class="panel">
    <h2>Filtros</h2>
    <form id="filtros-auditorias" method="GET" action="{{ route('esterilizacion.auditorias.index') }}" class="grid filters-grid">
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
            <label for="usuario_id">Usuario</label>
            <select id="usuario_id" name="usuario_id">
                <option value="">Todos</option>
                @foreach($usuarios as $usuario)
                    <option value="{{ $usuario->id }}" @selected((int) $filtros['usuario_id'] === $usuario->id)>{{ $usuario->nombre_completo }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="proceso_id">Proceso</label>
            <select id="proceso_id" name="proceso_id">
                <option value="">Todos</option>
                @foreach($procesos as $proceso)
                    <option value="{{ $proceso->id }}" @selected((int) $filtros['proceso_id'] === $proceso->id)>#{{ $proceso->id }} &middot; {{ $proceso->servicio?->nombre }} &middot; {{ $proceso->material?->nombre }}</option>
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
        <div class="field-actions">
            <button type="submit" class="btn primary">Aplicar</button>
            <button type="button" class="btn green" onclick="descargarAuditorias()">Descargar Excel</button>
            <a href="{{ route('esterilizacion.auditorias.index') }}" class="btn">Limpiar</a>
        </div>
    </form>
    <p class="muted">
        Las fechas tienen prioridad sobre el periodo. Si eliges Dia, Semana o Mes dejas las fechas vacias
        y el sistema usa ese rango. La descarga Excel incluye exactamente los eventos del detalle con los
        filtros aplicados, de todos los usuarios o de uno en particular.
    </p>
</section>

<section class="panel">
    <h2>Resumen</h2>
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
    <h2>Estadisticas por usuario ({{ $estadisticas->total() }})</h2>
    <div class="table-responsive audit-table">
        <table>
            <thead>
                <tr>
                    <th class="col-210">Usuario</th>
                    <th>Procesos iniciados</th>
                    <th>Recepciones (registrador)</th>
                    <th>Recepciones (responsable)</th>
                    <th>Entregas (responsable)</th>
                    <th>Area Azul</th>
                    <th>Area Verde</th>
                    <th>Operaciones</th>
                    <th class="col-130">Detalle</th>
                </tr>
            </thead>
            <tbody>
                @forelse($estadisticas as $fila)
                    <tr>
                        <td><strong>{{ $fila['usuario'] }}</strong></td>
                        <td>{{ number_format($fila['procesos_iniciados']) }}</td>
                        <td>{{ number_format($fila['recepciones_registradas']) }}</td>
                        <td>{{ number_format($fila['recepciones_responsable']) }}</td>
                        <td>{{ number_format($fila['entregas_responsable']) }}</td>
                        <td>{{ number_format($fila['area_azul']) }}</td>
                        <td>{{ number_format($fila['area_verde']) }}</td>
                        <td>{{ number_format($fila['total_operaciones']) }}</td>
                        <td>
                            <a class="btn sm primary" href="{{ route('esterilizacion.auditorias.usuarios.show', $fila['usuario_id']) }}">Ver operaciones</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="muted">No hay actividad registrada con esos filtros.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="pagination">{{ $estadisticas->links() }}</div>
</section>

<script>
function descargarAuditorias() {
    const form = document.getElementById('filtros-auditorias');
    form.action = @json(route('esterilizacion.auditorias.excel'));
    form.submit();
}
</script>
@endsection
