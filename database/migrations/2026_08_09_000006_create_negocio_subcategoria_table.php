<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('negocio_subcategoria', function (Blueprint $table) {
            $table->foreignId('negocio_id')->constrained('negocios')->cascadeOnDelete();
            $table->foreignId('subcategoria_negocio_id')->constrained('subcategorias_negocio')->cascadeOnDelete();
            $table->unique(['negocio_id', 'subcategoria_negocio_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('negocio_subcategoria');
    }
};
