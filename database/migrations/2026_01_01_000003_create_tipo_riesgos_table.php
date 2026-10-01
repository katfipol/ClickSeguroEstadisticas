<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tipo_riesgo', function (Blueprint $tabla) {
            $tabla->id('id_tipo_riesgo');
            $tabla->string('nombre', 30)->unique();
            $tabla->string('descripcion', 255);
            $tabla->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tipo_riesgo');
    }
};
