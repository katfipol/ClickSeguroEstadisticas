<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoRiesgo extends Model
{
    protected $table = 'tipo_riesgo';

    protected $primaryKey = 'id_tipo_riesgo';

    protected $fillable = ['nombre', 'descripcion'];

    public function analisis()
    {
        return $this->hasMany(Analisis::class, 'id_tipo_riesgo', 'id_tipo_riesgo');
    }
}