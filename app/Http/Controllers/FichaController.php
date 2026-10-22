<?php

namespace App\Http\Controllers;

use App\Exceptions\AnalisisException;
use App\Http\Resources\AnalisisResource;
use App\Services\FichaDominioService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** HU-12: Consulta de ficha técnica de dominio o dirección IP */
class FichaController extends Controller
{
    public function __construct(private FichaDominioService $servicio)
    {
    }

    public function show(Request $request): JsonResponse
    {
        $nombre = trim((string) $request->query('nombre', ''));
        if ($nombre === '') {
            throw new AnalisisException('Ingresa un dominio o dirección IP para consultar.');
        }

        $ficha = $this->servicio->obtener($nombre);

        $previos = collect();
        if ($ficha['tipo'] === 'dominio') {
            $previos = $this->servicio->analisisPrevios($request->user(), $ficha['dominio']->id_dominio);
        }

        return response()->json([
            'status' => 'success',
            'data'   => [
                'consulta' => $nombre,
                'tipo'     => $ficha['tipo'],
                'dominio'  => $ficha['tipo'] === 'dominio' ? [
                    'id_dominio'     => $ficha['dominio']->id_dominio,
                    'nombre'         => $ficha['dominio']->nombre,
                    'fecha_creacion' => $ficha['dominio']->fecha_creacion,
                    'registrador'    => $ficha['dominio']->registrador,
                    'ip'             => $ficha['dominio']->ip,
                    'pais'           => $ficha['dominio']->pais,
                    'reputacion'     => $ficha['dominio']->reputacion,
                    'consultado_at'  => $ficha['dominio']->consultado_at?->toIso8601String(),
                ] : null,
                'ip_info'  => $ficha['tipo'] === 'ip' ? [
                    'ip'          => $ficha['ip'],
                    'pais'        => $ficha['datos']['pais'] ?? null,
                    'propietario' => $ficha['datos']['propietario'] ?? null,
                    'reputacion'  => $ficha['datos']['reputacion'] ?? null,
                ] : null,
                'analisis_previos' => AnalisisResource::collection($previos),
            ],
        ], Response::HTTP_OK);
    }
}