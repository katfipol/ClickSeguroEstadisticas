<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Analisis extends Model
{
    const UPDATED_AT = null;

    protected $table = 'analisis';
    protected $fillable = ['usuario_id', 'url_id', 'url_final_id', 'tipo_riesgo_id', 'analisis_anterior_id'];

    public function usuario()    { return $this->belongsTo(Usuario::class, 'usuario_id'); }
    public function url()        { return $this->belongsTo(Url::class, 'url_id'); }
    public function urlFinal()   { return $this->belongsTo(Url::class, 'url_final_id'); }
    public function tipoRiesgo() { return $this->belongsTo(TipoRiesgo::class, 'tipo_riesgo_id'); }
    public function anterior()   { return $this->belongsTo(Analisis::class, 'analisis_anterior_id'); }
    public function detalles()   { return $this->hasMany(DetalleAnalisis::class, 'analisis_id'); }

    /** URL realmente evaluada por las fuentes: la final si hubo acortador, si no la original. */
    public function urlEvaluada(): Url
    {
        return $this->urlFinal ?? $this->url;
    }

    /** Motores que detectaron la URL (filas con motor; el resumen "limpio" tiene motor nulo). */
    public function getDeteccionesAttribute(): int
    {
        return $this->detalles->whereNotNull('motor')->count();
    }
}
