<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetalleAnalisis extends Model
{
    protected $table = 'detalle_analisis';

    protected $primaryKey = 'id_detalle';

    protected $fillable = [
        'id_analisis',
        'id_fuente',
        'motor',
        'resultado',
        'categoria',
        'descripcion',
    ];

    public function analisis()
    {
        return $this->belongsTo(Analisis::class, 'id_analisis', 'id_analisis');
    }

    public function fuenteVerificacion()
    {
        return $this->belongsTo(FuenteVerificacion::class, 'id_fuente', 'id_fuente');
    }
}