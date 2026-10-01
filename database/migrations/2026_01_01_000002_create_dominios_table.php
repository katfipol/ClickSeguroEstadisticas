<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dominio', function (Blueprint $tabla) {
            $tabla->id('id_dominio');
            $tabla->string('nombre', 255)->unique();
            $tabla->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dominio');
    }
};