@extends('layouts.app')

@section('title', 'Registrar Área Azul')

@section('content')
<div class="detail-actions area-page-header area-azul-header">
    <div class="title-with-back">
        <a class="btn" href="{{ route('esterilizacion.procesos.show', $proceso) }}" title="Volver al proceso" aria-label="Volver al proceso"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg></a>
        <h1 class="no-margin">Registrar Área Azul - Proceso #{{ $proceso->getKey() }}</h1>
    </div>
</div>

<section class="panel area-form-card area-context-card">
    <div class="summary">
        <div><span>Fecha</span>{{ optional($proceso->fecha)->format('d/m/Y') ?? $proceso->fecha }}</div>
        <div><span>Servicios y materiales</span>{{ $proceso->instrumentos->map(fn($i) => ($i->servicio?->nombre ?? "Servicio histórico")." · ".($i->material?->nombre ?? "Material histórico"))->unique()->implode(", ") ?: "Sin detalle de instrumental" }}</div>
        <div><span>Cantidad</span>{{ $proceso->cantidad_piezas }}</div>
        <div><span>Estado</span><span class="badge {{ $proceso->estado_actual }}">{{ $proceso->estado_actual }}</span></div>
    </div>
</section>

<section class="panel area-form-card">
    <form method="POST" action="{{ route('esterilizacion.azul.store', $proceso) }}" class="grid area-form area-azul-form">
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
            <label for="numero_carga">Número de carga</label>
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
                @foreach(\App\Models\AreaAzul::EQUIPOS_BIOMEDICOS_AZUL as $valor => $etiqueta)
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
            <button class="btn blue" type="submit">Guardar Área Azul</button>
        </div>
    </form>
</section>
@endsection
