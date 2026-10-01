<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FuenteVerificacion extends Model
{
    protected $table = 'fuente_verificacions';
    protected $fillable = ['nombre'];

    public function detalles()
    {
        return $this->hasMany(DetalleAnalisis::class, 'fuente_verificacion_id');
    }
}
