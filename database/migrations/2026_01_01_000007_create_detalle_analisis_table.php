<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detalle_analisis', function (Blueprint $tabla) {
            $tabla->id('id_detalle');

            $tabla->foreignId('id_analisis')
                ->constrained('analisis', 'id_analisis')
                ->cascadeOnDelete();

            $tabla->foreignId('id_fuente')
                ->constrained('fuente_verificacion', 'id_fuente')
                ->restrictOnDelete();

            // Un motor nulo distingue el resumen generado por la aplicación.
            $tabla->string('motor', 100)->nullable();
            $tabla->string('resultado', 20);
            $tabla->string('categoria', 100)->nullable();
            $tabla->string('descripcion', 255)->nullable();
            $tabla->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detalle_analisis');
    }
};