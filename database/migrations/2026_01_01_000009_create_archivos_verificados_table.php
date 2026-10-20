<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('archivo_verificado', function (Blueprint $tabla) {
            $tabla->id('id_archivo');

            $tabla->foreignId('id_usuario')
                ->constrained('usuario', 'id_usuario')
                ->cascadeOnDelete();

            $tabla->string('nombre', 255);
            $tabla->unsignedInteger('tamano');
            $tabla->char('sha256', 64);

            $tabla->foreignId('id_tipo_riesgo')
                ->nullable()
                ->constrained('tipo_riesgo', 'id_tipo_riesgo')
                ->restrictOnDelete();

            $tabla->timestamp('created_at')->useCurrent();

            $tabla->index(['id_usuario', 'created_at']);
            $tabla->index('sha256');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('archivo_verificado');
    }
};
