<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Analisis extends Model
{
    // Un reanálisis genera otro registro, sin sobrescribir el resultado anterior.
    const UPDATED_AT = null;

    protected $table = 'analisis';

    protected $primaryKey = 'id_analisis';

    protected $fillable = [
        'id_usuario',
        'id_url',
        'id_url_final',
        'id_tipo_riesgo',
        'id_analisis_anterior',
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }

    public function url()
    {
        return $this->belongsTo(Url::class, 'id_url', 'id_url');
    }

    public function urlFinal()
    {
        return $this->belongsTo(Url::class, 'id_url_final', 'id_url');
    }

    public function tipoRiesgo()
    {
        return $this->belongsTo(TipoRiesgo::class, 'id_tipo_riesgo', 'id_tipo_riesgo');
    }

    public function anterior()
    {
        return $this->belongsTo(Analisis::class, 'id_analisis_anterior', 'id_analisis');
    }

    public function detalles()
    {
        return $this->hasMany(DetalleAnalisis::class, 'id_analisis', 'id_analisis');
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