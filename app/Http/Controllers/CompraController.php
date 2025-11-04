<?php

namespace App\Http\Controllers;

use App\Models\Compra;
use App\Models\DetalleCompra;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CompraController extends Controller
{
    // Mostrar todas las compras con filtros
    public function index(Request $request)
    {
        $query = Compra::with('detalles.producto')->orderBy('fecha', 'desc');

        // 📅 Filtro por fechas
        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha', '>=', $request->fecha_desde);
        }
        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha', '<=', $request->fecha_hasta);
        }

        // 🧾 Filtro por proveedor
        if ($request->filled('proveedor')) {
            $query->where('proveedor', 'like', '%' . $request->proveedor . '%');
        }

        // 💵 Filtro por montos USD
        if ($request->filled('monto_usd_min')) {
            $query->where('total_usd', '>=', $request->monto_usd_min);
        }
        if ($request->filled('monto_usd_max')) {
            $query->where('total_usd', '<=', $request->monto_usd_max);
        }

        // 💰 Filtro por montos ARS
        if ($request->filled('monto_ars_min')) {
            $query->where('total_ars', '>=', $request->monto_ars_min);
        }
        if ($request->filled('monto_ars_max')) {
            $query->where('total_ars', '<=', $request->monto_ars_max);
        }

        $compras = $query->get();

        // 🔹 Totales según los resultados filtrados
        $totalUsd = $compras->sum('total_usd');
        $totalArs = $compras->sum('total_ars');

        return view('compras.index', compact('compras', 'totalUsd', 'totalArs'));
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
            'fecha' => 'required|date|before_or_equal:today',
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

    // Mostrar formulario de edición
    public function edit(Compra $compra)
    {
        $productos = Producto::orderBy('nombre')->get();
        $compra->load('detalles.producto');
        return view('compras.edit', compact('compra', 'productos'));
    }

    // Actualizar compra
    public function update(Request $request, Compra $compra)
    {
        $request->validate([
            'fecha' => 'required|date|before_or_equal:today',
            'cotizacion_dolar' => 'required|numeric',
        ]);

        DB::transaction(function () use ($request, $compra) {
            // Revertir stock anterior
            foreach ($compra->detalles as $detalle) {
                $detalle->producto->decrement('stock', $detalle->cantidad);
                $detalle->delete();
            }

            $totalUsd = 0;
            $totalArs = 0;

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

                $producto->increment('stock', $item['cantidad']);

                $totalUsd += $subtotalUsd;
                $totalArs += $subtotalArs;
            }

            $compra->update([
                'fecha' => $request->fecha,
                'cotizacion_dolar' => $request->cotizacion_dolar,
                'total_usd' => $totalUsd,
                'total_ars' => $totalArs,
                'observaciones' => $request->observaciones,
            ]);
        });

        return redirect()->route('compras.index')->with('success', 'Compra actualizada correctamente.');
    }

    // Eliminar compra definitivamente
    public function destroy(Compra $compra)
    {
        DB::transaction(function () use ($compra) {
            // 🔹 Revertir stock antes de eliminar
            foreach ($compra->detalles as $detalle) {
                $detalle->producto->decrement('stock', $detalle->cantidad);
            }

            // 🔹 Eliminar detalles y la compra
            $compra->detalles()->delete();
            $compra->delete();
        });

        return redirect()->route('compras.index')->with('success', 'Compra eliminada correctamente.');
    }
}
