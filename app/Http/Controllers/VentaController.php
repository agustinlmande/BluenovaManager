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
    // 🔹 Listado de ventas
    public function index()
    {
        $ventas = Venta::with('vendedor', 'detalles.producto')
            ->orderBy('fecha', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        return view('ventas.index', compact('ventas'));
    }

    // 🔹 Formulario nueva venta
    public function create()
    {
        $productos = Producto::orderBy('nombre')->get();
        $vendedores = Vendedor::orderBy('nombre')->get();
        return view('ventas.create', compact('productos', 'vendedores'));
    }

    // 🔹 Guardar nueva venta
    public function store(Request $request)
    {
        $request->validate([
            'fecha' => 'required|date|before_or_equal:today',
            'cotizacion_dolar' => 'required|numeric|min:0',
            'metodo_pago' => 'required|in:efectivo,transferencia',
            'productos' => 'required|array|min:1',
            'productos.*.id' => 'required|exists:productos,id',
            'productos.*.cantidad' => 'required|integer|min:1',
            'productos.*.precio_unitario_ars' => 'required|numeric|min:0',
            'costo_envio' => 'nullable|numeric|min:0',
            'monto_pagado' => 'required|numeric|min:0',
            'porcentaje_comision_vendedor' => 'nullable|numeric|min:0|max:100',
        ]);

        DB::transaction(function () use ($request) {
            $totalArs = 0;
            $totalUsd = 0;
            $gananciaTotal = 0;

            // Vendedor y datos congelados
            $vendedorNombre = null;
            $porcentajeComision = null;

            if ($request->vendedor_id) {
                $vendedor = Vendedor::find($request->vendedor_id);
                $vendedorNombre = $vendedor ? $vendedor->nombre : 'Venta propia';
                $porcentajeComision = $request->porcentaje_comision_vendedor ?? $vendedor->comision_por_defecto ?? 0;
            } else {
                $vendedorNombre = 'Venta propia';
            }

            // Crear la venta base (totales en 0, luego actualizamos)
            $venta = Venta::create([
                'fecha' => $request->fecha,
                'vendedor_id' => $request->vendedor_id,
                'vendedor_nombre' => $vendedorNombre,
                'porcentaje_comision_vendedor' => $porcentajeComision,
                'cotizacion_dolar' => $request->cotizacion_dolar,
                'tipo_entrega' => $request->tipo_entrega ?: null,
                'costo_envio' => $request->costo_envio ?? 0,
                'metodo_pago' => $request->metodo_pago,
                'total_venta_ars' => 0,
                'total_venta_usd' => 0,
                'ganancia_ars' => 0, // 🔹 agregado
                'observaciones' => $request->observaciones,
                'monto_pagado' => 0,
                'saldo_pendiente' => 0,
                'estado_pago' => 'pagado',
            ]);

            // Procesar los productos vendidos
            foreach ($request->productos as $item) {
                $producto = Producto::find($item['id']);
                $cantidad = (int) $item['cantidad'];
                $precioArs = (float) $item['precio_unitario_ars'];

                $subtotalArs = $precioArs * $cantidad;
                $subtotalUsd = $request->cotizacion_dolar > 0
                    ? $subtotalArs / $request->cotizacion_dolar
                    : 0;

                // Reducir stock
                $producto->decrement('stock', $cantidad);

                // Ganancia bruta
                $costoTotalCompra = $producto->precio_compra_ars * $cantidad;
                $gananciaBruta = $subtotalArs - $costoTotalCompra;

                // Comisión del vendedor
                $comision = 0;
                if ($request->vendedor_id && $porcentajeComision !== null) {
                    $comision = ($subtotalArs * $porcentajeComision) / 100;
                }

                $gananciaNeta = $gananciaBruta - $comision;
                $gananciaTotal += $gananciaNeta; // 🔹 acumulamos ganancia total

                DetalleVenta::create([
                    'venta_id' => $venta->id,
                    'producto_id' => $producto->id,
                    'vendedor_id' => $request->vendedor_id,
                    'cantidad' => $cantidad,
                    'precio_unitario_ars' => $precioArs,
                    'precio_unitario_usd' => $cantidad > 0 ? $subtotalUsd / $cantidad : 0,
                    'ganancia_ars' => $gananciaNeta,
                    'porcentaje_ganancia' => $costoTotalCompra > 0
                        ? ($gananciaNeta / $costoTotalCompra) * 100
                        : 0,
                ]);

                $totalArs += $subtotalArs;
                $totalUsd += $subtotalUsd;
            }

            // ✅ total incluye costo de envío
            $totalArs += ($request->costo_envio ?? 0);

            // Pagos
            $montoPagado = (float) $request->monto_pagado;
            $saldoPendiente = max(0, $totalArs - $montoPagado);
            $estadoPago = $saldoPendiente > 0 ? 'pendiente' : 'pagado';

            // ✅ Actualizamos la venta con totales y ganancia
            $venta->update([
                'total_venta_ars' => $totalArs,
                'total_venta_usd' => $totalUsd,
                'ganancia_ars'    => $gananciaTotal, // 🔹 ahora se guarda la ganancia real
                'monto_pagado'    => $montoPagado,
                'saldo_pendiente' => $saldoPendiente,
                'estado_pago'     => $estadoPago,
            ]);
        });

        return redirect()->route('ventas.index')->with('success', 'Venta registrada correctamente.');
    }

    // 🔹 Mostrar / imprimir recibo
    public function show(Venta $venta)
    {
        $venta->load('vendedor', 'detalles.producto');
        return view('ventas.show', compact('venta'));
    }

    // 🔹 Editar venta (solo pagos)
    public function edit(Venta $venta)
    {
        $venta->load('vendedor', 'detalles.producto');
        return view('ventas.edit', compact('venta'));
    }

    // 🔹 Actualizar venta (solo pagos)
    public function update(Request $request, Venta $venta)
    {
        $request->validate([
            'monto_pagado'    => 'required|numeric|min:0',
            'saldo_pendiente' => 'required|numeric|min:0',
            'estado_pago'     => 'required|in:pagado,pendiente',
        ]);

        $total       = (float) $venta->total_venta_ars;
        $montoPagado = (float) $request->monto_pagado;
        $saldo       = (float) $request->saldo_pendiente;

        if ($montoPagado > $total) {
            $montoPagado = $total;
            $saldo = 0;
        } else {
            $saldo = max(0, $total - $montoPagado);
        }

        $estadoPago = $saldo > 0 ? 'pendiente' : 'pagado';

        $venta->update([
            'monto_pagado'    => $montoPagado,
            'saldo_pendiente' => $saldo,
            'estado_pago'     => $estadoPago,
        ]);

        return redirect()->route('ventas.index')->with('success', 'Venta actualizada correctamente.');
    }

    // 🔹 Eliminar venta (restaura stock)
    public function destroy(Venta $venta)
    {
        DB::transaction(function () use ($venta) {
            $venta->load('detalles.producto');

            foreach ($venta->detalles as $detalle) {
                if ($detalle->producto) {
                    $detalle->producto->increment('stock', $detalle->cantidad);
                }
            }

            $venta->detalles()->delete();
            $venta->delete();
        });

        return redirect()->route('ventas.index')->with('success', 'Venta eliminada correctamente.');
    }
}
