@extends('layouts.app')

@section('title', 'Registrar Area Azul')

@section('content')
<div class="detail-actions">
    <div class="title-with-back">
        <a class="btn" href="{{ route('esterilizacion.procesos.show', $proceso) }}" title="Volver al proceso" aria-label="Volver al proceso"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg></a>
        <h1 class="no-margin">Registrar Area Azul - Proceso #{{ $proceso->getKey() }}</h1>
    </div>
</div>

<section class="panel">
    <div class="summary">
        <div><span>Fecha</span>{{ optional($proceso->fecha)->format('d/m/Y') ?? $proceso->fecha }}</div>
        <div><span>Servicio</span>{{ $proceso->servicio?->nombre ?? $proceso->servicio?->descripcion ?? $proceso->servicio_id }}</div>
        <div><span>Material</span>{{ $proceso->material?->nombre ?? $proceso->material?->descripcion ?? $proceso->material_id }}</div>
        <div><span>Cantidad</span>{{ $proceso->cantidad_piezas }}</div>
        <div><span>Estado</span><span class="badge {{ $proceso->estado_actual }}">{{ $proceso->estado_actual }}</span></div>
    </div>
</section>

<section class="panel">
    <form method="POST" action="{{ route('esterilizacion.azul.store', $proceso) }}" class="grid">
        @csrf
        <div>
            <label for="hora">Hora</label>
            <input id="hora" type="time" name="hora" value="{{ old('hora') }}" required>
        </div>
        <div>
            <label for="empaque">Empaque</label>
            <select id="empaque" name="empaque" required>
                <option value="">Seleccione</option>
                @foreach(['PAPEL CREPADO', 'MANGA', 'PAPEL NO TEJIDO'] as $opcion)
                    <option value="{{ $opcion }}" @selected(old('empaque') === $opcion)>{{ $opcion }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="tipo_carga">Tipo de carga</label>
            <select id="tipo_carga" name="tipo_carga" required>
                @foreach(['INSTRUMENTAL','TEXTIL','GOMA'] as $tipo)
                    <option value="{{ $tipo }}" @selected(old('tipo_carga') === $tipo)>{{ $tipo }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="numero_carga">Numero de carga</label>
            <input id="numero_carga" name="numero_carga" value="{{ old('numero_carga') }}" maxlength="50" class="upper" required>
        </div>
        <div>
            <label for="ib">IB</label>
            <select id="ib" name="ib" required>
                <option value="">Seleccione</option>
                @foreach(['VIRO', 'NO VIRO', 'SI VIRO', 'REACCIONO', 'NO REACCIONO'] as $opcion)
                    <option value="{{ $opcion }}" @selected(old('ib') === $opcion)>{{ $opcion }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="equipo_biomedico">Equipo biomédico</label>
            <select id="equipo_biomedico" name="equipo_biomedico" required>
                <option value="">Seleccione</option>
                @foreach(\App\Models\AreaAzul::EQUIPOS_BIOMÉDICOS_AZUL as $valor => $etiqueta)
                    <option value="{{ $valor }}" @selected(old('equipo_biomedico') === $valor)>{{ $etiqueta }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="operador_id">Operador</label>
            <select id="operador_id" name="operador_id" required>
                <option value="">Seleccione</option>
                @foreach($usuarios as $usuario)
                    <option value="{{ $usuario->id }}" @selected(old('operador_id') == $usuario->id)>{{ $usuario->nombre_completo }}</option>
                @endforeach
            </select>
        </div>
        <div class="field-actions-end">
            <button class="btn blue" type="submit">Guardar Area Azul</button>
        </div>
    </form>
</section>
@endsection
