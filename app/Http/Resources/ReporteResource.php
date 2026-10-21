<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ReporteResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id_reporte' => $this->id_reporte,
            'motivo'     => $this->motivo,
            'estado'     => $this->estado,
            'created_at' => $this->created_at?->toIso8601String(),
            'url'        => $this->whenLoaded('url', fn () => [
                'id_url' => $this->url->id_url,
                'url'    => $this->url->url,
            ]),
        ];
    }
}