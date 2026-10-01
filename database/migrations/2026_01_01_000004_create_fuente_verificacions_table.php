<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fuente_verificacion', function (Blueprint $tabla) {
            $tabla->id('id_fuente');
            $tabla->string('nombre', 50)->unique();
            $tabla->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fuente_verificacion');
    }
};