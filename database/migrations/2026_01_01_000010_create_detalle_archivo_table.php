<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detalle_archivo', function (Blueprint $tabla) {
            $tabla->id('id_detalle_archivo');

            $tabla->foreignId('id_archivo')
                ->constrained('archivo_verificado', 'id_archivo')
                ->cascadeOnDelete();

            $tabla->foreignId('id_fuente')
                ->constrained('fuente_verificacion', 'id_fuente')
                ->restrictOnDelete();

            $tabla->string('motor', 100)->nullable();
            $tabla->string('resultado', 20);
            $tabla->string('categoria', 100)->nullable();
            $tabla->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detalle_archivo');
    }
};
