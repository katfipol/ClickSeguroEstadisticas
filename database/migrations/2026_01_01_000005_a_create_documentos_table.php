<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documento', function (Blueprint $tabla) {
            $tabla->id('id_documento');
            $tabla->foreignId('id_usuario')
                ->constrained('usuario', 'id_usuario')
                ->cascadeOnDelete();
            $tabla->string('nombre', 255);
            $tabla->unsignedInteger('total_enlaces')->default(0);
            $tabla->unsignedInteger('enlaces_omitidos')->default(0);
            $tabla->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documento');
    }
};
