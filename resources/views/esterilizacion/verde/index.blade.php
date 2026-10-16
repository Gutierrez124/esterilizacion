@extends('layouts.app')

@section('title', 'Pendientes Area Verde')

@section('content')
<div class="detail-actions">
    <h1 class="no-margin">Pendientes de Area Verde</h1>
    <a class="btn primary" href="{{ route('esterilizacion.roja.create') }}">Nuevo proceso</a>
</div>
<section class="panel">
    <div class="section-title green">Procesos listos para descarga y entrega</div>
    @include('esterilizacion.partials.procesos-table', ['procesos' => $procesos])
</section>
@endsection
