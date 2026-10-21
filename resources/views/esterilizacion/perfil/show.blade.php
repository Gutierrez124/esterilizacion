@extends('layouts.app')

@section('title', 'Inicio - Mi perfil')

@section('content')
@php
    $iniciales = collect(explode(' ', trim($usuario->nombre_completo)))
        ->filter()
        ->take(2)
        ->map(fn ($parte) => mb_strtoupper(mb_substr($parte, 0, 1)))
        ->implode('');
    $fechaHoy = \Illuminate\Support\Carbon::now()->locale('es')->translatedFormat('d \d\e F \d\e Y');
@endphp

{{-- Dashboard de bienvenida --}}
<section class="welcome-banner">
    <div class="welcome-main">
        <div class="welcome-avatar">
            @if($usuario->url_foto)
                <button type="button" class="avatar-zoom" data-lightbox-src="{{ $usuario->url_foto }}" title="Ver foto completa" aria-label="Ver foto completa">
                    <img src="{{ $usuario->url_foto }}" alt="Foto de perfil">
                </button>
            @else
                {{ $iniciales !== '' ? $iniciales : '?' }}
            @endif
        </div>
        <div class="welcome-text">
            <p class="welcome-greeting">Hola, <strong>{{ $usuario->nombres }}</strong> <span class="welcome-wave">&#128075;</span></p>
            <h1 class="welcome-title">Bienvenido al Sistema de trazabilidad de Central de Esterilización del Hospital IIE Bellavista SITCE</h1>
            <div class="profile-meta">
                @if($usuario->dni)
                    <span class="profile-chip">DNI: {{ $usuario->dni }}</span>
                @endif
                @if($usuario->codigo)
                    <span class="profile-chip">Código: {{ $usuario->codigo }}</span>
                @endif
            </div>
        </div>
    </div>
    <div class="welcome-side">
        <span class="welcome-date">{{ ucfirst($fechaHoy) }}</span>
        <div class="welcome-quick">
            <a class="welcome-quick-link" href="{{ route('esterilizacion.auditorias.index') }}">&#128269; Auditorías</a>
        </div>
    </div>
</section>

<div class="profile-stats">
    <div class="profile-stat">
        <span class="stat-label">Usuario desde</span>
        <p class="stat-value">{{ $registradoEl ?? 'Sin fecha registrada' }}</p>
    </div>
    <div class="profile-stat">
        <span class="stat-label">Último inicio de sesión</span>
        <p class="stat-value">{{ $ultimoLogin?->created_at?->format('d/m/Y H:i') ?? 'Sin inicios registrados' }}</p>
    </div>
    <div class="profile-stat">
        <span class="stat-label">Acciones registradas</span>
        <p class="stat-value">{{ $totalAuditorias }}</p>
    </div>
</div>

