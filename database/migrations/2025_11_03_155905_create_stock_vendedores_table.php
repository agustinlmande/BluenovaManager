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
        Schema::create('stock_vendedores', function (Blueprint $table) {
    $table->id();
    $table->foreignId('vendedor_id')->constrained('vendedores')->onDelete('cascade');
    $table->foreignId('producto_id')->constrained('productos')->onDelete('cascade');
    $table->integer('cantidad')->default(0);
    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_vendedores');
    }
};
