@extends('layouts.app')

@section('title', 'Reportes Excel')

@section('content')
<h1>Reportes Excel</h1>

<p class="muted">
    Elige el tipo de reporte y el periodo a consultar. El año es obligatorio; el mes se completa
    cuando eliges consulta por mes o por día, y el día solo cuando la consulta es por día.
    Si no existen registros para el periodo no se genera el archivo.
</p>

<section class="panel">
    <h2>1. Reporte operativo por áreas</h2>
    <p class="muted">
        Un registro por proceso agrupado en secciones de Área Roja, Área Azul, Área Verde y Observación,
        con los responsables de cada etapa. Listo para imprimir.
    </p>
    <form method="GET" action="{{ route('esterilizacion.reportes.operativo') }}" class="grid filters-grid" data-form-periodo>
        @include('esterilizacion.reportes._periodo', ['prefijo' => 'operativo'])
        <div class="field-actions">
            <button type="submit" class="btn green">Descargar Excel</button>
        </div>
    </form>
</section>

<section class="panel">
    <h2>2. Reporte completo actual</h2>
    <p class="muted">
        Todas las acciones registradas (usuarios, áreas, procesos y descripciones) con sus mismas
        columnas y datos, consultadas por el periodo elegido.
    </p>
    <form method="GET" action="{{ route('esterilizacion.reportes.completo') }}" class="grid filters-grid" data-form-periodo>
        @include('esterilizacion.reportes._periodo', ['prefijo' => 'completo'])
        <div class="field-actions">
            <button type="submit" class="btn green">Descargar Excel</button>
        </div>
    </form>
</section>

<section class="panel">
    <h2>3. Reporte por usuario</h2>
    <p class="muted">
        Actividad registrada por el usuario seleccionado en el periodo elegido, con el responsable
        de cada etapa del proceso.
    </p>
    <form method="GET" action="{{ route('esterilizacion.reportes.usuario') }}" class="grid filters-grid" data-form-periodo>
        <div>
            <label for="usuario_reporte">Usuario</label>
            <select id="usuario_reporte" name="usuario_id" required>
                <option value="">Seleccione</option>
                @foreach($usuarios as $usuario)
                    <option value="{{ $usuario->id }}">{{ $usuario->nombre_completo }}</option>
                @endforeach
            </select>
        </div>
        @include('esterilizacion.reportes._periodo', ['prefijo' => 'usuario'])
        <div class="field-actions">
            <button type="submit" class="btn green">Descargar Excel</button>
        </div>
    </form>
</section>

<script>
document.querySelectorAll('form[data-form-periodo]').forEach(function (form) {
    const tipo = form.querySelector('[data-tipo-periodo]');
    const mesWrap = form.querySelector('[data-mes-wrap]');
    const diaWrap = form.querySelector('[data-dia-wrap]');
    const mes = form.querySelector('[data-mes]');
    const dia = form.querySelector('[data-dia]');

    function aplicar() {
        const valor = tipo.value;
        mesWrap.hidden = valor === 'anio';
        diaWrap.hidden = valor !== 'dia';
        mes.disabled = valor === 'anio';
        dia.disabled = valor !== 'dia';
    }

    tipo.addEventListener('change', aplicar);
    aplicar();
});
</script>
@endsection
