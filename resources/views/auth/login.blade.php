<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Iniciar sesión</title>
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}?v={{ filemtime(public_path('css/auth.css')) }}">
    <link rel="icon" type="image/png" sizes="128x128" href="{{ asset('logos/icono.png') }}?v={{ filemtime(public_path('logos/icono.png')) }}">
    <link rel="apple-touch-icon" sizes="128x128" href="{{ asset('logos/icono.png') }}?v={{ filemtime(public_path('logos/icono.png')) }}">

</head>
<body>
<div class="card">
    <div class="logo">
        <img src="{{ asset('logos/BellaVistaLogin.png') }}" alt="Hospital Bella Vista">
    </div>
    <h1>Trazabilidad - Central de Esterilización</h1>
    <p class="sub">Acceso para personal del hospital</p>

    @if(session('status'))
        <div class="alert success">{{ session('status') }}</div>
    @endif

    @if($errors->any())
        <div class="errors">
            <strong>No se pudo iniciar sesión.</strong>
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('login.store') }}">
        @csrf
        <label for="email">Correo electrónico</label>
        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username">

        <label for="password">Contraseña</label>
        <div class="password-wrap">
            <input id="password" type="password" name="password" required autocomplete="current-password">
            <button type="button" class="eye-btn" data-eye="password" title="Mostrar u ocultar contraseña" aria-label="Mostrar u ocultar contraseña">
                <svg class="ico-eye" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                <svg class="ico-eye-off is-hidden" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>
            </button>
        </div>

        <div class="remember">
            <input id="remember" type="checkbox" name="remember">
            <label for="remember" class="checkbox-label">Recordar</label>
        </div>

        <button type="submit">Iniciar sesión</button>
    </form>
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
});
</script>
</body>
</html>
