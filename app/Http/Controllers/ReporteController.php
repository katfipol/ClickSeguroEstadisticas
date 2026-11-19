<?php

namespace App\Http\Controllers;

use App\Exceptions\AnalisisException;
use App\Http\Requests\ReporteRequest;
use App\Http\Resources\ReporteResource;
use App\Services\AnalisisService;
use App\Services\UrlService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** HU-09: Reporte de URLs sospechosas por parte de los usuarios */
class ReporteController extends Controller
{
    public function __construct(
        private UrlService $urls,
        private AnalisisService $analisisService
    ) {
    }

    /**
     * Lista los últimos reportes realizados por el usuario autenticado.
     */
    public function index(Request $request): JsonResponse
    {
        $reportes = $request->user()->reportes()
            ->with('url')
            ->latest('id_reporte')
            ->limit(15)
            ->get();

        return response()->json([
            'status' => 'success',
            'data'   => ReporteResource::collection($reportes),
        ], Response::HTTP_OK);
    }

    /**
     * Registra un nuevo reporte de URL (HU-09).
     */
    public function store(ReporteRequest $request): JsonResponse
    {
        $url = $this->analisisService->registrarUrl(
            $this->urls->normalizar($request->url)
        );

        if ($request->user()->reportes()->where('id_url', $url->id_url)->exists()) {
            throw new AnalisisException('Ya reportaste esta URL anteriormente.');
        }

        $reporte = $request->user()->reportes()->create([
            'id_url' => $url->id_url,
            'motivo' => $request->motivo,
            'estado' => 'pendiente',
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Reporte registrado. Gracias por ayudar.',
            'data'    => new ReporteResource($reporte->load('url')),
        ], Response::HTTP_CREATED); // 201
    }
}