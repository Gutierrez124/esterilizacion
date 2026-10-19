@extends('layouts.app')

@section('title', 'Mi perfil')

@section('content')
@php
    $iniciales = collect(explode(' ', trim($usuario->nombre_completo)))
        ->filter()
        ->take(2)
        ->map(fn ($parte) => mb_strtoupper(mb_substr($parte, 0, 1)))
        ->implode('');
@endphp

<h1>Mi perfil</h1>

<p class="muted">Datos de tu cuenta y acceso a tus auditorias personales.</p>

<section class="profile-hero">
    <div class="profile-avatar">{{ $iniciales !== '' ? $iniciales : '?' }}</div>
    <div class="profile-hero-info">
        <p class="profile-name">{{ $usuario->nombre_completo }}</p>
        <div class="profile-meta">
            <span class="profile-chip">{{ $usuario->rol }}</span>
            <span class="profile-chip {{ $usuario->activo ? 'is-ok' : 'is-off' }}">{{ $usuario->activo ? 'Cuenta activa' : 'Cuenta inactiva' }}</span>
            <span class="profile-chip">{{ $usuario->email ?? 'Sin correo asignado' }}</span>
        </div>
    </div>
</section>

<div class="profile-stats">
    <div class="profile-stat">
        <span class="stat-label">Usuario desde</span>
        <p class="stat-value">{{ $registradoEl ?? 'Sin fecha registrada' }}</p>
    </div>
    <div class="profile-stat">
        <span class="stat-label">Ultimo inicio de sesion</span>
        <p class="stat-value">{{ $ultimoLogin?->created_at?->format('d/m/Y H:i') ?? 'Sin inicios registrados' }}</p>
    </div>
    <div class="profile-stat">
        <span class="stat-label">Acciones registradas</span>
        <p class="stat-value">{{ $totalAuditorias }}</p>
    </div>
</div>

<section class="profile-audit-card">
    <div>
        <h2 class="audit-title">Mis auditorias</h2>
        <p class="muted audit-text">
            Consulta tus inicios y cierres de sesion y cada accion que realizaste.
            Este es el unico punto de acceso a tus auditorias.
        </p>
    </div>
    <a class="btn primary btn-lg" href="{{ route('esterilizacion.auditorias.index') }}">Ver mis auditorias &#8594;</a>
</section>
@endsection
