@php
    $servicioNombre = fn($servicio) => $servicio?->nombre ?? $servicio?->descripcion ?? 'Servicio '.$servicio?->getKey();
    $materialNombre = fn($material) => $material?->nombre ?? $material?->descripcion ?? 'Material '.$material?->getKey();
    $usuarioNombre = fn($usuario) => $usuario?->nombre_completo ?? $usuario?->nombre ?? trim(($usuario?->nombres ?? '').' '.($usuario?->apellidos ?? '')) ?: 'Usuario '.$usuario?->getKey();
@endphp
<div class="table-responsive process-table">
    <table>
        <thead>
            <tr>
                <th class="col-id">ID</th>
                <th class="col-date">Fecha</th>
                <th class="col-shift">Turno</th>
                <th class="col-service">Servicio</th>
                <th class="col-material">Material</th>
                <th class="col-quantity">Cantidad</th>
                <th class="col-operator">Operador</th>
                <th class="col-area">Area actual</th>
                <th class="col-actions">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($procesos as $proceso)
                <tr>
                    <td class="col-id">{{ $proceso->getKey() }}</td>
                    <td class="col-date">{{ optional($proceso->fecha)->format('d/m/Y') ?? $proceso->fecha }}</td>
                    <td class="col-shift">{{ $proceso->turno }}</td>
                    <td class="col-service">{{ $servicioNombre($proceso->servicio) }}</td>
                    <td class="col-material">{{ $materialNombre($proceso->material) }}</td>
                    <td class="col-quantity">{{ $proceso->cantidad_piezas }}</td>
                    <td class="col-operator">{{ $usuarioNombre($proceso->operador) }}</td>
                    <td class="col-area"><span class="badge {{ $proceso->estado_actual }}">{{ $proceso->estado_actual }}</span></td>
                    <td class="col-actions">
                        <div class="actions process-actions">
                            <a class="btn sm" href="{{ route('esterilizacion.procesos.show', $proceso) }}">Detalle</a>
                            @if($proceso->estado_actual === 'ROJA')
                                <a class="btn sm blue" href="{{ route('esterilizacion.azul.create', $proceso) }}">Registrar Azul</a>
                            @elseif($proceso->estado_actual === 'AZUL')
                                <a class="btn sm green" href="{{ route('esterilizacion.verde.create', $proceso) }}">Registrar Verde</a>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="9" class="muted">No hay procesos para mostrar.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="pagination">{{ $procesos->links() }}</div>
