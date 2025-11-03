<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vendedor extends Model
{
    use HasFactory;

    protected $table = 'vendedores';

    protected $fillable = ['nombre', 'contacto', 'comision_por_defecto', 'observaciones'];

    public function ventas()
    {
        return $this->hasMany(Venta::class, 'vendedor_id');
    }

    public function stock()
    {
        return $this->hasMany(StockVendedor::class, 'vendedor_id');
    }

    public function pagos()
    {
        return $this->hasMany(PagoVendedor::class, 'vendedor_id');
    }
}
