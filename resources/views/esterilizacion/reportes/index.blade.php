@extends('layouts.app')

@section('title', 'Reportes Excel')

@section('content')
<header class="reportes-header">
    <span class="reportes-kicker">CENTRAL DE ESTERILIZACIÓN</span>
    <h1>Reportes Excel</h1>
    <p>Define las fechas de inicio y fin para consultar el rango que necesitas.</p>
</header>

<div class="reportes-grid">
    <section class="panel reporte-card">
        <div class="reporte-card-heading">
            <span class="reporte-numero">01</span>
            <div>
                <h2>Reporte operativo por áreas</h2>
                <p>Procesos y responsables de las áreas Roja, Azul, Verde y Observación.</p>
            </div>
        </div>
        <form method="GET" action="{{ route('esterilizacion.reportes.operativo') }}" class="reporte-form" data-form-periodo>
            @include('esterilizacion.reportes._periodo', ['prefijo' => 'operativo'])
            <button type="submit" class="btn primary">Descargar Excel</button>
        </form>
    </section>

    <section class="panel reporte-card">
        <div class="reporte-card-heading">
            <span class="reporte-numero">02</span>
            <div>
                <h2>Reporte completo</h2>
                <p>Acciones, usuarios, áreas y procesos registrados durante el período elegido.</p>
            </div>
        </div>
        <form method="GET" action="{{ route('esterilizacion.reportes.completo') }}" class="reporte-form" data-form-periodo>
            @include('esterilizacion.reportes._periodo', ['prefijo' => 'completo'])
            <button type="submit" class="btn primary">Descargar Excel</button>
        </form>
    </section>

    <section class="panel reporte-card">
        <div class="reporte-card-heading">
            <span class="reporte-numero">03</span>
            <div>
                <h2>Reporte por usuario</h2>
                <p>Actividad del usuario seleccionado durante el período elegido.</p>
            </div>
        </div>
        <form method="GET" action="{{ route('esterilizacion.reportes.usuario') }}" class="reporte-form" data-form-periodo>
            <div class="reporte-periodo">
                <label for="usuario_reporte">Usuario</label>
                <select id="usuario_reporte" name="usuario_id" required>
                    <option value="">Selecciona un usuario</option>
                    @foreach($usuarios as $usuario)
                        <option value="{{ $usuario->id }}" @selected((string) old('usuario_id') === (string) $usuario->id)>{{ $usuario->nombre_completo }}</option>
                    @endforeach
                </select>
            </div>
            @include('esterilizacion.reportes._periodo', ['prefijo' => 'usuario'])
            <button type="submit" class="btn primary">Descargar Excel</button>
        </form>
    </section>
</div>


@endsection
