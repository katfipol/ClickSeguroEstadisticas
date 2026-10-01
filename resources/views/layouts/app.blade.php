<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'Click Seguro')</title>
  <link rel="stylesheet" href="{{ asset('css/app.css') }}">
  <script src="{{ asset('js/app.js') }}" defer></script>
</head>
<body>
@auth
  <header class="nav">
    <a class="marca" href="{{ route('dashboard') }}">Click Seguro</a>
    <nav>
      <a href="{{ route('dashboard') }}">Analizar</a>
      <a href="{{ route('historial') }}">Historial</a>
      <a href="{{ route('estadisticas') }}">Estadísticas</a>
      <a href="{{ route('reportes.create') }}">Reportar</a>
    </nav>
    <form method="POST" action="{{ route('logout') }}">@csrf<button class="link" type="submit">Cerrar sesión</button></form>
  </header>
@endauth
<main class="contenedor">
  @if (session('ok'))<p class="aviso ok" role="status">{{ session('ok') }}</p>@endif
  @yield('content')
</main>
</body>
</html>
