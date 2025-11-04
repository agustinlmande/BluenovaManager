<?php

namespace App\Http\Controllers;

use App\Models\Compra;
use App\Models\DetalleCompra;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CompraController extends Controller
{
    // 📋 Mostrar todas las compras (con filtros y búsqueda)
    public function index(Request $request)
    {
        $query = Compra::orderBy('fecha', 'desc');

        // 🔍 Búsqueda general (proveedor, fecha, totales)
        if ($request->filled('buscar')) {
            $buscar = $request->buscar;
            $query->where(function ($q) use ($buscar) {
                $q->where('proveedor', 'like', "%$buscar%")
                  ->orWhere('fecha', 'like', "%$buscar%")
                  ->orWhere('total_usd', 'like', "%$buscar%")
                  ->orWhere('total_ars', 'like', "%$buscar%");
            });
        }

        // 📅 Filtro por fecha
        if ($request->filled('fecha_desde')) {
            $query->where('fecha', '>=', $request->fecha_desde);
        }
        if ($request->filled('fecha_hasta')) {
            $query->where('fecha', '<=', $request->fecha_hasta);
        }

        // 🏢 Filtro por proveedor
        if ($request->filled('proveedor')) {
            $query->where('proveedor', $request->proveedor);
        }

        // 💵 Filtro por total USD
        if ($request->filled('usd_filtro')) {
            if ($request->usd_filtro === 'mayor') {
                $query->where('total_usd', '>=', $request->usd_valor);
            } elseif ($request->usd_filtro === 'menor') {
                $query->where('total_usd', '<=', $request->usd_valor);
            } elseif ($request->usd_filtro === 'entre') {
                $query->whereBetween('total_usd', [$request->usd_desde, $request->usd_hasta]);
            }
        }

        // 💰 Filtro por total ARS
        if ($request->filled('ars_filtro')) {
            if ($request->ars_filtro === 'mayor') {
                $query->where('total_ars', '>=', $request->ars_valor);
            } elseif ($request->ars_filtro === 'menor') {
                $query->where('total_ars', '<=', $request->ars_valor);
            } elseif ($request->ars_filtro === 'entre') {
                $query->whereBetween('total_ars', [$request->ars_desde, $request->ars_hasta]);
            }
        }

        $compras = $query->with('detalles.producto')->get();
        $proveedores = Compra::select('proveedor')->distinct()->pluck('proveedor');

        return view('compras.index', compact('compras', 'proveedores'));
    }

    // 🆕 Mostrar formulario de creación
    public function create()
    {
        $productos = \App\Models\Producto::orderBy('nombre')->get();
    $categorias = \App\Models\Categoria::orderBy('nombre')->get();
    return view('compras.create', compact('productos', 'categorias'));
    }

    // 💾 Guardar nueva compra
    public function store(Request $request)
    {
        $request->validate([
            'fecha' => 'required|date|before_or_equal:today',
            'cotizacion_dolar' => 'required|numeric|min:0',
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

                $producto->increment('stock', $item['cantidad']);
                $totalUsd += $subtotalUsd;
                $totalArs += $subtotalArs;
            }

            $compra->update([
                'total_usd' => $totalUsd,
                'total_ars' => $totalArs,
            ]);
        });

        return redirect()->route('compras.index')->with('success', 'Compra registrada correctamente.');
    }

    // ✏️ Editar compra
    public function edit(Compra $compra)
    {
        $productos = Producto::orderBy('nombre')->get();
        $compra->load('detalles.producto');
        return view('compras.edit', compact('compra', 'productos'));
    }

    // 🔄 Actualizar compra
    public function update(Request $request, Compra $compra)
    {
        $request->validate([
            'fecha' => 'required|date|before_or_equal:today',
            'cotizacion_dolar' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($request, $compra) {
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
                'proveedor' => $request->proveedor,
            ]);
        });

        return redirect()->route('compras.index')->with('success', 'Compra actualizada correctamente.');
    }

    // 🗑️ Eliminar compra
    public function destroy(Compra $compra)
    {
        DB::transaction(function () use ($compra) {
            foreach ($compra->detalles as $detalle) {
                $detalle->producto->decrement('stock', $detalle->cantidad);
            }
            $compra->detalles()->delete();
            $compra->delete();
        });

        return redirect()->route('compras.index')->with('success', 'Compra eliminada correctamente.');
    }
}
