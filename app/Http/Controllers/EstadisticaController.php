<?php

namespace App\Http\Controllers;

use App\Models\Analisis;
use Illuminate\Http\Request;

class EstadisticaController extends Controller
{
    public function index(Request $request)
    {
        $idUsuario = $request->user()->getKey();
        // Todo se calcula con consultas sobre las tablas existentes; no hay tabla de estadísticas.
        $porRiesgo = Analisis::where('analisis.id_usuario', $idUsuario)
            ->join('tipo_riesgo', 'tipo_riesgo.id_tipo_riesgo', '=', 'analisis.id_tipo_riesgo')
            ->selectRaw('tipo_riesgo.nombre as nombre, COUNT(*) as total')
            ->groupBy('tipo_riesgo.nombre')
            ->pluck('total', 'nombre');

        $stats = [
            'total'       => Analisis::where('id_usuario', $idUsuario)->count(),
            'seguros'     => (int) ($porRiesgo['Seguro'] ?? 0),
            'sospechosos' => (int) ($porRiesgo['Sospechoso'] ?? 0),
            'peligrosos'  => (int) ($porRiesgo['Peligroso'] ?? 0),
            'reportes'    => $request->user()->reportes()->count(),
        ];
        return view('estadisticas', compact('stats'));
    }
}