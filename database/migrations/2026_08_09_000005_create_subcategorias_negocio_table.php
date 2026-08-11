<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subcategorias_negocio', function (Blueprint $table) {
            $table->id();
            $table->foreignId('categoria_negocio_id')->constrained('categorias_negocio')->restrictOnDelete();
            $table->string('nombre', 100);
            $table->string('descripcion')->nullable();
            $table->string('icono', 100)->nullable();
            $table->boolean('activo')->default(true)->index();
            $table->timestamps();
            $table->unique(['categoria_negocio_id', 'nombre']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subcategorias_negocio');
    }
};