<div class="profile-columns">
    <section class="panel">
        <h2>Datos personales</h2>
        <p class="muted" style="margin-top:0;">Actualiza tu nombre, DNI, código, correo y foto de perfil.</p>
        <form method="POST" action="{{ route('esterilizacion.perfil.update') }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="grid profile-grid">
                <div>
                    <label for="nombres">Nombres</label>
                    <input id="nombres" type="text" name="nombres" value="{{ old('nombres', $usuario->nombres) }}" maxlength="100" required>
                </div>
                <div>
                    <label for="apellidos">Apellidos</label>
                    <input id="apellidos" type="text" name="apellidos" value="{{ old('apellidos', $usuario->apellidos) }}" maxlength="100" required>
                </div>
                <div>
                    <label for="dni">DNI</label>
                    <input id="dni" type="text" name="dni" value="{{ old('dni', $usuario->dni) }}" maxlength="20" placeholder="Ej. 12345678">
                </div>
                <div>
                    <label for="codigo">Código</label>
                    <input id="codigo" type="text" name="codigo" value="{{ old('codigo', $usuario->codigo) }}" maxlength="20" placeholder="Ej. 00123">
                </div>
                <div class="profile-grid-full">
                    <label for="email">Correo electrónico</label>
                    <input id="email" type="email" name="email" value="{{ old('email', $usuario->email) }}" maxlength="255" required>
                </div>
                <div class="profile-grid-full">
                    <label for="foto">Foto de perfil</label>
                    <div class="foto-row">
                        <input id="foto" type="file" name="foto" accept="image/jpeg,image/png,image/webp">
                        <img id="fotoPreview" class="foto-preview is-hidden" alt="Vista previa de la foto">
                        @if($usuario->url_foto)
                            <img class="foto-preview" src="{{ $usuario->url_foto }}" data-lightbox-src="{{ $usuario->url_foto }}" title="Ver foto completa" alt="Foto actual de perfil" style="cursor:zoom-in;">
                        @endif
                        <span class="muted">JPG, PNG o WebP. Máximo 2 MB.</span>
                    </div>
                </div>
                <div class="field-actions-end profile-grid-full">
                    <button type="submit" class="btn primary">Guardar cambios</button>
                </div>
            </div>
        </form>
    </section>

    <div class="profile-side">
        <section class="panel">
            <h2>Seguridad</h2>
            <p class="muted" style="margin-top:0;">Tu contraseña está protegida (solo se guarda hasheada). Para cambiarla, escribe tu contraseña actual y la nueva dos veces.</p>
            <form method="POST" action="{{ route('esterilizacion.perfil.password') }}">
                @csrf
                @method('PUT')
                <div class="stack-fields">
                    <div>
                        <label for="password_actual">Contraseña actual</label>
                        <div class="password-wrap">
                            <input id="password_actual" type="password" name="password_actual" required autocomplete="current-password" placeholder="Escribe tu contraseña actual">
                            <button type="button" class="eye-btn" data-eye="password_actual" title="Mostrar u ocultar contraseña" aria-label="Mostrar u ocultar contraseña">
                                <svg class="ico-eye" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                <svg class="ico-eye-off is-hidden" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>
                            </button>
                        </div>
                    </div>
                    <div>
                        <label for="password">Nueva contraseña</label>
                        <div class="password-wrap">
                            <input id="password" type="password" name="password" minlength="6" required autocomplete="new-password" placeholder="Mínimo 6 caracteres">
                            <button type="button" class="eye-btn" data-eye="password" title="Mostrar u ocultar contraseña" aria-label="Mostrar u ocultar contraseña">
                                <svg class="ico-eye" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                <svg class="ico-eye-off is-hidden" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>
                            </button>
                        </div>
                    </div>
                    <div>
                        <label for="password_confirmation">Repetir nueva contraseña</label>
                        <div class="password-wrap">
                            <input id="password_confirmation" type="password" name="password_confirmation" minlength="6" required autocomplete="new-password" placeholder="Debe coincidir">
                            <button type="button" class="eye-btn" data-eye="password_confirmation" title="Mostrar u ocultar contraseña" aria-label="Mostrar u ocultar contraseña">
                                <svg class="ico-eye" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                <svg class="ico-eye-off is-hidden" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>
                            </button>
                        </div>
                    </div>
                    <div class="field-actions-end">
                        <button type="submit" class="btn primary">Cambiar contraseña</button>
                    </div>
                </div>
            </form>
        </section>
    </div>
</div>

{{-- Lightbox: vista completa de la foto de perfil --}}
<div class="photo-lightbox is-hidden" id="photoLightbox" role="dialog" aria-modal="true" aria-label="Foto de perfil ampliada">
    <button type="button" class="lightbox-close" id="lightboxClose" aria-label="Cerrar">&times;</button>
    <img id="lightboxImg" src="" alt="Foto de perfil ampliada">
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.eye-btn[data-eye]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const input = document.getElementById(btn.getAttribute('data-eye'));
            if (!input) return;
            const ver = input.type === 'password';
            input.type = ver ? 'text' : 'password';
            btn.querySelector('.ico-eye').classList.toggle('is-hidden', ver);
            btn.querySelector('.ico-eye-off').classList.toggle('is-hidden', !ver);
        });
    });

    const foto = document.getElementById('foto');
    const preview = document.getElementById('fotoPreview');
    if (foto && preview) {
        foto.addEventListener('change', function () {
            const archivo = foto.files && foto.files[0];
            if (!archivo) return;
            preview.src = URL.createObjectURL(archivo);
            preview.classList.remove('is-hidden');
        });
    }

    // Lightbox: abrir la foto completa al hacer clic en el avatar o la foto actual
    const lightbox = document.getElementById('photoLightbox');
    const lightboxImg = document.getElementById('lightboxImg');
    const lightboxClose = document.getElementById('lightboxClose');

    function abrirLightbox(src) {
        if (!lightbox || !lightboxImg || !src) return;
        lightboxImg.src = src;
        lightbox.classList.remove('is-hidden');
        document.body.style.overflow = 'hidden';
    }

    function cerrarLightbox() {
        if (!lightbox || !lightboxImg) return;
        lightbox.classList.add('is-hidden');
        lightboxImg.src = '';
        document.body.style.overflow = '';
    }

    document.querySelectorAll('[data-lightbox-src]').forEach(function (el) {
        el.addEventListener('click', function () {
            abrirLightbox(el.getAttribute('data-lightbox-src'));
        });
    });

    if (lightboxClose) lightboxClose.addEventListener('click', cerrarLightbox);
    if (lightbox) lightbox.addEventListener('click', function (e) {
        if (e.target === lightbox) cerrarLightbox();
    });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && lightbox && !lightbox.classList.contains('is-hidden')) cerrarLightbox();
    });
});
</script>
@endsection
