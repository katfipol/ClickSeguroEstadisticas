<?php

namespace App\Http\Controllers;

use App\Models\Analisis;
use App\Models\DetalleAnalisis;
use App\Models\Reporte;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EstadisticaController extends Controller
{
    /** HU-10: Estadísticas personales del usuario autenticado. */
    public function personal(Request $request): JsonResponse
    {
        $uid = $request->user()->id_usuario;
        $porRiesgo = Analisis::where('analisis.id_usuario', $uid)
            ->join('tipo_riesgo', 'tipo_riesgo.id_tipo_riesgo', '=', 'analisis.id_tipo_riesgo')
            ->selectRaw('tipo_riesgo.nombre as nombre, COUNT(*) as total')
            ->groupBy('tipo_riesgo.nombre')
            ->pluck('total', 'nombre');

        return response()->json([
            'status' => 'success',
            'data'   => [
                'total'       => (int) $porRiesgo->sum(),
                'seguros'     => (int) ($porRiesgo['Seguro'] ?? 0),
                'sospechosos' => (int) ($porRiesgo['Sospechoso'] ?? 0),
                'peligrosos'  => (int) ($porRiesgo['Peligroso'] ?? 0),
                'reportes'    => $request->user()->reportes()->count(),
            ],
        ], Response::HTTP_OK);
    }

    /** HU-16: Estadísticas globales agregadas del sitio. */
    public function global(Request $request): JsonResponse
    {
        $dias = in_array((int) $request->query('dias'), [7, 30, 90], true) ? (int) $request->query('dias') : 30;
        $desde = now()->subDays($dias - 1)->startOfDay();
        $base = fn () => Analisis::where('analisis.estado', 'completado')->where('analisis.created_at', '>=', $desde);

        $porRiesgo = $base()
            ->join('tipo_riesgo', 'tipo_riesgo.id_tipo_riesgo', '=', 'analisis.id_tipo_riesgo')
            ->selectRaw('tipo_riesgo.nombre as nombre, COUNT(*) as total')
            ->groupBy('tipo_riesgo.nombre')->pluck('total', 'nombre');
        $total = (int) $porRiesgo->sum();

        $porDia = $base()->selectRaw('DATE(analisis.created_at) as dia, COUNT(*) as total')
            ->groupBy('dia')->pluck('total', 'dia');
        $etiquetas = [];
        $valores = [];
        for ($i = $dias - 1; $i >= 0; $i--) {
            $d = now()->subDays($i)->toDateString();
            $etiquetas[] = substr($d, 5);
            $valores[] = (int) ($porDia[$d] ?? 0);
        }

        $activos = $base()->distinct()->count('analisis.id_usuario');

        $dominiosReportados = Reporte::where('reporte.created_at', '>=', $desde)
            ->join('url', 'url.id_url', '=', 'reporte.id_url')
            ->join('dominio', 'dominio.id_dominio', '=', 'url.id_dominio')
            ->selectRaw('dominio.nombre as nombre, COUNT(*) as total')
            ->groupBy('dominio.nombre')->havingRaw('COUNT(*) >= 2')
            ->orderByDesc('total')->limit(10)->get();

        $registrosAmenazas = DetalleAnalisis::join('analisis', 'analisis.id_analisis', '=', 'detalle_analisis.id_analisis')
            ->where('analisis.created_at', '>=', $desde)
            ->whereNotNull('detalle_analisis.categoria')
            ->whereNotIn('detalle_analisis.categoria', [
                'clean', 'unrated', 'harmless', 'undetected',
                'Limpio', 'Inofensivo / Seguro', 'Sin detecciones', 'Sin calificar', '—'
            ])
            ->select('detalle_analisis.id_analisis', 'detalle_analisis.categoria')
            ->get();

        $amenazas = $registrosAmenazas
            ->groupBy(fn ($item) => Analisis::traducirTermino($item->categoria))
            ->map(fn ($grupo, $categoriaTraducida) => [
                'categoria' => $categoriaTraducida,
                'total'     => $grupo->pluck('id_analisis')->unique()->count(),
            ])
            ->sortByDesc('total')
            ->take(8)
            ->values();
            
        return response()->json([
            'status' => 'success',
            'data'   => [
                'dias'                => $dias,
                'total'               => $total,
                'por_riesgo'          => $porRiesgo,
                'etiquetas'           => $etiquetas,
                'valores'             => $valores,
                'activos'             => $activos,
                'dominios_reportados' => $dominiosReportados,
                'amenazas'            => $amenazas,
            ],
        ], Response::HTTP_OK);
    }
}