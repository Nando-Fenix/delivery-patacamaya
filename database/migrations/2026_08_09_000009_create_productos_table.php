<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('productos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('negocio_id')->constrained('negocios')->cascadeOnDelete();
            $table->foreignId('categoria_producto_id')->nullable()->constrained('categorias_producto')->nullOnDelete();
            $table->string('nombre', 150);
            $table->text('descripcion')->nullable();
            $table->decimal('precio', 10, 2);
            $table->string('imagen')->nullable();
            $table->boolean('activo')->default(true)->index();
            $table->boolean('disponible')->default(true)->index();
            $table->unsignedInteger('orden')->nullable();
            $table->timestamps();
            $table->index(['negocio_id', 'categoria_producto_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('productos');
    }
};
