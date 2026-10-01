@extends('layouts.app')
@section('title', 'Historial')
@section('content')
<h1>Historial</h1>
@if ($analisis->isEmpty() && $analisis->currentPage() === 1)
  <p class="muted">No hay análisis todavía. <a href="{{ route('dashboard') }}">Analiza tu primera URL</a>.</p>
@else
<div class="tabla-scroll">
<table>
  <thead><tr><th>URL</th><th>Fecha</th><th>Nivel de riesgo</th><th>Acciones</th></tr></thead>
  <tbody>
  @foreach ($analisis as $a)
    <tr>
      <td class="mono corto">{{ $a->url->url }}@if($a->urlFinal)<br><span class="muted">→ {{ $a->urlFinal->url }}</span>@endif</td>
      <td>{{ $a->created_at->format('d/m/Y H:i') }}</td>
      <td><span class="badge riesgo-{{ strtolower($a->tipoRiesgo->nombre) }}">{{ $a->tipoRiesgo->nombre }}</span></td>
      <td class="acciones-fila">
        <a href="{{ route('analisis.show', $a->id) }}">Ver</a>
        <form method="POST" action="{{ route('analisis.reanalizar', $a->id) }}">@csrf<button class="link" type="submit">Reanalizar</button></form>
        <form method="POST" action="{{ route('analisis.destroy', $a->id) }}" data-confirm="¿Eliminar este análisis? No se puede deshacer.">@csrf @method('DELETE')<button class="link peligro" type="submit">Eliminar</button></form>
      </td>
    </tr>
  @endforeach
  </tbody>
</table>
</div>
<p class="paginas">
  @if ($analisis->previousPageUrl())<a href="{{ $analisis->previousPageUrl() }}">Anteriores</a>@endif
  @if ($analisis->nextPageUrl())<a href="{{ $analisis->nextPageUrl() }}">Siguientes</a>@endif
</p>
@endif
@endsection
