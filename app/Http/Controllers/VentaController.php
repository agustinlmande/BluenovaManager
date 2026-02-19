<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use App\Models\DetalleVenta;
use App\Models\Producto;
use App\Models\Vendedor;
use App\Models\Cuenta; // ✅ Importamos el modelo de Cuentas
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VentaController extends Controller
{
    // 🔹 Listado de ventas
    public function index()
    {
        $ventas = Venta::with('vendedor', 'detalles.producto', 'cuenta')
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
        $cuentas = Cuenta::orderBy('nombre')->get(); // ✅ Enviamos las cuentas a la vista
        return view('ventas.create', compact('productos', 'vendedores', 'cuentas'));
    }

    // 🔹 Guardar nueva venta
    public function store(Request $request)
    {
        $request->validate([
            'fecha' => 'required|date|before_or_equal:today',
            'cotizacion_dolar' => 'required|numeric|min:0',
            'metodo_pago' => 'required|in:efectivo,transferencia',
            'facturado' => 'required|boolean', // ✅ Validamos el nuevo campo
            'cuenta_id' => 'required|exists:cuentas,id', // ✅ Validamos la cuenta de ingreso
            'productos' => 'required|array|min:1',
            'productos.*.id' => 'required|exists:productos,id',
            'productos.*.cantidad' => 'required|integer|min:1',
            'productos.*.precio_unitario_ars' => 'required|numeric|min:0',
            'costo_envio' => 'nullable|numeric|min:0',
            'monto_pagado' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($request) {
            $totalArs = 0;
            $totalUsd = 0;
            $gananciaTotal = 0;

            $vendedorNombre = null;
            $porcentajeComision = null;

            if ($request->vendedor_id) {
                $vendedor = Vendedor::find($request->vendedor_id);
                $vendedorNombre = $vendedor ? $vendedor->nombre : 'Venta propia';
                $porcentajeComision = $request->porcentaje_comision_vendedor ?? $vendedor->comision_por_defecto ?? 0;
            } else {
                $vendedorNombre = 'Venta propia';
            }

            $venta = Venta::create([
                'fecha' => $request->fecha,
                'vendedor_id' => $request->vendedor_id,
                'vendedor_nombre' => $vendedorNombre,
                'porcentaje_comision_vendedor' => $porcentajeComision,
                'cotizacion_dolar' => $request->cotizacion_dolar,
                'tipo_entrega' => $request->tipo_entrega ?: null,
                'costo_envio' => $request->costo_envio ?? 0,
                'metodo_pago' => $request->metodo_pago,
                'facturado' => $request->facturado, // ✅ Guardamos si se facturó
                'cuenta_id' => $request->cuenta_id, // ✅ Guardamos a qué cuenta fue el dinero
                'total_venta_ars' => 0,
                'total_venta_usd' => 0,
                'ganancia_ars' => 0,
                'observaciones' => $request->observaciones,
                'monto_pagado' => 0,
                'saldo_pendiente' => 0,
                'estado_pago' => 'pagado',
            ]);

            foreach ($request->productos as $item) {
                $producto = Producto::find($item['id']);
                $cantidad = (int) $item['cantidad'];
                $precioArs = (float) $item['precio_unitario_ars'];

                $subtotalArs = $precioArs * $cantidad;
                $subtotalUsd = $request->cotizacion_dolar > 0 ? $subtotalArs / $request->cotizacion_dolar : 0;

                // 📉 Reducir stock
                $producto->decrement('stock', $cantidad);

                // 🧮 Lógica de Ganancia Real
                // Si es facturado, lo que realmente te queda es el precio / 1.21
                $precioNetoVenta = $request->facturado ? ($precioArs / 1.21) : $precioArs;

                // Costo real (Precio compra + envío cargado en la compra)
                $costoTotalCompra = ($producto->precio_compra_ars + $producto->envio_ars) * $cantidad;

                // Ganancia bruta = Venta Neta - Costo
                $gananciaBruta = ($precioNetoVenta * $cantidad) - $costoTotalCompra;

                // Comisión del vendedor (calculada sobre el total cobrado al cliente)
                $comision = 0;
                if ($request->vendedor_id && $porcentajeComision !== null) {
                    $comision = ($subtotalArs * $porcentajeComision) / 100;
                }

                $gananciaNeta = $gananciaBruta - $comision;
                $gananciaTotal += $gananciaNeta;

                DetalleVenta::create([
                    'venta_id' => $venta->id,
                    'producto_id' => $producto->id,
                    'vendedor_id' => $request->vendedor_id,
                    'cantidad' => $cantidad,
                    'precio_unitario_ars' => $precioArs,
                    'precio_unitario_usd' => $cantidad > 0 ? $subtotalUsd / $cantidad : 0,
                    'ganancia_ars' => $gananciaNeta,
                    'porcentaje_ganancia' => $costoTotalCompra > 0 ? ($gananciaNeta / $costoTotalCompra) * 100 : 0,
                ]);

                $totalArs += $subtotalArs;
                $totalUsd += $subtotalUsd;
            }

            // Sumar envío al total final
            $totalArs += ($request->costo_envio ?? 0);

            // Descontar comisión global del total de la venta (según tu lógica actual)
            if ($request->vendedor_id && $porcentajeComision !== null && $porcentajeComision > 0) {
                $comisionGlobal = ($totalArs * $porcentajeComision) / 100;
                $totalArs -= $comisionGlobal;
            }

            $montoPagado = (float) $request->monto_pagado;
            $saldoPendiente = max(0, $totalArs - $montoPagado);
            $estadoPago = $saldoPendiente > 0 ? 'pendiente' : 'pagado';

            $venta->update([
                'total_venta_ars' => $totalArs,
                'total_venta_usd' => $totalUsd,
                'ganancia_ars'    => $gananciaTotal,
                'monto_pagado'    => $montoPagado,
                'saldo_pendiente' => $saldoPendiente,
                'estado_pago'     => $estadoPago,
            ]);

            // ✅ Sincronizar con la Billetera/Banco elegido
            if ($montoPagado > 0) {
                $cuenta = Cuenta::find($request->cuenta_id);
                if ($cuenta) $cuenta->increment('saldo', $montoPagado);
            }
        });

        return redirect()->route('ventas.index')->with('success', 'Venta registrada correctamente.');
    }

    // ✏️ Actualizar venta (solo pagos)
    public function update(Request $request, Venta $venta)
    {
        $request->validate([
            'monto_pagado'    => 'required|numeric|min:0',
            'saldo_pendiente' => 'required|numeric|min:0',
            'estado_pago'     => 'required|in:pagado,pendiente',
        ]);

        $total = (float) $venta->total_venta_ars;
        $nuevoMontoPagado = (float) $request->monto_pagado;

        // Ajustar el saldo de la cuenta si el pago cambió
        $diferencia = $nuevoMontoPagado - $venta->monto_pagado;
        if ($diferencia != 0 && $venta->cuenta_id) {
            $cuenta = Cuenta::find($venta->cuenta_id);
            if ($cuenta) $cuenta->increment('saldo', $diferencia);
        }

        $saldo = max(0, $total - $nuevoMontoPagado);
        $estadoPago = $saldo > 0 ? 'pendiente' : 'pagado';

        $venta->update([
            'monto_pagado'    => $nuevoMontoPagado,
            'saldo_pendiente' => $saldo,
            'estado_pago'     => $estadoPago,
        ]);

        return redirect()->route('ventas.index')->with('success', 'Pago actualizado correctamente.');
    }

    // 🔹 Mostrar / imprimir recibo
    public function show(Venta $venta)
    {
        // Cargamos las relaciones incluyendo la nueva 'cuenta'
        $venta->load('vendedor', 'detalles.producto', 'cuenta');
        return view('ventas.show', compact('venta'));
    }

    // 🔹 Editar venta (solo pagos)
    public function edit(Venta $venta)
    {
        $venta->load('vendedor', 'detalles.producto', 'cuenta');
        return view('ventas.edit', compact('venta'));
    }

    // 🗑️ Eliminar venta
    public function destroy(Venta $venta)
    {
        DB::transaction(function () use ($venta) {
            foreach ($venta->detalles as $detalle) {
                if ($detalle->producto) {
                    $detalle->producto->increment('stock', $detalle->cantidad);
                }
            }

            // Devolver la plata de la cuenta
            if ($venta->monto_pagado > 0 && $venta->cuenta_id) {
                $cuenta = Cuenta::find($venta->cuenta_id);
                if ($cuenta) $cuenta->decrement('saldo', $venta->monto_pagado);
            }

            $venta->detalles()->delete();
            $venta->delete();
        });

        return redirect()->route('ventas.index')->with('success', 'Venta eliminada correctamente.');
    }
}
