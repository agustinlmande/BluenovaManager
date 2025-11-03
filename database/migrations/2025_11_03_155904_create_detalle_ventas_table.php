<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('detalle_ventas', function (Blueprint $table) {
    $table->id();
    $table->foreignId('venta_id')->constrained('ventas')->onDelete('cascade');
    $table->foreignId('producto_id')->constrained('productos')->onDelete('cascade');
    $table->foreignId('vendedor_id')->nullable()->constrained('vendedores')->onDelete('set null');
    $table->integer('cantidad');
    $table->decimal('precio_unitario_ars', 10, 2);
    $table->decimal('precio_unitario_usd', 10, 2)->nullable();
    $table->decimal('ganancia_ars', 10, 2)->nullable();
    $table->decimal('porcentaje_ganancia', 5, 2)->nullable();
    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detalle_ventas');
    }
};
