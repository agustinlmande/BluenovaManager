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
        Schema::create('productos', function (Blueprint $table) {
    $table->id();
    $table->string('nombre');
    $table->foreignId('categoria_id')->constrained('categorias')->onDelete('cascade');
    $table->text('descripcion')->nullable();
    $table->string('imagen')->nullable();
    $table->integer('stock')->default(0);
    $table->decimal('precio_compra_usd', 10, 2);
    $table->decimal('cotizacion_compra', 10, 2);
    $table->decimal('precio_compra_ars', 10, 2);
    $table->decimal('precio_venta_usd', 10, 2)->nullable();
    $table->decimal('precio_venta_ars', 10, 2);
    $table->decimal('porcentaje_ganancia', 5, 2);
    $table->enum('modo_calculo', ['porcentaje', 'manual']);
    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('productos');
    }
};
