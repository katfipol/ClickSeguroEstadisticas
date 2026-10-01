@extends('layouts.app')
@section('title', 'Crear cuenta')
@section('content')
<section class="panel angosto">
  <h1>Crear cuenta</h1>
  <form method="POST" action="{{ url('/register') }}" novalidate>
    @csrf
    <label for="nombre">Nombres</label>
    <input id="nombre" name="nombre" value="{{ old('nombre') }}" maxlength="100" autocomplete="given-name" required autofocus>
    @error('nombre')<p class="error">{{ $message }}</p>@enderror
    <label for="apellido_paterno">Apellido paterno (opcional)</label>
    <input id="apellido_paterno" name="apellido_paterno" value="{{ old('apellido_paterno') }}" maxlength="100">
    @error('apellido_paterno')<p class="error">{{ $message }}</p>@enderror
    <label for="apellido_materno">Apellido materno (opcional)</label>
    <input id="apellido_materno" name="apellido_materno" value="{{ old('apellido_materno') }}" maxlength="100">
    @error('apellido_materno')<p class="error">{{ $message }}</p>@enderror
    <label for="email">Correo electrónico</label>
    <input id="email" name="email" type="email" value="{{ old('email') }}" required>
    @error('email')<p class="error">{{ $message }}</p>@enderror
    <label for="password">Contraseña (mínimo 8 caracteres)</label>
    <input id="password" name="password" type="password" required minlength="8">
    @error('password')<p class="error">{{ $message }}</p>@enderror
    <label for="password_confirmation">Repite la contraseña</label>
    <input id="password_confirmation" name="password_confirmation" type="password" required>
    <button class="btn" type="submit">Crear cuenta</button>
  </form>
  <p class="muted">¿Ya tienes cuenta? <a href="{{ route('login') }}">Inicia sesión</a></p>
</section>
@endsection