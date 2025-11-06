<?php

namespace App\Http\Controllers;

use App\Models\Compra;
use App\Models\DetalleCompra;
use App\Models\Producto;
use App\Models\HistorialPrecio; // 🟩 Importante: agregado
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Caja;

class CompraController extends Controller
{
    // 📋 Mostrar todas las compras (con filtros y búsqueda)
    public function index(Request $request)
    {
        $query = Compra::orderBy('fecha', 'desc')->orderBy('id', 'desc');


        if ($request->filled('buscar')) {
            $buscar = $request->buscar;
            $query->where(function ($q) use ($buscar) {
                $q->where('proveedor', 'like', "%$buscar%")
                    ->orWhere('fecha', 'like', "%$buscar%")
                    ->orWhere('total_usd', 'like', "%$buscar%")
                    ->orWhere('total_ars', 'like', "%$buscar%");
            });
        }

        if ($request->filled('fecha_desde')) $query->where('fecha', '>=', $request->fecha_desde);
        if ($request->filled('fecha_hasta')) $query->where('fecha', '<=', $request->fecha_hasta);
        if ($request->filled('proveedor')) $query->where('proveedor', $request->proveedor);

        if ($request->filled('usd_filtro')) {
            if ($request->usd_filtro === 'mayor') $query->where('total_usd', '>=', $request->usd_valor);
            elseif ($request->usd_filtro === 'menor') $query->where('total_usd', '<=', $request->usd_valor);
            elseif ($request->usd_filtro === 'entre') $query->whereBetween('total_usd', [$request->usd_desde, $request->usd_hasta]);
        }

        if ($request->filled('ars_filtro')) {
            if ($request->ars_filtro === 'mayor') $query->where('total_ars', '>=', $request->ars_valor);
            elseif ($request->ars_filtro === 'menor') $query->where('total_ars', '<=', $request->ars_valor);
            elseif ($request->ars_filtro === 'entre') $query->whereBetween('total_ars', [$request->ars_desde, $request->ars_hasta]);
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

                // 📦 Incrementar stock
                $producto->increment('stock', $item['cantidad']);

                // 🟩 Actualizar precios y ganancia en el producto
                $producto->update([
                    'precio_compra_usd' => $item['precio_unitario_usd'],
                    'cotizacion_compra' => $request->cotizacion_dolar,
                    'precio_compra_ars' => $item['precio_unitario_usd'] * $request->cotizacion_dolar,
                    'precio_venta_usd' => $item['precio_venta_usd'] ?? $producto->precio_venta_usd,
                    'precio_venta_ars' => $item['precio_venta_ars'] ?? $producto->precio_venta_ars,
                    'porcentaje_ganancia' => $item['ganancia'] ?? $producto->porcentaje_ganancia,
                ]);

                // 🧾 Guardar historial de precio
                HistorialPrecio::create([
                    'producto_id' => $producto->id,
                    'compra_id' => $compra->id,
                    'precio_compra_usd' => $item['precio_unitario_usd'],
                    'cotizacion' => $request->cotizacion_dolar,
                    'precio_compra_ars' => $item['precio_unitario_usd'] * $request->cotizacion_dolar,
                ]);

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
        $categorias = \App\Models\Categoria::orderBy('nombre')->get();
        $compra->load('detalles.producto');
        return view('compras.edit', compact('compra', 'productos', 'categorias'));
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
                    'envio_ars' => $item['envio_ars'] ?? 0, // ✅ nuevo campo
                ]);

                // 📦 Actualizar stock y precios del producto
                $producto->increment('stock', $item['cantidad']);
                $producto->update([
                    'precio_compra_usd' => $item['precio_unitario_usd'],
                    'cotizacion_compra' => $request->cotizacion_dolar,
                    'precio_compra_ars' => $item['precio_unitario_usd'] * $request->cotizacion_dolar,
                    'envio_ars' => $item['envio_ars'] ?? 0, // ✅ nuevo campo
                    'precio_venta_usd' => $item['precio_venta_usd'] ?? $producto->precio_venta_usd,
                    'precio_venta_ars' => $item['precio_venta_ars'] ?? $producto->precio_venta_ars,
                    'porcentaje_ganancia' => $item['ganancia'] ?? $producto->porcentaje_ganancia,
                ]);


                // 🧾 Guardar historial actualizado
                HistorialPrecio::create([
                    'producto_id' => $producto->id,
                    'compra_id' => $compra->id,
                    'precio_compra_usd' => $item['precio_unitario_usd'],
                    'cotizacion' => $request->cotizacion_dolar,
                    'precio_compra_ars' => $item['precio_unitario_usd'] * $request->cotizacion_dolar,
                ]);

                $totalUsd += $subtotalUsd;
                $totalArs += $subtotalArs;
            }

            $compra->update([
                'fecha' => $request->fecha,
                'cotizacion_dolar' => $request->cotizacion_dolar,
                'total_usd' => $totalUsd,
                'total_ars' => $totalArs,
                'proveedor' => $request->proveedor,
                'observaciones' => $request->observaciones,
            ]);
        });

        return redirect()->route('compras.index')->with('success', 'Compra actualizada correctamente.');
    }

    // 🗑️ Eliminar compra
    public function destroy(Compra $compra)
    {
        DB::transaction(function () use ($compra) {
            foreach ($compra->detalles as $detalle) {
                $producto = $detalle->producto;

                // 📦 Restar stock
                $producto->decrement('stock', $detalle->cantidad);

                // 🔙 Restaurar precio anterior si existe
                $ultimoPrecio = HistorialPrecio::where('producto_id', $producto->id)
                    ->where('compra_id', '!=', $compra->id)
                    ->latest('created_at')
                    ->first();

                if ($ultimoPrecio) {
                    $producto->update([
                        'precio_compra_usd' => $ultimoPrecio->precio_compra_usd,
                        'cotizacion_compra' => $ultimoPrecio->cotizacion,
                        'precio_compra_ars' => $ultimoPrecio->precio_compra_ars,
                    ]);
                }
            }

            $compra->detalles()->delete();
            $compra->delete();
        });

        return redirect()->route('compras.index')->with('success', 'Compra eliminada correctamente.');
    }

    // ❌ Eliminar producto individual (devolución)
    public function removeItem($detalleId)
    {
        $detalle = DetalleCompra::findOrFail($detalleId);
        $compra = $detalle->compra;

        DB::transaction(function () use ($detalle, $compra) {
            $detalle->producto->decrement('stock', $detalle->cantidad);

            $subtotalUsd = $detalle->cantidad * $detalle->precio_unitario_usd;
            $subtotalArs = $subtotalUsd * $detalle->cotizacion_dolar;

            $compra->decrement('total_usd', $subtotalUsd);
            $compra->decrement('total_ars', $subtotalArs);

            $detalle->delete();
        });

        return back()->with('success', 'Producto eliminado de la compra correctamente.');
    }
}
