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
    Schema::create('caja', function (Blueprint $table) {
        $table->id();
        $table->enum('tipo', ['ingreso', 'egreso']);
        $table->decimal('monto', 12, 2);
        $table->string('motivo')->nullable();
        $table->date('fecha');
        $table->boolean('editable')->default(true); // si viene de venta o compra será false
        $table->timestamps();
    });
}

public function down(): void
{
    Schema::dropIfExists('caja');
}

};
