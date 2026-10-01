<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dominio extends Model
{
    protected $table = 'dominios';
    protected $fillable = ['nombre'];

    public function urls()
    {
        return $this->hasMany(Url::class);
    }
}
