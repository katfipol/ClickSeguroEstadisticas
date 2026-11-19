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
<h1>Click Seguro: Informe de verificación de archivo</h1>
<div>Código de informe: <strong>{{ $codigo }}</strong></div>
<div class="nivel">{{ $archivo->tipoRiesgo ? strtoupper($archivo->tipoRiesgo->nombre) : 'SIN INFORMACIÓN' }}</div>
<table>
  <tr><td class="k">Archivo</td><td>{{ $archivo->nombre }}</td></tr>
  <tr><td class="k">Tamaño</td><td>{{ number_format($archivo->tamano / 1024, 1) }} KB</td></tr>
  <tr><td class="k">Huella SHA-256</td><td>{{ $archivo->sha256 }}</td></tr>
  <tr><td class="k">Fecha</td><td>{{ $archivo->created_at->format('d/m/Y H:i') }}</td></tr>
  @if ($archivo->tipoRiesgo)<tr><td class="k">Detecciones</td><td>{{ $archivo->detecciones }} motores</td></tr>@endif
</table>
@if ($archivo->detalles->isNotEmpty())
<h2>Razones del resultado</h2>
<table>
  <tr><th>Fuente</th><th>Motor</th><th>Resultado</th><th>Categoría</th></tr>
  @foreach ($archivo->detalles as $d)
    <tr><td>{{ $d->fuenteVerificacion->nombre }}</td><td>{{ $d->motor ?? '—' }}</td><td>{{ ucfirst($d->resultado) }}</td><td>{{ $d->categoria ?? '—' }}</td></tr>
  @endforeach
</table>
@else
<p>VirusTotal no tiene información de este archivo. Eso no significa que sea seguro.</p>
@endif
<p class="pie">El archivo no fue subido a ningún servicio: solo se consultó su huella. Resultado válido para la fecha indicada. Generado por Click Seguro el {{ now()->format('d/m/Y H:i') }}.</p>
</body></html>
