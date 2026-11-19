<?php

namespace App\Http\Controllers;

use App\Http\Requests\UrlRequest;
use App\Http\Resources\AnalisisResource;
use App\Services\AnalisisService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AnalisisController extends Controller
{
    public function __construct(private AnalisisService $servicio)
    {
    }

    /**
     * Lista paginada del historial de análisis del usuario (reemplaza dashboard y history).
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = min(max((int) $request->query('per_page', 15), 1), 50);

        $analisis = $request->user()->analisis()
            ->with(['url', 'urlFinal', 'tipoRiesgo', 'documento'])
            ->latest('id_analisis')
            ->paginate($perPage);

        return AnalisisResource::collection($analisis)
            ->additional(['status' => 'success'])
            ->response();
    }

    /**
     * Inicia un análisis de URL en segundo plano (HU-02, HU-18).
     */
    public function store(UrlRequest $request): JsonResponse
    {
        $analisis = $this->servicio->crearPendiente($request->user(), $request->url);

        return response()->json([
            'status'  => 'success',
            'message' => 'Análisis iniciado en segundo plano.',
            'data'    => new AnalisisResource($analisis),
        ], Response::HTTP_ACCEPTED); // 202
    }

    /**
     * Muestra el detalle completo de un análisis (HU-03 a HU-07).
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $analisis = $request->user()->analisis()
            ->with([
                'url.dominio',
                'urlFinal.dominio',
                'tipoRiesgo',
                'detalles.fuenteVerificacion',
                'anterior.tipoRiesgo',
                'anterior.detalles',
                'documento',
            ])
            ->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data'   => new AnalisisResource($analisis),
        ], Response::HTTP_OK);
    }

    /**
     * Consulta rápida de estado para polling desde el frontend (HU-18).
     */
    public function status(Request $request, int $id): JsonResponse
    {
        $analisis = $request->user()->analisis()->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data'   => [
                'id_analisis' => $analisis->id_analisis,
                'terminado'   => $analisis->estaTerminado(),
                'estado'      => $analisis->estado,
                'error'       => $analisis->error,
            ],
        ], Response::HTTP_OK);
    }

    /**
     * Solicita un nuevo análisis enlazado al análisis anterior (HU-07).
     */
    public function reanalyze(Request $request, int $id): JsonResponse
    {
        $anterior = $request->user()->analisis()->with('url')->findOrFail($id);
        $nuevo = $this->servicio->crearPendiente($request->user(), $anterior->url->url, $anterior);

        return response()->json([
            'status'  => 'success',
            'message' => 'Reanálisis iniciado en segundo plano.',
            'data'    => new AnalisisResource($nuevo),
        ], Response::HTTP_ACCEPTED); // 202
    }

    /**
     * Elimina un análisis del historial del usuario.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $analisis = $request->user()->analisis()->findOrFail($id);
        $analisis->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Análisis eliminado correctamente.',
        ], Response::HTTP_OK);
    }
}