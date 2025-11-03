<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockVendedor extends Model
{
    use HasFactory;

    protected $table = 'stock_vendedores';

    protected $fillable = ['vendedor_id', 'producto_id', 'cantidad'];

    public function vendedor()
    {
        return $this->belongsTo(Vendedor::class, 'vendedor_id');
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }
}
