<?php

namespace App\Http\Controllers;

use App\Http\Requests\ArchivoRequest;
use App\Http\Resources\ArchivoVerificadoResource;
use App\Services\ArchivoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** HU-11: Verificación de archivos por huella SHA-256 */
class ArchivoController extends Controller
{
    public function __construct(private ArchivoService $servicio)
    {
    }

    /**
     * Lista paginada de archivos verificados por el usuario.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = min(max((int) $request->query('per_page', 15), 1), 50);

        $archivos = $request->user()->archivosVerificados()
            ->with('tipoRiesgo')
            ->latest('id_archivo')
            ->paginate($perPage);

        return ArchivoVerificadoResource::collection($archivos)
            ->additional(['status' => 'success'])
            ->response();
    }

    /**
     * Verifica la huella SHA-256 de un archivo y registra el resultado (HU-11).
     */
    public function store(ArchivoRequest $request): JsonResponse
    {
        $archivo = $this->servicio->verificar(
            $request->user(),
            $request->sha256,
            $request->nombre,
            (int) $request->tamano
        );

        return response()->json([
            'status'  => 'success',
            'message' => 'Archivo verificado correctamente.',
            'data'    => new ArchivoVerificadoResource($archivo),
        ], Response::HTTP_CREATED); // 201
    }

    /**
     * Muestra el detalle de un archivo verificado.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $archivo = $request->user()->archivosVerificados()
            ->with(['tipoRiesgo', 'detalles.fuenteVerificacion'])
            ->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data'   => new ArchivoVerificadoResource($archivo),
        ], Response::HTTP_OK);
    }

    /**
  * Elimina un archivo verificado del historial del usuario.
  */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $archivo = $request->user()->archivosVerificados()->findOrFail($id);
        $archivo->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Archivo eliminado correctamente.',
        ], Response::HTTP_OK);
    }
}