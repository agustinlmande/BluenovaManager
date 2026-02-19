<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Venta extends Model
{
    use HasFactory;

    protected $table = 'ventas';

    protected $fillable = [
        'fecha',
        'vendedor_id',
        'vendedor_nombre',
        'porcentaje_comision_vendedor',
        'cotizacion_dolar',
        'tipo_entrega',
        'costo_envio',
        'metodo_pago',
        'total_venta_ars',
        'total_venta_usd',
        'ganancia_ars', // ✅ NUEVO: ganancia total de la venta
        'observaciones',
        'monto_pagado',
        'saldo_pendiente',
        'estado_pago',
        'facturado',
    ];

    // 🔹 Relaciones
    public function detalles()
    {
        return $this->hasMany(DetalleVenta::class, 'venta_id');
    }

    public function vendedor()
    {
        return $this->belongsTo(Vendedor::class, 'vendedor_id');
    }

    public function cuenta()
    {
        return $this->belongsTo(Cuenta::class, 'cuenta_id');
    }
}
