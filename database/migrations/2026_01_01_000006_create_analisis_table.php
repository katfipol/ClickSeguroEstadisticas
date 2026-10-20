<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analisis', function (Blueprint $tabla) {
            $tabla->id('id_analisis');

            $tabla->foreignId('id_usuario')
                ->constrained('usuario', 'id_usuario')
                ->cascadeOnDelete();

            $tabla->foreignId('id_url')
                ->constrained('url', 'id_url')
                ->restrictOnDelete();

            $tabla->foreignId('id_url_final')
                ->nullable()
                ->constrained('url', 'id_url')
                ->restrictOnDelete();

            $tabla->foreignId('id_tipo_riesgo')
                ->nullable()
                ->constrained('tipo_riesgo', 'id_tipo_riesgo')
                ->restrictOnDelete();

            $tabla->foreignId('id_analisis_anterior')
                ->nullable()
                ->constrained('analisis', 'id_analisis')
                ->nullOnDelete();

            $tabla->foreignId('id_documento')
                ->nullable()
                ->constrained('documento', 'id_documento')
                ->nullOnDelete();

            $tabla->string('estado', 20)->default('pendiente');
            $tabla->string('error', 255)->nullable();

            $tabla->timestamp('created_at')->useCurrent();

            $tabla->index(['id_usuario', 'created_at']);
            $tabla->index(['id_usuario', 'id_tipo_riesgo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analisis');
    }
};
