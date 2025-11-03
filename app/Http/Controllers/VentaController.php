<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use App\Models\DetalleVenta;
use App\Models\Producto;
use App\Models\Vendedor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VentaController extends Controller
{
    public function index()
    {
        $ventas = Venta::with('vendedor', 'detalles.producto')->orderBy('id', 'desc')->get();
        return view('ventas.index', compact('ventas'));
    }

    public function create()
    {
        $productos = Producto::orderBy('nombre')->get();
        $vendedores = Vendedor::orderBy('nombre')->get();
        return view('ventas.create', compact('productos', 'vendedores'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'fecha' => 'required|date',
            'cotizacion_dolar' => 'required|numeric|min:0',
            'metodo_pago' => 'required|in:efectivo,transferencia',
            'productos' => 'required|array|min:1',
            'productos.*.id' => 'required|exists:productos,id',
            'productos.*.cantidad' => 'required|integer|min:1',
            'productos.*.precio_unitario_ars' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($request) {
            $totalArs = 0;
            $totalUsd = 0;

            $venta = Venta::create([
                'fecha' => $request->fecha,
                'vendedor_id' => $request->vendedor_id,
                'cotizacion_dolar' => $request->cotizacion_dolar,
                'tipo_entrega' => $request->tipo_entrega ?? null,
                'costo_envio' => $request->costo_envio ?? 0,
                'metodo_pago' => $request->metodo_pago,
                'total_venta_ars' => 0,
                'total_venta_usd' => 0,
                'observaciones' => $request->observaciones,
            ]);

            foreach ($request->productos as $item) {
                $producto = Producto::find($item['id']);
                $cantidad = $item['cantidad'];
                $precio = $item['precio_unitario_ars'];

                $subtotalArs = $precio * $cantidad;
                $subtotalUsd = $subtotalArs / $request->cotizacion_dolar;

                $producto->decrement('stock', $cantidad);

                $ganancia = $subtotalArs - ($producto->precio_compra_ars * $cantidad);

                if ($request->vendedor_id) {
                    $vendedor = Vendedor::find($request->vendedor_id);
                    $comision = ($subtotalArs * $vendedor->comision_por_defecto) / 100;
                    $ganancia -= $comision;
                }

                DetalleVenta::create([
                    'venta_id' => $venta->id,
                    'producto_id' => $producto->id,
                    'vendedor_id' => $request->vendedor_id,
                    'cantidad' => $cantidad,
                    'precio_unitario_ars' => $precio,
                    'precio_unitario_usd' => $subtotalUsd / $cantidad,
                    'ganancia_ars' => $ganancia,
                    'porcentaje_ganancia' => ($producto->precio_compra_ars > 0)
                        ? ($ganancia / ($producto->precio_compra_ars * $cantidad)) * 100
                        : 0,
                ]);

                $totalArs += $subtotalArs;
                $totalUsd += $subtotalUsd;
            }

            $totalArs -= ($request->costo_envio ?? 0);

            $venta->update([
                'total_venta_ars' => $totalArs,
                'total_venta_usd' => $totalUsd,
            ]);
        });

        return redirect()->route('ventas.index')->with('success', 'Venta registrada correctamente.');
    }
}
