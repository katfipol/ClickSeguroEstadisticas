<?php

namespace App\Http\Controllers;

use App\Models\Analisis;
use Illuminate\Http\Request;

class EstadisticaController extends Controller
{
    public function index(Request $request)
    {
        $uid = $request->user()->id;
        // Todo se calcula con consultas sobre las tablas existentes; no hay tabla de estadísticas.
        $porRiesgo = Analisis::where('analisis.usuario_id', $uid)
            ->join('tipo_riesgos', 'tipo_riesgos.id', '=', 'analisis.tipo_riesgo_id')
            ->selectRaw('tipo_riesgos.nombre as nombre, COUNT(*) as total')
            ->groupBy('tipo_riesgos.nombre')
            ->pluck('total', 'nombre');

        $stats = [
            'total'       => Analisis::where('usuario_id', $uid)->count(),
            'seguros'     => (int) ($porRiesgo['Seguro'] ?? 0),
            'sospechosos' => (int) ($porRiesgo['Sospechoso'] ?? 0),
            'peligrosos'  => (int) ($porRiesgo['Peligroso'] ?? 0),
            'reportes'    => $request->user()->reportes()->count(),
        ];
        return view('estadisticas', compact('stats'));
    }
}
