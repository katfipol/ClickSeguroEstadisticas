<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ArchivoVerificadoResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id_archivo'  => $this->id_archivo,
            'nombre'      => $this->nombre,
            'tamano'      => (int) $this->tamano,
            'sha256'      => $this->sha256,
            'conocido'    => $this->id_tipo_riesgo !== null,
            'created_at'  => $this->created_at?->toIso8601String(),

            'tipo_riesgo' => $this->whenLoaded('tipoRiesgo', fn () => $this->tipoRiesgo ? [
                'id_tipo_riesgo' => $this->tipoRiesgo->id_tipo_riesgo,
                'nombre'         => $this->tipoRiesgo->nombre,
            ] : null),

            'detecciones' => $this->whenLoaded('detalles', fn () => $this->detecciones),

            'detalles' => $this->whenLoaded('detalles', fn () => $this->detalles->map(fn ($d) => [
                'id_detalle_archivo' => $d->id_detalle_archivo,
                'motor'              => $d->motor,
                'resultado'          => $d->resultado,
                'categoria'          => $d->categoria,
                'fuente'             => $d->relationLoaded('fuenteVerificacion') ? $d->fuenteVerificacion?->nombre : null,
            ])),
        ];
    }
}