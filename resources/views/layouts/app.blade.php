<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Esterilizacion hospitalaria')</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}?v={{ filemtime(public_path('css/app.css')) }}">
    <link rel="icon" type="image/png" sizes="128x128" href="{{ asset('logos/icono.png') }}?v={{ filemtime(public_path('logos/icono.png')) }}">
    <link rel="apple-touch-icon" sizes="128x128" href="{{ asset('logos/icono.png') }}?v={{ filemtime(public_path('logos/icono.png')) }}">

</head>
<body>
<div class="app">
    <aside class="sidenav" id="sidenav">
        <div class="brand">
            <img class="brand-logo" src="{{ asset('logos/BellaVistaSidenav.png') }}" alt="Logo hospital">
            <span>Control de esterilización<small>Gestión hospitalaria</small></span>
        </div>
        @auth
            <div class="nav-user">{{ Auth::user()->nombre_completo }} ({{ Auth::user()->rol }})</div>
        @endauth
        <a href="{{ route('esterilizacion.procesos.index') }}" class="nav-item @if(request()->routeIs('esterilizacion.procesos.*','esterilizacion.roja.*','esterilizacion.azul.*','esterilizacion.verde.*')) active @endif">
            <span class="nav-icon">&#8635;</span> Proceso
        </a>
        <a href="{{ route('esterilizacion.instrumental.index') }}" class="nav-item @if(request()->routeIs('esterilizacion.instrumental.*','esterilizacion.instrumentos.*')) active @endif">
            <span class="nav-icon">&#9878;</span> Instrumentos por material
        </a>
        <a href="{{ route('esterilizacion.reportes.index') }}" class="nav-item @if(request()->routeIs('esterilizacion.reportes.*')) active @endif">
            <span class="nav-icon"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line></svg></span> Reportes Excel
        </a>
        <a href="{{ route('esterilizacion.perfil.show') }}" class="nav-item @if(request()->routeIs('esterilizacion.perfil.*','esterilizacion.auditorias.*')) active @endif">
            <span class="nav-icon"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg></span> Perfil
        </a>
        <div class="spacer"></div>
        @auth
            <form method="POST" action="{{ route('logout') }}" class="logout-form">
                @csrf
                <button type="submit" class="nav-item"><span class="nav-icon"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg></span> Cerrar sesión</button>
            </form>
        @endauth
    </aside>
    <div class="overlay" id="overlay"></div>
    <div class="content">
        <header class="topbar">
            <button class="menu-toggle" id="menuToggle" type="button">&#9776; Menu</button>
            <div class="topbar-title">@yield('title', 'Esterilizacion hospitalaria')</div>
        </header>
        <main>
            @if($errors->any())
                <div class="alert error">
                    <strong>Revisa la informacion ingresada.</strong>
                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            @yield('content')
        </main>
    </div>
</div>

{{-- Modal global de confirmacion de eliminacion: exige escribir la palabra ELIMINAR --}}
<div id="modalEliminar" class="modal">
    <div class="panel modal-box modal-sm">
        <div class="modal-header">
            <h2 class="no-margin">Confirmar eliminacion</h2>
        </div>
        <p class="modal-intro">Para eliminar, escribe la palabra <strong>ELIMINAR</strong> y luego pulsa <strong>Aceptar</strong>.</p>
        <input id="modalEliminarPalabra" class="modal-eliminar-input" type="text" placeholder="Escribe ELIMINAR" autocomplete="off" spellcheck="false" aria-label="Palabra de confirmacion">
        <div class="modal-actions">
            <button type="button" class="btn" id="modalEliminarCancelar">Cancelar</button>
            <button type="button" class="btn red" id="modalEliminarAceptar" disabled>Aceptar</button>
        </div>
    </div>
</div>
{{-- Toast inferior de notificaciones: verde=exito, amarillo=warning/faltan campos, rojo=error --}}
@php
    $toastTipo = null;
    $toastMensaje = null;
    if (session('success')) { $toastTipo = 'success'; $toastMensaje = session('success'); }
    elseif (session('status')) { $toastTipo = 'success'; $toastMensaje = session('status'); }
    elseif (session('warning')) { $toastTipo = 'warning'; $toastMensaje = session('warning'); }
    elseif (session('error')) { $toastTipo = 'error'; $toastMensaje = session('error'); }
    $toastIconos = [
        'success' => '<svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>',
        'warning' => '<svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="7" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>',
        'error' => '<svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>',
    ];
