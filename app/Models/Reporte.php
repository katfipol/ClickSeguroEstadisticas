<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reporte extends Model
{
    protected $table = 'reportes';
    protected $fillable = ['usuario_id', 'url_id', 'motivo', 'estado'];

    public function usuario() { return $this->belongsTo(Usuario::class, 'usuario_id'); }
    public function url()     { return $this->belongsTo(Url::class, 'url_id'); }
}
