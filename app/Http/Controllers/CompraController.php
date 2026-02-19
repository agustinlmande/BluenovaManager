<?php

namespace App\Http\Controllers;

use App\Models\Compra;
use App\Models\DetalleCompra;
use App\Models\Producto;
use App\Models\HistorialPrecio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CompraController extends Controller
{
    // 📋 Mostrar todas las compras (ordenadas por fecha descendente)
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

        $compras = $query->with('detalles.producto')->get();
        $proveedores = Compra::select('proveedor')->distinct()->pluck('proveedor');

        return view('compras.index', compact('compras', 'proveedores'));
    }

    // 🆕 Formulario de nueva compra
    public function create()
    {
        $productos = Producto::orderBy('nombre')->get();
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
            $totalProductosUsd = 0;
            $totalProductosArs = 0;
            $totalEnviosArs = 0; // Separamos el envío

            // Detectar si aplicó IVA en la vista
            $aplica_iva = $request->aplica_iva == '1';
            $porcentaje_iva = $aplica_iva ? $request->porcentaje_iva : null;

            $compra = Compra::create([
                'proveedor' => $request->proveedor,
                'fecha' => $request->fecha,
                'total_usd' => 0,
                'total_ars' => 0,
                'observaciones' => $request->observaciones,
                'aplica_iva' => $aplica_iva,
                'porcentaje_iva' => $porcentaje_iva,
            ]);

            foreach ($request->productos as $item) {
                $producto = Producto::find($item['id']);

                $subtotalUsd = $item['precio_unitario_usd'] * $item['cantidad'];
                $subtotalArs = $subtotalUsd * $request->cotizacion_dolar;
                $costoEnvioFila = ($item['envio_ars'] ?? 0) * $item['cantidad'];

                // 🧾 Guardar detalle
                DetalleCompra::create([
                    'compra_id' => $compra->id,
                    'producto_id' => $producto->id,
                    'cantidad' => $item['cantidad'],
                    'precio_unitario_usd' => $item['precio_unitario_usd'],
                    'cotizacion_dolar' => $request->cotizacion_dolar,
                    'precio_unitario_ars' => $item['precio_unitario_usd'] * $request->cotizacion_dolar,
                    'envio_ars' => $item['envio_ars'] ?? 0,
                ]);

                // 📦 Actualizar stock y costos
                $producto->increment('stock', $item['cantidad']);
                $producto->update([
                    'precio_compra_usd' => $item['precio_unitario_usd'],
                    'cotizacion_compra' => $request->cotizacion_dolar,
                    'precio_compra_ars' => $item['precio_unitario_usd'] * $request->cotizacion_dolar,
                    'envio_ars' => $item['envio_ars'] ?? 0,
                    'precio_venta_usd' => $item['precio_venta_usd'] ?? $producto->precio_venta_usd,
                    'precio_venta_ars' => $item['precio_venta_ars'] ?? $producto->precio_venta_ars,
                    'porcentaje_ganancia' => $item['ganancia'] ?? $producto->porcentaje_ganancia,
                ]);

                HistorialPrecio::create([
                    'producto_id' => $producto->id,
                    'compra_id' => $compra->id,
                    'precio_compra_usd' => $item['precio_unitario_usd'],
                    'cotizacion' => $request->cotizacion_dolar,
                    'precio_compra_ars' => $item['precio_unitario_usd'] * $request->cotizacion_dolar,
                ]);

                // Sumamos por separado para calcular el IVA después
                $totalProductosUsd += $subtotalUsd;
                $totalProductosArs += $subtotalArs;
                $totalEnviosArs += $costoEnvioFila;
            }

            // 🧮 Calcular totales finales de la factura
            $totalFinalUsd = $totalProductosUsd;
            $totalFinalArs = $totalProductosArs;

            // Si hay IVA, se lo sumamos a los productos (NO al envío)
            if ($aplica_iva && $porcentaje_iva > 0) {
                $multiplicador = 1 + ($porcentaje_iva / 100);
                $totalFinalUsd = $totalProductosUsd * $multiplicador;
                $totalFinalArs = $totalProductosArs * $multiplicador;
            }

            // Por último, agregamos el envío al total en Pesos
            $totalFinalArs += $totalEnviosArs;

            // Actualizamos la compra general
            $compra->update([
                'total_usd' => $totalFinalUsd,
                'total_ars' => $totalFinalArs,
            ]);
        });

        return redirect()->route('compras.index')->with('success', 'Compra registrada correctamente.');
    }

    // ✏️ Editar compra existente
    public function edit(Compra $compra)
    {
        $productos = Producto::orderBy('nombre')->get();
        $categorias = \App\Models\Categoria::orderBy('nombre')->get();
        $compra->load(['detalles' => function ($q) {
            $q->select('id', 'compra_id', 'producto_id', 'cantidad', 'precio_unitario_usd', 'cotizacion_dolar', 'precio_unitario_ars', 'envio_ars'); // 👈 incluimos envio_ars
        }, 'detalles.producto']);

        return view('compras.edit', compact('compra', 'productos', 'categorias'));
    }

    // 🔄 Actualizar compra existente
    public function update(Request $request, Compra $compra)
    {
        $request->validate([
            'fecha' => 'required|date|before_or_equal:today',
            'cotizacion_dolar' => 'required|numeric|min:0',
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
                    'envio_ars' => $item['envio_ars'] ?? 0,
                ]);

                // Actualizar stock y precios
                $producto->increment('stock', $item['cantidad']);
                $producto->update([
                    'precio_compra_usd' => $item['precio_unitario_usd'],
                    'cotizacion_compra' => $request->cotizacion_dolar,
                    'precio_compra_ars' => $item['precio_unitario_usd'] * $request->cotizacion_dolar,
                    'envio_ars' => $item['envio_ars'] ?? 0,
                    'precio_venta_usd' => $item['precio_venta_usd'] ?? $producto->precio_venta_usd,
                    'precio_venta_ars' => $item['precio_venta_ars'] ?? $producto->precio_venta_ars,
                    'porcentaje_ganancia' => $item['ganancia'] ?? $producto->porcentaje_ganancia,
                ]);

                HistorialPrecio::create([
                    'producto_id' => $producto->id,
                    'compra_id' => $compra->id,
                    'precio_compra_usd' => $item['precio_unitario_usd'],
                    'cotizacion' => $request->cotizacion_dolar,
                    'precio_compra_ars' => $item['precio_unitario_usd'] * $request->cotizacion_dolar,
                ]);

                $totalUsd += $subtotalUsd;
                $totalArs += $subtotalArs + (($item['envio_ars'] ?? 0) * $item['cantidad']);
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
                $producto->decrement('stock', $detalle->cantidad);

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

    // ❌ Eliminar un producto individual de una compra
    public function removeItem($detalleId)
    {
        $detalle = DetalleCompra::findOrFail($detalleId);
        $compra = $detalle->compra;

        DB::transaction(function () use ($detalle, $compra) {
            $detalle->producto->decrement('stock', $detalle->cantidad);

            $subtotalUsd = $detalle->cantidad * $detalle->precio_unitario_usd;
            $subtotalArs = $subtotalUsd * $detalle->cotizacion_dolar + ($detalle->envio_ars ?? 0);

            $compra->decrement('total_usd', $subtotalUsd);
            $compra->decrement('total_ars', $subtotalArs);

            $detalle->delete();
        });

        return back()->with('success', 'Producto eliminado de la compra correctamente.');
    }
}
