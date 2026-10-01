<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reporte extends Model
{
    protected $table = 'reporte';

    protected $primaryKey = 'id_reporte';

    protected $fillable = ['id_usuario', 'id_url', 'motivo', 'estado'];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }

    public function url()
    {
        return $this->belongsTo(Url::class, 'id_url', 'id_url');
    }
}