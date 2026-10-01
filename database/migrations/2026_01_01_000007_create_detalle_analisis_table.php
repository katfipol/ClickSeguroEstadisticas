<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detalle_analisis', function (Blueprint $t) {
            $t->id();
            $t->foreignId('analisis_id')->constrained('analisis')->cascadeOnDelete();
            $t->foreignId('fuente_verificacion_id')->constrained('fuente_verificacions')->restrictOnDelete();
            $t->string('motor', 100)->nullable();       // motor que detectó; null = resumen de la fuente
            $t->string('resultado', 20);                // limpio | sospechoso | malicioso | sin_datos
            $t->string('categoria', 100)->nullable();   // phishing, malware, etc.
            $t->string('descripcion', 255)->nullable(); // factor/motivo legible
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detalle_analisis');
    }
};
