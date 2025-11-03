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
        Schema::create('pagos_vendedores', function (Blueprint $table) {
    $table->id();
    $table->foreignId('vendedor_id')->constrained('vendedores')->onDelete('cascade');
    $table->foreignId('venta_id')->constrained('ventas')->onDelete('cascade');
    $table->decimal('monto_total', 12, 2);
    $table->decimal('monto_pagado', 12, 2)->default(0);
    $table->enum('metodo_pago', ['efectivo', 'transferencia']);
    $table->enum('estado', ['pendiente', 'pagado'])->default('pendiente');
    $table->date('fecha_pago')->nullable();
    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pagos_vendedores');
    }
};
