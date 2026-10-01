@extends('layouts.app')
@section('title', 'Resultado del análisis')
@section('content')
@php($nivel = strtolower($analisis->tipoRiesgo->nombre))
@php($urlEvaluada = $analisis->urlEvaluada())
<section class="veredicto riesgo-{{ $nivel }}">
  <p class="nivel">{{ strtoupper($analisis->tipoRiesgo->nombre) }}</p>
  <p>{{ $analisis->tipoRiesgo->descripcion }}</p>
</section>

@if ($errors->any())<p class="error" role="alert">{{ $errors->first() }}</p>@endif

<section class="panel">
  <dl class="datos">
    <dt>URL analizada</dt><dd class="mono">{{ $urlEvaluada->url }}</dd>
    @if ($analisis->urlFinal)
      <dt>URL original (acortada)</dt><dd class="mono">{{ $analisis->url->url }}</dd>
      <dt>URL final</dt><dd class="mono">{{ $analisis->urlFinal->url }}</dd>
    @endif
    <dt>Dominio</dt><dd>{{ $urlEvaluada->dominio->nombre }}</dd>
    <dt>Fecha</dt><dd>{{ $analisis->created_at->format('d/m/Y H:i') }}</dd>
    <dt>Detecciones</dt><dd>{{ $analisis->detecciones }} {{ $analisis->detecciones === 1 ? 'motor' : 'motores' }}</dd>
  </dl>
</section>

<section class="panel">
  <h2>Por qué este resultado</h2>
  <div class="tabla-scroll">
    <table>
      <thead><tr><th>Fuente</th><th>Motor</th><th>Resultado</th><th>Categoría</th><th>Detalle</th></tr></thead>
      <tbody>
      @foreach ($analisis->detalles as $detalle)
        <tr>
          <td>{{ $detalle->fuenteVerificacion->nombre }}</td>
          <td>{{ $detalle->motor ?? '—' }}</td>
          <td>{{ ucfirst($detalle->resultado) }}</td>
          <td>{{ $detalle->categoria ?? '—' }}</td>
          <td>{{ $detalle->descripcion }}</td>
        </tr>
      @endforeach
      </tbody>
    </table>
  </div>
</section>

@if ($analisis->anterior)
  @php($analisisAnterior = $analisis->anterior)
  <section class="panel">
    <h2>Comparación con el análisis anterior</h2>
    <p>
      {{ $analisisAnterior->created_at->format('d/m/Y H:i') }}:
      <span class="badge riesgo-{{ strtolower($analisisAnterior->tipoRiesgo->nombre) }}">{{ $analisisAnterior->tipoRiesgo->nombre }}</span>
      ({{ $analisisAnterior->detecciones }} detecciones)
      → ahora:
      <span class="badge riesgo-{{ $nivel }}">{{ $analisis->tipoRiesgo->nombre }}</span>
      ({{ $analisis->detecciones }} detecciones).
      {{ $analisisAnterior->id_tipo_riesgo === $analisis->id_tipo_riesgo ? 'El nivel de riesgo no cambió.' : 'El nivel de riesgo cambió.' }}
    </p>
    <a href="{{ route('analisis.show', $analisisAnterior->getKey()) }}">Ver el análisis anterior</a>
  </section>
@endif

<div class="acciones">
  <form method="POST" action="{{ route('analisis.reanalizar', $analisis->getKey()) }}">@csrf<button class="btn" type="submit">Reanalizar</button></form>
  <a class="btn sec" href="{{ route('reportes.create', ['url' => $urlEvaluada->url]) }}">Reportar</a>
  <a class="btn sec" href="{{ route('historial') }}">Volver al historial</a>
</div>
@endsection