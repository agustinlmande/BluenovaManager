<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Compra extends Model
{
    use HasFactory;

    protected $table = 'compras';

    // Agregamos 'aplica_iva' y 'porcentaje_iva' al final de esta lista
    protected $fillable = [
        'proveedor',
        'fecha',
        'total_usd',
        'total_ars',
        'observaciones',
        'aplica_iva',
        'porcentaje_iva'
    ];

    public function detalles()
    {
        return $this->hasMany(DetalleCompra::class, 'compra_id');
    }

    public function ultimoProveedor()
    {
        return $this->hasOneThrough(
            \App\Models\Compra::class,
            \App\Models\DetalleCompra::class,
            'producto_id',   // Foreign key en detalle_compra
            'id',            // Foreign key en compra
            'id',            // Local key en producto
            'compra_id'      // Local key en detalle_compra
        )->latestOfMany();  // obtiene la última compra del producto
    }
}
