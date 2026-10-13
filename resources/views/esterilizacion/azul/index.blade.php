@extends('layouts.app')

@section('title', 'Pendientes Area Azul')

@section('content')
<div class="detail-actions">
    <h1 style="margin:0;">Pendientes de Area Azul</h1>
    <a class="btn primary" href="{{ route('esterilizacion.roja.create') }}">Nuevo proceso</a>
</div>
<section class="panel">
    <div class="section-title blue">Procesos listos para empaque y carga</div>
    @include('esterilizacion.partials.procesos-table', ['procesos' => $procesos])
</section>
@endsection
