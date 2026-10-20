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
                <th class="col-quantity" title="Cantidad">Cant.</th>
                <th class="col-operator">Operador</th>
                <th class="col-area" title="Área actual">Área</th>
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
                            <a class="btn sm tooltip" href="{{ route('esterilizacion.procesos.show', $proceso) }}" data-tooltip="Detalles" aria-label="Detalles"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg></a>
                            @if($proceso->estado_actual === 'ROJA')
                                <a class="btn sm blue tooltip" href="{{ route('esterilizacion.azul.create', $proceso) }}" data-tooltip="Área Azul" aria-label="Registrar Área Azul">RA</a>
                            @elseif($proceso->estado_actual === 'AZUL')
                                <a class="btn sm green tooltip" href="{{ route('esterilizacion.verde.create', $proceso) }}" data-tooltip="Área Verde" aria-label="Registrar Área Verde">RV</a>
                            @endif
                            <form method="POST" action="{{ route('esterilizacion.procesos.destroy', $proceso) }}" class="form-eliminar">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn sm red" title="Eliminar proceso" aria-label="Eliminar proceso"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg></button>
                            </form>
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
