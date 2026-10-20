<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AnalisisResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id_analisis' => $this->id_analisis,
            'estado'      => $this->estado,
            'terminado'   => $this->estaTerminado(),
            'error'       => $this->error,
            'created_at'  => $this->created_at?->toIso8601String(),

            'url' => $this->whenLoaded('url', fn () => [
                'id_url'  => $this->url->id_url,
                'url'     => $this->url->url,
                'dominio' => $this->url->relationLoaded('dominio') ? $this->url->dominio?->nombre : null,
            ]),

            'url_final' => $this->whenLoaded('urlFinal', fn () => $this->urlFinal ? [
                'id_url'  => $this->urlFinal->id_url,
                'url'     => $this->urlFinal->url,
                'dominio' => $this->urlFinal->relationLoaded('dominio') ? $this->urlFinal->dominio?->nombre : null,
            ] : null),

            'tipo_riesgo' => $this->whenLoaded('tipoRiesgo', fn () => $this->tipoRiesgo ? [
                'id_tipo_riesgo' => $this->tipoRiesgo->id_tipo_riesgo,
                'nombre'         => $this->tipoRiesgo->nombre,
            ] : null),

            'detecciones' => $this->whenLoaded('detalles', fn () => $this->detecciones),

            'detalles' => $this->whenLoaded('detalles', fn () => $this->detalles->map(fn ($d) => [
                'id_detalle'  => $d->id_detalle,
                'motor'       => $d->motor,
                'resultado'   => $d->resultado,
                'categoria'   => $d->categoria,
                'descripcion' => $d->descripcion,
                'fuente'      => $d->relationLoaded('fuenteVerificacion') ? $d->fuenteVerificacion?->nombre : null,
            ])),

            'analisis_anterior' => $this->whenLoaded('anterior', fn () => $this->anterior ? [
                'id_analisis' => $this->anterior->id_analisis,
                'created_at'  => $this->anterior->created_at?->toIso8601String(),
                'tipo_riesgo' => $this->anterior->relationLoaded('tipoRiesgo') ? $this->anterior->tipoRiesgo?->nombre : null,
                'detecciones' => $this->anterior->relationLoaded('detalles') ? $this->anterior->detecciones : null,
            ] : null),

            'documento' => $this->whenLoaded('documento', fn () => $this->documento ? [
                'id_documento' => $this->documento->id_documento,
                'nombre'       => $this->documento->nombre,
            ] : null),
        ];
    }
}