<div>
    <label for="{{ $prefijo }}_tipo_periodo">Tipo de periodo</label>
    <select id="{{ $prefijo }}_tipo_periodo" name="tipo_periodo" data-tipo-periodo required>
        <option value="anio">Por año</option>
        <option value="mes">Por mes</option>
        <option value="dia">Por día</option>
    </select>
</div>
<div>
    <label for="{{ $prefijo }}_anio">Año</label>
    <input id="{{ $prefijo }}_anio" type="number" name="anio" min="2000" max="2100" step="1" value="{{ $anioActual }}" required>
</div>
<div data-mes-wrap hidden>
    <label for="{{ $prefijo }}_mes">Mes</label>
    <select id="{{ $prefijo }}_mes" name="mes" data-mes>
        @foreach($meses as $numero => $nombre)
            <option value="{{ $numero }}" @selected($numero === $mesActual)>{{ $nombre }}</option>
        @endforeach
    </select>
</div>
<div data-dia-wrap hidden>
    <label for="{{ $prefijo }}_dia">Día</label>
    <input id="{{ $prefijo }}_dia" type="number" name="dia" min="1" max="31" step="1" value="{{ $diaActual }}" data-dia>
</div>
