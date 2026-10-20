@extends('layouts.app')
@section('title', 'Iniciar sesión')
@section('content')
<section class="panel angosto">
  <h1>Iniciar sesión</h1>
  <form method="POST" action="{{ url('/login') }}" novalidate>
    @csrf
    <label for="email">Correo electrónico</label>
    <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus>
    <label for="password">Contraseña</label>
    <input id="password" name="password" type="password" required>
    @if ($errors->any())<p class="error" role="alert">{{ $errors->first() }}</p>@endif
    <button class="btn" type="submit">Iniciar sesión</button>
  </form>
  <p class="muted">¿No tienes cuenta? <a href="{{ route('register') }}">Regístrate</a></p>
</section>
@endsection
