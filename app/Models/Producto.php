<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;




class Producto extends Model
{
    use HasFactory;


    protected $table = 'productos';

    protected $fillable = [
        'nombre',
        'descripcion',
        'categoria_id',
        'stock',
        'precio_compra_usd',
        'cotizacion_compra',
        'precio_compra_ars',
        'envio_ars',
        'precio_venta_usd',
        'precio_venta_ars',
        'porcentaje_ganancia',
    ];


    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'categoria_id');
    }

    public function detallesCompra()
    {
        return $this->hasMany(DetalleCompra::class, 'producto_id');
    }

    public function detallesVenta()
    {
        return $this->hasMany(DetalleVenta::class, 'producto_id');
    }

    public function stockVendedores()
    {
        return $this->hasMany(StockVendedor::class, 'producto_id');
    }
}
