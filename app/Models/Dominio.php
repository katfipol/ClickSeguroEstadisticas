<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dominio extends Model
{
    protected $table = 'dominio';

    protected $primaryKey = 'id_dominio';

    protected $fillable = [
        'nombre',
        'fecha_creacion',
        'registrador',
        'ip',
        'pais',
        'reputacion',
        'consultado_at',
    ];

    public function getIdAttribute()
    {
        return $this->getKey();
    }

    protected $casts = [
        'fecha_creacion' => 'date',
        'consultado_at'  => 'datetime',
    ];

    public function urls()
    {
        return $this->hasMany(Url::class, 'id_dominio', 'id_dominio');
    }
}
