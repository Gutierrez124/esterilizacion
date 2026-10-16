@extends('layouts.app')

@section('title', 'Detalle del proceso')

@section('content')
@php
    $servicio = $proceso->servicio?->nombre ?? $proceso->servicio?->descripcion ?? 'Servicio '.$proceso->servicio_id;
    $material = $proceso->material?->nombre ?? $proceso->material?->descripcion ?? 'Material '.$proceso->material_id;
    $usuario = function ($u) {
        if (! $u) {
            return 'Pendiente';
        }

        return $u->nombre_completo
            ?? $u->nombre
            ?? trim(($u->nombres ?? '').' '.($u->apellidos ?? ''))
            ?: 'Usuario '.$u->getKey();
    };
    $fecha = fn($value) => $value ? \Illuminate\Support\Carbon::parse($value)->format('d/m/Y') : 'Pendiente';
    $hora = fn($value) => $value ? substr((string) $value, 0, 5) : 'Pendiente';
@endphp

<h1>Proceso #{{ $proceso->getKey() }}</h1>

<div class="detail-actions">
    <a class="btn" href="{{ route('esterilizacion.procesos.index') }}">Volver al listado</a>
    @if($proceso->estado_actual === 'ROJA')
        <a class="btn blue" href="{{ route('esterilizacion.azul.create', $proceso) }}">Registrar Area Azul</a>
    @elseif($proceso->estado_actual === 'AZUL')
        <a class="btn green" href="{{ route('esterilizacion.verde.create', $proceso) }}">Registrar Area Verde</a>
    @endif
</div>

<section class="panel">
    <div class="section-title service">Servicio</div>
    <div class="summary">
        <div><span>Identificador</span>{{ $proceso->getKey() }}</div>
        <div><span>Servicio</span>{{ $servicio }}</div>
        <div><span>Area actual</span><span class="badge {{ $proceso->estado_actual }}">{{ $proceso->estado_actual }}</span></div>
        <div><span>Registrado por</span>{{ $usuario($proceso->creadoPor) }}</div>

    </div>
</section>

<section class="panel">
    <div class="section-title red">Area Roja</div>
    <div class="summary">
        <div><span>Fecha</span>{{ $fecha($proceso->fecha) }}</div>
        <div><span>Turno</span>{{ $proceso->turno }}</div>
        <div><span>Hora</span>{{ $hora($proceso->hora) }}</div>
        <div><span>Recibe</span>{{ $usuario($proceso->personaRecibe) }}</div>
        <div><span>Entrega</span>{{ $usuario($proceso->personaEntrega) }}</div>
        <div><span>Material/Instrumental</span>{{ $material }}</div>
        <div><span>Cantidad de piezas</span>{{ $proceso->cantidad_piezas }}</div>
        <div><span>Tipo de lavado</span>{{ $proceso->tipo_lavado }}</div>
        <div><span>Equipo biomedico a usar</span>{{ $proceso->equipo_biomedico }}</div>
        <div><span>OP</span>{{ $usuario($proceso->operador) }}</div>
    </div>
</section>
<section class="panel">
    <div class="section-title red">Detalle de instrumental recibido</div>
    @if($proceso->instrumentos->count())
        <table>
            <thead>
                <tr>
                    <th class="col-120">Cantidad real</th>
                    <th>Instrumento</th>
                    <th>Codigo</th>
                    <th class="col-130">Predeterminada</th>
                </tr>
            </thead>
            <tbody>
                @foreach($proceso->instrumentos as $pieza)
                    <tr>
                        <td><strong>{{ $pieza->cantidad_real }}</strong></td>
                        <td>{{ $pieza->nombre_instrumento }}</td>
                        <td>{{ $pieza->codigo_instrumento ?? '—' }}</td>
                        <td>{{ $pieza->cantidad_predeterminada }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p class="muted">Este proceso no tiene detalle de instrumental registrado.</p>
    @endif
</section>

<section class="panel">
    <div class="section-title blue">Area Azul</div>
    @if($proceso->areaAzul)
        <div class="summary">
            <div><span>Hora</span>{{ $hora($proceso->areaAzul->hora) }}</div>
            <div><span>Empaque</span>{{ $proceso->areaAzul->empaque ?: 'Pendiente' }}</div>
            <div><span>Tipo de carga</span>{{ $proceso->areaAzul->tipo_carga }}</div>
            <div><span>Nro. de carga</span>{{ $proceso->areaAzul->numero_carga ?: 'Pendiente' }}</div>
            <div><span>IB</span>{{ $proceso->areaAzul->ib ?: 'Pendiente' }}</div>
            <div><span>OP</span>{{ $usuario($proceso->areaAzul->operador) }}</div>
        </div>
    @else
        <p class="muted">Area Azul pendiente de registro.</p>
    @endif
</section>

<section class="panel">
    <div class="section-title green">Area Verde</div>
    @if($proceso->areaVerde)
        <div class="summary">
            <div><span>Entrega - fecha</span>{{ $fecha($proceso->areaVerde->fecha_entrega) }}</div>
            <div><span>Entrega - hora</span>{{ $hora($proceso->areaVerde->hora_entrega) }}</div>
            <div><span>Descarga</span>{{ $usuario($proceso->areaVerde->personaDescarga) }}</div>
            <div><span>Entrega</span>{{ $usuario($proceso->areaVerde->personaEntrega) }}</div>
            <div><span>Recibe</span>{{ $usuario($proceso->areaVerde->personaRecibe) }}</div>
        </div>
    @else
        <p class="muted">Area Verde pendiente de registro.</p>
    @endif
</section>

<section class="panel">
    <div class="section-title observation">Observacion</div>
    <div class="summary">
        <div><span>Observacion final</span>{{ $proceso->areaVerde?->observacion ?: 'Sin observacion registrada' }}</div>
    </div>
</section>
@endsection
