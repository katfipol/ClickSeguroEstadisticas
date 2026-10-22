<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ArchivoVerificado extends Model
{
    const UPDATED_AT = null;

    protected $table = 'archivo_verificado';

    protected $primaryKey = 'id_archivo';

    protected $fillable = [
        'id_usuario',
        'nombre',
        'tamano',
        'sha256',
        'id_tipo_riesgo',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }

    public function tipoRiesgo(): BelongsTo
    {
        return $this->belongsTo(TipoRiesgo::class, 'id_tipo_riesgo', 'id_tipo_riesgo');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(DetalleArchivo::class, 'id_archivo', 'id_archivo');
    }

    public function getDeteccionesAttribute(): int
    {
        return $this->detalles->whereNotNull('motor')->count();
    }
}