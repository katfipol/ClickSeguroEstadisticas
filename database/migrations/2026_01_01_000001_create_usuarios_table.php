<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usuarios', function (Blueprint $t) {
            $t->id();
            $t->string('nombre', 100); // Uno o varios nombres
            $t->string('apellido_paterno', 100)->nullable();
            $t->string('apellido_materno', 100)->nullable();
            $t->string('email', 150)->unique();
            $t->string('password');
            $t->timestamps(); // created_at = fecha de registro
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usuarios');
    }
};