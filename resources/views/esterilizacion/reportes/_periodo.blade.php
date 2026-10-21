<div class="reporte-periodo reporte-rango-fechas">
    <small>Selecciona en el calendario o escribe las fechas del rango.</small>
    <div class="reporte-fechas">
        <div>
            <label for="{{ $prefijo }}_fecha_desde">Desde</label>
            <input
                id="{{ $prefijo }}_fecha_desde"
                type="date"
                name="fecha_desde"
                value="{{ old('fecha_desde', now()->startOfMonth()->toDateString()) }}"
                min="2000-01-01"
                max="2100-12-31"
                required
            >
        </div>
        <div>
            <label for="{{ $prefijo }}_fecha_hasta">Hasta</label>
            <input
                id="{{ $prefijo }}_fecha_hasta"
                type="date"
                name="fecha_hasta"
                value="{{ old('fecha_hasta', now()->toDateString()) }}"
                min="2000-01-01"
                max="2100-12-31"
                required
            >
        </div>
    </div>
</div>