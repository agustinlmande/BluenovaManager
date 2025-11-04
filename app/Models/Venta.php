<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;





class Venta extends Model
{
    use HasFactory;

    protected $table = 'ventas';

    protected $fillable = [
        'cliente_id',
        'vendedor_id',
        'fecha',
        'cotizacion_dolar',
        'total_venta_ars',
        'total_venta_usd',
        'tipo_entrega',
        'costo_envio',
        'metodo_pago',
        'observaciones',
        'monto_pagado',
        'saldo_pendiente',
        'estado_pago',
    ];


    public function vendedor()
    {
        return $this->belongsTo(Vendedor::class, 'vendedor_id');
    }

    public function detalles()
    {
        return $this->hasMany(DetalleVenta::class, 'venta_id');
    }

    public function pagosVendedor()
    {
        return $this->hasMany(PagoVendedor::class, 'venta_id');
    }
}
