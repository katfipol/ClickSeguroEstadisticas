<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Url extends Model
{
    protected $table = 'urls';
    protected $fillable = ['dominio_id', 'url', 'url_hash'];

    public function dominio()
    {
        return $this->belongsTo(Dominio::class);
    }

    public function analisis()
    {
        return $this->hasMany(Analisis::class, 'url_id');
    }

    public function reportes()
    {
        return $this->hasMany(Reporte::class, 'url_id');
    }
}
