@php
    $servicioNombre = fn($servicio) => $servicio?->nombre ?? $servicio?->descripcion ?? 'Servicio '.$servicio?->getKey();
    $materialNombre = fn($material) => $material?->nombre ?? $material?->descripcion ?? 'Material '.$material?->getKey();
    $usuarioNombre = fn($usuario) => $usuario?->nombre_completo ?? $usuario?->nombre ?? trim(($usuario?->nombres ?? '').' '.($usuario?->apellidos ?? '')) ?: 'Usuario '.$usuario?->getKey();
@endphp
<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Fecha</th>
            <th>Turno</th>
            <th>Servicio</th>
            <th>Material</th>
            <th>Cantidad</th>
            <th>Operador</th>
            <th>Area actual</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        @forelse($procesos as $proceso)
            <tr>
                <td>{{ $proceso->getKey() }}</td>
                <td>{{ optional($proceso->fecha)->format('d/m/Y') ?? $proceso->fecha }}</td>
                <td>{{ $proceso->turno }}</td>
                <td>{{ $servicioNombre($proceso->servicio) }}</td>
                <td>{{ $materialNombre($proceso->material) }}</td>
                <td>{{ $proceso->cantidad_piezas }}</td>
                <td>{{ $usuarioNombre($proceso->operador) }}</td>
                <td><span class="badge {{ $proceso->estado_actual }}">{{ $proceso->estado_actual }}</span></td>
                <td>
                    <div class="actions">
                        <a class="btn" href="{{ route('esterilizacion.procesos.show', $proceso) }}">Detalle</a>
                        @if($proceso->estado_actual === 'ROJA')
                            <a class="btn blue" href="{{ route('esterilizacion.azul.create', $proceso) }}">Registrar Azul</a>
                        @elseif($proceso->estado_actual === 'AZUL')
                            <a class="btn green" href="{{ route('esterilizacion.verde.create', $proceso) }}">Registrar Verde</a>
                        @endif
                    </div>
                </td>
            </tr>
        @empty
            <tr><td colspan="9" class="muted">No hay procesos para mostrar.</td></tr>
        @endforelse
    </tbody>
</table>
<div class="pagination">{{ $procesos->links() }}</div>
