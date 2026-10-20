@extends('layouts.app')
@section('title', 'Estadísticas')
@section('content')
<h1>Tus estadísticas</h1>
<dl class="cifras">
  <div><dt>Total analizados</dt><dd>{{ $stats['total'] }}</dd></div>
  <div class="riesgo-seguro"><dt>Seguros</dt><dd>{{ $stats['seguros'] }}</dd></div>
  <div class="riesgo-sospechoso"><dt>Sospechosos</dt><dd>{{ $stats['sospechosos'] }}</dd></div>
  <div class="riesgo-peligroso"><dt>Peligrosos</dt><dd>{{ $stats['peligrosos'] }}</dd></div>
  <div><dt>Reportados</dt><dd>{{ $stats['reportes'] }}</dd></div>
</dl>
@endsection
