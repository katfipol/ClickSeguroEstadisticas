@extends('layouts.app')
@section('title', 'Reportar una URL')
@section('content')
<h1>Reportar una URL sospechosa</h1>
<section class="panel">
  <form method="POST" action="{{ route('reportes.store') }}" data-url-form novalidate>
    @csrf
    <label for="url">URL</label>
    <input id="url" name="url" class="mono" value="{{ old('url', request('url')) }}" required>
    <p class="error js-error" role="alert">{{ $errors->first('url') }}</p>
    <label for="motivo">Motivo (opcional)</label>
    <textarea id="motivo" name="motivo" rows="3" maxlength="500">{{ old('motivo') }}</textarea>
    @error('motivo')<p class="error">{{ $message }}</p>@enderror
    <button class="btn" type="submit">Enviar reporte</button>
  </form>
</section>
<section>
  <h2>Tus reportes recientes</h2>
  @forelse ($reportes as $r)
    <div class="item"><span class="badge">{{ ucfirst($r->estado) }}</span><span class="mono corto">{{ $r->url->url }}</span><span class="muted">{{ $r->created_at->format('d/m/Y H:i') }}</span></div>
  @empty
    <p class="muted">Todavía no reportaste ninguna URL.</p>
  @endforelse
</section>
@endsection
