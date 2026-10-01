<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetalleAnalisis extends Model
{
    protected $table = 'detalle_analisis';
    protected $fillable = ['analisis_id', 'fuente_verificacion_id', 'motor', 'resultado', 'categoria', 'descripcion'];

    public function analisis()
    {
        return $this->belongsTo(Analisis::class, 'analisis_id');
    }

    public function fuenteVerificacion()
    {
        return $this->belongsTo(FuenteVerificacion::class, 'fuente_verificacion_id');
    }
}
