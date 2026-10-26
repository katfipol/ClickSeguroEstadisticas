<!doctype html>
<html lang="es"><head><meta charset="utf-8">
<style>
 body{font-family:DejaVu Sans,sans-serif;font-size:11px;color:#1b2733}
 h1{font-size:18px;margin:0 0 4px} h2{font-size:13px;margin:16px 0 6px}
 .nivel{font-size:26px;font-weight:bold;margin:10px 0}
 table{width:100%;border-collapse:collapse} th,td{border:1px solid #c9d3da;padding:4px 6px;text-align:left;vertical-align:top} th{background:#dce6f0}
 .k{width:28%;font-weight:bold;background:#f2f5f8}
 .pie{margin-top:24px;font-size:9px;color:#5b6b78}
</style></head><body>
<h1>Click Seguro: Informe de análisis</h1>
<div>Código de informe: <strong>{{ $codigo }}</strong></div>
<div class="nivel">{{ strtoupper($analisis->tipoRiesgo->nombre) }}</div>
<table>
  <tr><td class="k">URL analizada</td><td>{{ $analisis->urlEvaluada()->url }}</td></tr>
  @if ($analisis->urlFinal)
    <tr><td class="k">URL original (acortada)</td><td>{{ $analisis->url->url }}</td></tr>
    <tr><td class="k">URL final</td><td>{{ $analisis->urlFinal->url }}</td></tr>
  @endif
  <tr><td class="k">Dominio</td><td>{{ $analisis->urlEvaluada()->dominio->nombre }}</td></tr>
  <tr><td class="k">Fecha del análisis</td><td>{{ $analisis->created_at->format('d/m/Y H:i') }}</td></tr>
  <tr><td class="k">Detecciones</td><td>{{ $analisis->detecciones }} motores</td></tr>
  <tr><td class="k">Fuentes</td><td>{{ $analisis->detalles->pluck('fuenteVerificacion.nombre')->unique()->implode(', ') }}</td></tr>
</table>
<h2>Razones del resultado</h2>
<table>
  <tr><th>Fuente</th><th>Motor</th><th>Resultado</th><th>Categoría</th></tr>
  @foreach ($analisis->detalles as $d)
    <tr><td>{{ $d->fuenteVerificacion->nombre }}</td><td>{{ $d->motor ?? '—' }}</td><td>{{ ucfirst($d->resultado) }}</td><td>{{ $d->categoria ?? '—' }}</td></tr>
  @endforeach
</table>
@if ($analisis->anterior && $analisis->anterior->tipoRiesgo)
  <h2>Comparación con el análisis anterior</h2>
  <p>{{ $analisis->anterior->created_at->format('d/m/Y H:i') }}: {{ $analisis->anterior->tipoRiesgo->nombre }} ({{ $analisis->anterior->detecciones }} detecciones) → ahora: {{ $analisis->tipoRiesgo->nombre }} ({{ $analisis->detecciones }} detecciones).</p>
@endif
<p class="pie">Este resultado corresponde únicamente a la fecha indicada; el nivel de riesgo de una URL puede cambiar con el tiempo. Generado por Click Seguro el {{ now()->format('d/m/Y H:i') }}.</p>
</body></html>
