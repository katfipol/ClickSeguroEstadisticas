@extends('layouts.app')
@section('title', 'Analizar una URL')
@section('content')
<h1>Hola, {{ auth()->user()->nombre_completo }}</h1>
<p class="muted">Pega un enlace para saber si es seguro antes de abrirlo. Nunca lo abrimos por ti.</p>

<section class="panel">
  <form method="POST" action="{{ route('analisis.store') }}" data-url-form novalidate>
    @csrf
    <label for="url">URL a analizar</label>
    <div class="fila">
      <input id="url" name="url" class="mono" value="{{ old('url') }}" placeholder="https://ejemplo.com/ruta" autocomplete="off" required>
      <button class="btn sec" type="button" data-paste>Pegar</button>
    </div>
    <p class="error js-error" role="alert">{{ $errors->first('url') }}</p>
    <button class="btn" type="submit">Analizar</button>
  </form>
</section>

<section class="panel">
  <h2>¿Tienes un código QR?</h2>
  <p class="muted">Sube la imagen. El QR se lee en tu navegador y la imagen no se envía al servidor.</p>
  <input id="qr-file" type="file" accept="image/png,image/jpeg,image/webp" aria-describedby="qr-msg">
  <p id="qr-msg" class="muted" role="status"></p>
</section>

<section>
  <h2>Análisis recientes</h2>
  @forelse ($recientes as $a)
    <a class="item" href="{{ route('analisis.show', $a->id) }}">
      <span class="badge riesgo-{{ strtolower($a->tipoRiesgo->nombre) }}">{{ $a->tipoRiesgo->nombre }}</span>
      <span class="mono corto">{{ $a->url->url }}</span>
      <span class="muted">{{ $a->created_at->format('d/m/Y H:i') }}</span>
    </a>
  @empty
    <p class="muted">Aún no analizaste ninguna URL. Pega la primera arriba.</p>
  @endforelse
</section>
@endsection