<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usuario', function (Blueprint $tabla) {
            $tabla->id('id_usuario');
            $tabla->string('nombre', 100);
            $tabla->string('apellido_paterno', 100)->nullable();
            $tabla->string('apellido_materno', 100)->nullable();
            $tabla->string('email', 150)->unique();
            $tabla->string('password');
            $tabla->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usuario');
    }
};