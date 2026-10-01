<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoRiesgo extends Model
{
    protected $table = 'tipo_riesgos';
    protected $fillable = ['nombre', 'descripcion'];

    public function analisis()
    {
        return $this->hasMany(Analisis::class, 'tipo_riesgo_id');
    }
}
