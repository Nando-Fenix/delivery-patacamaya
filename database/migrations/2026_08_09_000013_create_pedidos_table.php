<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pedidos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('usuarios')->restrictOnDelete();
            $table->foreignId('negocio_id')->constrained('negocios')->restrictOnDelete();
            $table->foreignId('direccion_usuario_id')->nullable()->constrained('direcciones_usuario')->nullOnDelete();
            $table->string('estado', 30)->index();
            $table->decimal('subtotal', 10, 2);
            $table->decimal('costo_delivery', 10, 2)->default(0);
            $table->decimal('total', 10, 2);
            $table->string('metodo_pago', 20);
            $table->string('observaciones', 500)->nullable();
            $table->string('motivo_rechazo', 300)->nullable();
            $table->string('direccion_nombre', 100);
            $table->string('direccion_referencia');
            $table->string('zona_nombre', 150);
            $table->decimal('latitud', 10, 7);
            $table->decimal('longitud', 10, 7);
            $table->timestamp('fecha_pedido');
            $table->timestamps();
            $table->index(['negocio_id', 'estado']);
            $table->index(['usuario_id', 'fecha_pedido']);
        });

        Schema::create('detalles_pedido', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pedido_id')->constrained('pedidos')->cascadeOnDelete();
            $table->foreignId('producto_id')->nullable()->constrained('productos')->nullOnDelete();
            $table->string('nombre_producto', 150);
            $table->decimal('precio_unitario', 10, 2);
            $table->unsignedTinyInteger('cantidad');
            $table->decimal('subtotal', 10, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detalles_pedido');
        Schema::dropIfExists('pedidos');
    }
};
