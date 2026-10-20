<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FuenteVerificacion extends Model
{
    protected $table = 'fuente_verificacion';

    protected $primaryKey = 'id_fuente';

    protected $fillable = ['nombre'];

    public function detalles()
    {
        return $this->hasMany(DetalleAnalisis::class, 'id_fuente', 'id_fuente');
    }
}