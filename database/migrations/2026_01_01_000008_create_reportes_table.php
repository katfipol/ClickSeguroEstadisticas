<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reporte', function (Blueprint $tabla) {
            $tabla->id('id_reporte');

            $tabla->foreignId('id_usuario')
                ->constrained('usuario', 'id_usuario')
                ->cascadeOnDelete();

            $tabla->foreignId('id_url')
                ->constrained('url', 'id_url')
                ->restrictOnDelete();

            $tabla->string('motivo', 500)->nullable();
            $tabla->string('estado', 20)->default('pendiente');
            $tabla->timestamps();

            $tabla->unique(['id_usuario', 'id_url']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reporte');
    }
};