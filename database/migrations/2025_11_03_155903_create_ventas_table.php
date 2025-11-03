<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ventas', function (Blueprint $table) {
            $table->engine = 'InnoDB'; // aseguramos mismo motor de DB
            $table->id();
            $table->unsignedBigInteger('cliente_id')->nullable();

            // 🔹 Relación con vendedores (forma segura)
            $table->unsignedBigInteger('vendedor_id')->nullable();
            $table->foreign('vendedor_id')
                  ->references('id')
                  ->on('vendedores')
                  ->onDelete('set null')   // 👈 cambio clave
                  ->onUpdate('cascade');   // 👈 opcional, mejora integridad

            $table->date('fecha');
            $table->decimal('cotizacion_dolar', 10, 2);
            $table->decimal('total_venta_ars', 12, 2);
            $table->decimal('total_venta_usd', 12, 2)->nullable();
            $table->enum('tipo_entrega', ['envio', 'retiro'])->nullable();
            $table->decimal('costo_envio', 10, 2)->default(0);
            $table->enum('metodo_pago', ['efectivo', 'transferencia']);
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ventas');
    }
};
