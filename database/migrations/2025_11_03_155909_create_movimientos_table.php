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
        Schema::create('movimientos', function (Blueprint $table) {
    $table->id();
    $table->enum('tipo', ['ingreso', 'egreso']);
    $table->decimal('monto', 12, 2);
    $table->string('detalle');
    $table->enum('metodo_pago', ['efectivo', 'transferencia'])->nullable();
    $table->date('fecha');
    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movimientos');
    }
};
