<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PagoVendedor extends Model
{
    use HasFactory;

    protected $table = 'pagos_vendedores';

    protected $fillable = [
        'vendedor_id', 'venta_id', 'monto_total', 'monto_pagado',
        'metodo_pago', 'estado', 'fecha_pago'
    ];

    public function vendedor()
    {
        return $this->belongsTo(Vendedor::class, 'vendedor_id');
    }

    public function venta()
    {
        return $this->belongsTo(Venta::class, 'venta_id');
    }
}
