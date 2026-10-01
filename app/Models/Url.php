<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Url extends Model
{
    protected $table = 'url';

    protected $primaryKey = 'id_url';

    protected $fillable = ['id_dominio', 'url', 'url_hash'];

    public function dominio()
    {
        return $this->belongsTo(Dominio::class, 'id_dominio', 'id_dominio');
    }

    public function analisis()
    {
        return $this->hasMany(Analisis::class, 'id_url', 'id_url');
    }

    public function reportes()
    {
        return $this->hasMany(Reporte::class, 'id_url', 'id_url');
    }
}