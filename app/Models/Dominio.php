<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dominio extends Model
{
    protected $table = 'dominio';

    protected $primaryKey = 'id_dominio';

    protected $fillable = ['nombre'];

    public function urls()
    {
        return $this->hasMany(Url::class, 'id_dominio', 'id_dominio');
    }
}