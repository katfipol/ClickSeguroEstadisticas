<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Analisis extends Model
{
    const UPDATED_AT = null;

    protected $table = 'analisis';

    protected $primaryKey = 'id_analisis';

    protected $fillable = [
        'id_usuario',
        'id_url',
        'id_url_final',
        'id_tipo_riesgo',
        'id_analisis_anterior',
        'id_documento',
        'estado',
        'error',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }

    public function url(): BelongsTo
    {
        return $this->belongsTo(Url::class, 'id_url', 'id_url');
    }

    public function urlFinal(): BelongsTo
    {
        return $this->belongsTo(Url::class, 'id_url_final', 'id_url');
    }

    public function tipoRiesgo(): BelongsTo
    {
        return $this->belongsTo(TipoRiesgo::class, 'id_tipo_riesgo', 'id_tipo_riesgo');
    }

    public function anterior(): BelongsTo
    {
        return $this->belongsTo(Analisis::class, 'id_analisis_anterior', 'id_analisis');
    }

    public function documento(): BelongsTo
    {
        return $this->belongsTo(Documento::class, 'id_documento', 'id_documento');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(DetalleAnalisis::class, 'id_analisis', 'id_analisis');
    }

    public function estaTerminado(): bool
    {
        return in_array($this->estado, ['completado', 'fallido'], true);
    }

    public function urlEvaluada(): Url
    {
        return $this->urlFinal ?? $this->url;
    }

    public function getDeteccionesAttribute(): int
    {
        return $this->detalles->whereNotNull('motor')->count();
    }
}