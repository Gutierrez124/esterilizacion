<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Esterilizacion hospitalaria')</title>
    <style>
        :root {
            --ink:#182230;
            --muted:#667085;
            --line:#d0d5dd;
            --soft-line:#eaecf0;
            --bg:#f3f6f8;
            --panel:#ffffff;
            --red:#d11135;
            --red-soft:#fff1f3;
            --blue:#315fb5;
            --blue-soft:#eff6ff;
            --green:#4c7f32;
            --green-soft:#f0f9ed;
            --brand:#0f766e;
            --brand-dark:#0b4f4a;
            --gray:#475467;
            --shadow:0 10px 24px rgba(16,24,40,.08);
        }
        * { box-sizing:border-box; }
        body { margin:0; background:var(--bg); color:var(--ink); font-family:Arial, Helvetica, sans-serif; font-size:15px; }
        header { position:sticky; top:0; z-index:20; background:#fff; border-bottom:1px solid var(--soft-line); box-shadow:0 2px 8px rgba(16,24,40,.04); }
        .wrap { width:min(1220px, calc(100% - 32px)); margin:0 auto; }
        .topbar { display:flex; justify-content:space-between; gap:16px; align-items:center; padding:14px 0; }
        .brand { font-size:20px; font-weight:800; color:var(--brand-dark); letter-spacing:.01em; }
        nav { display:flex; gap:8px; flex-wrap:wrap; }
        nav a, .btn { display:inline-flex; align-items:center; justify-content:center; min-height:38px; padding:9px 13px; border:1px solid var(--line); border-radius:7px; background:#fff; color:var(--ink); text-decoration:none; font-weight:700; cursor:pointer; transition:border-color .15s, background .15s, transform .15s; }
        nav a:hover, .btn:hover { border-color:#98a2b3; background:#f9fafb; }
        .btn.primary { background:var(--brand); color:#fff; border-color:var(--brand); }
        .btn.primary:hover { background:var(--brand-dark); border-color:var(--brand-dark); }
        .btn.blue { background:var(--blue); color:#fff; border-color:var(--blue); }
        .btn.green { background:var(--green); color:#fff; border-color:var(--green); }
        main { padding:24px 0 48px; }
        h1 { font-size:28px; margin:0 0 18px; letter-spacing:0; }
        h2 { font-size:18px; margin:0 0 12px; }
        .panel { background:var(--panel); border:1px solid var(--soft-line); border-radius:8px; padding:18px; margin-bottom:18px; box-shadow:var(--shadow); }
        .grid { display:grid; grid-template-columns:repeat(4, minmax(0, 1fr)); gap:15px; align-items:start; }
        .grid.two { grid-template-columns:repeat(2, minmax(0, 1fr)); }
        label { display:block; font-weight:800; margin-bottom:6px; color:#344054; font-size:13px; }
        input, select, textarea { width:100%; min-height:40px; border:1px solid #b8c4ce; border-radius:7px; padding:8px 10px; background:#fff; color:var(--ink); outline:none; }
        input:focus, select:focus, textarea:focus { border-color:var(--brand); box-shadow:0 0 0 3px rgba(15,118,110,.14); }
        textarea { min-height:96px; resize:vertical; }
        table { width:100%; border-collapse:separate; border-spacing:0; background:#fff; border:1px solid var(--soft-line); border-radius:8px; overflow:hidden; box-shadow:var(--shadow); }
        th, td { padding:12px 11px; border-bottom:1px solid var(--soft-line); text-align:left; vertical-align:middle; }
        th { background:#eef3f6; font-size:12px; text-transform:uppercase; letter-spacing:.02em; color:#344054; }
        tbody tr:hover td { background:#f9fafb; }
        tbody tr:last-child td { border-bottom:0; }
        .actions { display:flex; gap:8px; flex-wrap:wrap; }
        .badge { display:inline-flex; align-items:center; min-height:24px; padding:4px 9px; border-radius:999px; font-weight:800; font-size:12px; }
        .badge.ROJA { background:#ffe4e8; color:var(--red); }
        .badge.AZUL { background:#dbeafe; color:var(--blue); }
        .badge.VERDE { background:#dcfce7; color:#166534; }
        .muted { color:var(--muted); }
        .alert { padding:12px 14px; border-radius:7px; margin-bottom:16px; border:1px solid; background:#fff; }
        .alert.success { background:#ecfdf3; border-color:#abefc6; color:#067647; }
        .alert.error { background:#fef3f2; border-color:#fecdca; color:#b42318; }
        .alert ul { margin:8px 0 0 18px; padding:0; }
        .summary { display:grid; grid-template-columns:repeat(3, minmax(0, 1fr)); gap:12px; }
        .summary div { background:#f8fafc; border:1px solid var(--soft-line); border-radius:7px; padding:11px; min-height:62px; }
        .summary span { display:block; color:var(--muted); font-size:12px; font-weight:800; text-transform:uppercase; margin-bottom:5px; }
        .pagination { margin-top:14px; }
        .section-title { display:flex; align-items:center; justify-content:space-between; gap:12px; margin:0 0 14px; padding:11px 13px; border-radius:7px; color:#fff; font-size:18px; font-weight:800; text-transform:uppercase; }
        .section-title.service { background:#344054; }
        .section-title.red { background:var(--red); }
        .section-title.blue { background:var(--blue); }
        .section-title.green { background:var(--green); }
        .section-title.observation { background:#6b7280; }
        .detail-actions { display:flex; gap:8px; flex-wrap:wrap; margin-bottom:18px; }
        .form-actions { display:flex; align-items:end; gap:8px; height:100%; }
        .form-actions .btn { width:100%; }
        @media (max-width: 1000px) { .grid { grid-template-columns:repeat(2, minmax(0, 1fr)); } .summary { grid-template-columns:repeat(2, minmax(0, 1fr)); } }
        @media (max-width: 700px) { .grid, .grid.two, .summary { grid-template-columns:1fr; } .topbar { align-items:flex-start; flex-direction:column; } table { display:block; overflow-x:auto; } h1 { font-size:24px; } }
    </style>
</head>
<body>
<header>
    <div class="wrap topbar">
        <div class="brand">Control de esterilizacion</div>
        <nav>
            <a href="{{ route('esterilizacion.procesos.index') }}">Procesos</a>
            <a href="{{ route('esterilizacion.roja.create') }}">Area Roja</a>
            <a href="{{ route('esterilizacion.azul.index') }}">Area Azul</a>
            <a href="{{ route('esterilizacion.verde.index') }}">Area Verde</a>
        </nav>
    </div>
</header>
<main class="wrap">
    @if(session('success'))
        <div class="alert success">{{ session('success') }}</div>
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
@stack('scripts')
</body>
</html>