<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Documento extends Model
{
    protected $table = 'documento';

    protected $primaryKey = 'id_documento';

    protected $fillable = [
        'id_usuario',
        'nombre',
        'total_enlaces',
        'enlaces_omitidos',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }

    public function analisis(): HasMany
    {
        return $this->hasMany(Analisis::class, 'id_documento', 'id_documento');
    }
}