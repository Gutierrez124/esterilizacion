<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Iniciar sesion</title>
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
            <strong>No se pudo iniciar sesion.</strong>
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
        <input id="password" type="password" name="password" required autocomplete="current-password">

        <div class="remember">
            <input id="remember" type="checkbox" name="remember">
            <label for="remember" class="checkbox-label">Recordar</label>
        </div>

        <button type="submit">Iniciar sesión</button>
    </form>
</div>
</body>
</html>
