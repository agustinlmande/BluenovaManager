<?php

namespace App\Http\Controllers;

use App\Models\Compra;
use App\Models\DetalleCompra;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CompraController extends Controller
{
    // Mostrar todas las compras
    public function index()
    {
        $compras = Compra::with('detalles.producto')->orderBy('id', 'desc')->get();
        return view('compras.index', compact('compras'));
    }

    // Mostrar formulario de nueva compra
    public function create()
    {
        $productos = Producto::orderBy('nombre')->get();
        return view('compras.create', compact('productos'));
    }

    // Guardar nueva compra
    public function store(Request $request)
    {
        $request->validate([
            'fecha' => 'required|date',
            'cotizacion_dolar' => 'required|numeric',
            'productos' => 'required|array|min:1',
            'productos.*.id' => 'required|exists:productos,id',
            'productos.*.cantidad' => 'required|integer|min:1',
            'productos.*.precio_unitario_usd' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($request) {
            $totalUsd = 0;
            $totalArs = 0;

            $compra = Compra::create([
                'proveedor' => $request->proveedor,
                'fecha' => $request->fecha,
                'total_usd' => 0,
                'total_ars' => 0,
                'observaciones' => $request->observaciones,
            ]);

            foreach ($request->productos as $item) {
                $producto = Producto::find($item['id']);
                $subtotalUsd = $item['precio_unitario_usd'] * $item['cantidad'];
                $subtotalArs = $subtotalUsd * $request->cotizacion_dolar;

                DetalleCompra::create([
                    'compra_id' => $compra->id,
                    'producto_id' => $producto->id,
                    'cantidad' => $item['cantidad'],
                    'precio_unitario_usd' => $item['precio_unitario_usd'],
                    'cotizacion_dolar' => $request->cotizacion_dolar,
                    'precio_unitario_ars' => $item['precio_unitario_usd'] * $request->cotizacion_dolar,
                ]);

                // Actualizar stock del producto
                $producto->increment('stock', $item['cantidad']);

                $totalUsd += $subtotalUsd;
                $totalArs += $subtotalArs;
            }

            // Actualizar totales de la compra
            $compra->update([
                'total_usd' => $totalUsd,
                'total_ars' => $totalArs,
            ]);
        });

        return redirect()->route('compras.index')->with('success', 'Compra registrada correctamente.');
    }
}
