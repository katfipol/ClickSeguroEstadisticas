<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetalleArchivo extends Model
{
    protected $table = 'detalle_archivo';

    protected $primaryKey = 'id_detalle_archivo';

    protected $fillable = [
        'id_archivo',
        'id_fuente',
        'motor',
        'resultado',
        'categoria',
    ];

    public function archivo()
    {
        return $this->belongsTo(ArchivoVerificado::class, 'id_archivo', 'id_archivo');
    }

    public function fuenteVerificacion()
    {
        return $this->belongsTo(FuenteVerificacion::class, 'id_fuente', 'id_fuente');
    }
}
