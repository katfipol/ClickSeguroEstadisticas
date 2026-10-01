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
  @foreach ($analisis as $registroAnalisis)
    <tr>
      <td class="mono corto">{{ $registroAnalisis->url->url }}@if($registroAnalisis->urlFinal)<br><span class="muted">→ {{ $registroAnalisis->urlFinal->url }}</span>@endif</td>
      <td>{{ $registroAnalisis->created_at->format('d/m/Y H:i') }}</td>
      <td><span class="badge riesgo-{{ strtolower($registroAnalisis->tipoRiesgo->nombre) }}">{{ $registroAnalisis->tipoRiesgo->nombre }}</span></td>
      <td class="acciones-fila">
        <a href="{{ route('analisis.show', $registroAnalisis->getKey()) }}">Ver</a>
        <form method="POST" action="{{ route('analisis.reanalizar', $registroAnalisis->getKey()) }}">@csrf<button class="link" type="submit">Reanalizar</button></form>
        <form method="POST" action="{{ route('analisis.destroy', $registroAnalisis->getKey()) }}" data-confirm="¿Eliminar este análisis? No se puede deshacer.">@csrf @method('DELETE')<button class="link peligro" type="submit">Eliminar</button></form>
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
