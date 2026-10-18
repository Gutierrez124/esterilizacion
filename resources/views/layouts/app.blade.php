<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Esterilizacion hospitalaria')</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}?v={{ filemtime(public_path('css/app.css')) }}">
</head>
<body>
<div class="app">
    <aside class="sidenav" id="sidenav">
        <div class="brand">Control de esterilizacion<small>Gestion hospitalaria</small></div>
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
            <span class="nav-icon">&#128202;</span> Reportes Excel
        </a>
        <div class="spacer"></div>
        @auth
            <form method="POST" action="{{ route('logout') }}" class="logout-form">
                @csrf
                <button type="submit" class="nav-item"><span class="nav-icon"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg></span> Cerrar sesion</button>
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
            @if(session('success'))
                <div class="alert success">{{ session('success') }}</div>
            @endif
            @if(session('status'))
                <div class="alert success">{{ session('status') }}</div>
            @endif
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
@stack('scripts')
<script>
    const sidenav = document.getElementById('sidenav');
    const overlay = document.getElementById('overlay');
    const toggle = document.getElementById('menuToggle');
    function closeNav(){ sidenav.classList.remove('open'); overlay.classList.remove('show'); }
    toggle && toggle.addEventListener('click', () => { sidenav.classList.toggle('open'); overlay.classList.toggle('show'); });
    overlay && overlay.addEventListener('click', closeNav);
</script>
</body>
</html>