@endphp
<div id="toastArea" class="toast-wrap" aria-live="polite">
    @if($toastMensaje)
        <div class="toast {{ $toastTipo }}" data-toast role="status">
            <span class="toast-ico">{!! $toastIconos[$toastTipo] !!}</span><span>{{ $toastMensaje }}</span>
        </div>
    @endif
</div>
@stack('scripts')
<script>
    const sidenav = document.getElementById('sidenav');
    const overlay = document.getElementById('overlay');
    const toggle = document.getElementById('menuToggle');
    function closeNav(){ sidenav.classList.remove('open'); overlay.classList.remove('show'); }
    toggle && toggle.addEventListener('click', () => { sidenav.classList.toggle('open'); overlay.classList.toggle('show'); });
    overlay && overlay.addEventListener('click', closeNav);
    document.addEventListener('submit', function (e) {
        e.target.querySelectorAll('.upper').forEach(function (campo) {
            campo.value = campo.value.toUpperCase();
        });
    });

    // ===== Modal global de eliminacion: hay que escribir ELIMINAR para confirmar =====
    const modalEliminar = document.getElementById('modalEliminar');
    const palabraEliminar = document.getElementById('modalEliminarPalabra');
    const btnAceptarEliminar = document.getElementById('modalEliminarAceptar');
    const btnCancelarEliminar = document.getElementById('modalEliminarCancelar');
    let formPendienteEliminar = null;

    function abrirModalEliminar(form) {
        if (!modalEliminar || !form) return;
        formPendienteEliminar = form;
        palabraEliminar.value = '';
        btnAceptarEliminar.disabled = true;
        modalEliminar.classList.add('is-open');
        document.body.classList.add('modal-abierto');
        setTimeout(() => palabraEliminar.focus(), 60);
    }

    function cerrarModalEliminar() {
        if (!modalEliminar) return;
        modalEliminar.classList.remove('is-open');
        document.body.classList.remove('modal-abierto');
        formPendienteEliminar = null;
        palabraEliminar.value = '';
        btnAceptarEliminar.disabled = true;
    }

    // Cualquier formulario con class="form-eliminar" abre el modal en vez de enviarse.
    document.addEventListener('submit', function (e) {
        const form = e.target;
        if (form && form.classList && form.classList.contains('form-eliminar')) {
            e.preventDefault();
            abrirModalEliminar(form);
        }
    });

    palabraEliminar && palabraEliminar.addEventListener('input', function () {
        btnAceptarEliminar.disabled = palabraEliminar.value.trim().toUpperCase() !== 'ELIMINAR';
    });

    palabraEliminar && palabraEliminar.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            if (!btnAceptarEliminar.disabled) btnAceptarEliminar.click();
        }
    });

    btnAceptarEliminar && btnAceptarEliminar.addEventListener('click', function () {
        if (btnAceptarEliminar.disabled || !formPendienteEliminar) return;
        const form = formPendienteEliminar;
        formPendienteEliminar = null;
        modalEliminar.classList.remove('is-open');
        document.body.classList.remove('modal-abierto');
        // submit() nativo NO dispara eventos 'submit': evita reabrir el mismo modal.
        form.submit();
    });

    btnCancelarEliminar && btnCancelarEliminar.addEventListener('click', cerrarModalEliminar);

    modalEliminar && modalEliminar.addEventListener('click', function (e) {
        if (e.target === modalEliminar) cerrarModalEliminar();
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && modalEliminar && modalEliminar.classList.contains('is-open')) cerrarModalEliminar();
    });

    // ===== Toasts de notificacion (parte inferior): success / warning / error =====
    const toastIconosJs = {
        success: '<svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>',
        warning: '<svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="7" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>',
        error: '<svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>'
    };
    function ocultarToast(toast) {
        if (!toast) return;
        toast.classList.add('hide');
        setTimeout(() => toast.remove(), 320);
    }
    function mostrarToast(mensaje, tipo) {
        const area = document.getElementById('toastArea');
        if (!area) return;
        tipo = String(tipo || 'success').toLowerCase();
        if (!toastIconosJs[tipo]) tipo = 'success';
        const toast = document.createElement('div');
        toast.className = 'toast ' + tipo;
        toast.setAttribute('role', 'status');
        toast.innerHTML = '<span class="toast-ico">' + toastIconosJs[tipo] + '</span><span></span>';
        toast.querySelector('span:last-child').textContent = mensaje;
        area.appendChild(toast);
        setTimeout(() => ocultarToast(toast), 4200);
    }
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('#toastArea .toast[data-toast]').forEach(function (toast) {
            setTimeout(() => ocultarToast(toast), 4200);
        });
    });
</script>
</body>
</html>

