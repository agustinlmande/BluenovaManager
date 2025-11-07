<?php

namespace App\Http\Controllers;

use App\Models\Caja;
use App\Models\Compra;
use App\Models\Venta;
use Illuminate\Http\Request;

class CajaController extends Controller
{
    // =============================
    // LISTADO GENERAL DE MOVIMIENTOS
    // =============================
    public function index()
    {
        // 🔹 Movimientos manuales (ingresos / egresos)
        $movimientos = Caja::select('id', 'tipo', 'monto', 'motivo', 'fecha', 'created_at')
            ->get()
            ->map(function ($item) {
                return [
                    'id'         => $item->id,
                    'tipo'       => ucfirst($item->tipo),
                    'monto'      => $item->monto,
                    'motivo'     => $item->motivo ?? '-',
                    'fecha'      => $item->fecha,
                    'created_at' => $item->created_at,
                    'editable'   => $item->editable ?? true,
                    'origen'     => 'Caja manual',
                ];
            });

        // 🔹 Ventas (ingresos automáticos)
        $ventas = Venta::select('id', 'total_venta_ars as monto', 'created_at')
            ->get()
            ->map(function ($venta) {
                return [
                    'id'         => $venta->id,
                    'tipo'       => 'Venta',
                    'monto'      => $venta->monto,
                    'motivo'     => 'Venta registrada',
                    'fecha'      => $venta->created_at,
                    'created_at' => $venta->created_at,
                    'editable'   => false,
                    'origen'     => 'Venta',
                ];
            });

        // 🔹 Compras (egresos automáticos)
        $compras = Compra::select('id', 'total_ars as monto', 'fecha', 'created_at')
            ->get()
            ->map(function ($compra) {
                return [
                    'id'         => $compra->id,
                    'tipo'       => 'Compra',
                    'monto'      => $compra->monto,
                    'motivo'     => 'Compra registrada',
                    'fecha'      => $compra->fecha,
                    'created_at' => $compra->created_at ?? $compra->fecha,
                    'editable'   => false,
                    'origen'     => 'Compra',
                ];
            });

        // 🔹 Unir todos los movimientos
        $todos = collect()
            ->merge($movimientos)
            ->merge($ventas)
            ->merge($compras)
            ->map(function ($item) {
                $base = $item['created_at'] ?? $item['fecha'];
                $item['orden'] = strtotime($base ?? now());
                return $item;
            });

        // =============================
        // CÁLCULOS
        // =============================
        $saldo = 0;
        $totalIngresos = 0;
        $totalEgresos  = 0;
        $totalVentas   = 0;
        $totalCompras  = 0;

        // Orden ascendente para calcular el saldo correctamente
        $todosAsc = $todos->sortBy('orden')->values();

        $todosAsc = $todosAsc->map(function ($mov) use (&$saldo, &$totalIngresos, &$totalEgresos, &$totalVentas, &$totalCompras) {
            $tipo = strtolower($mov['tipo']);

            if ($tipo === 'ingreso') {
                $saldo += $mov['monto'];
                $totalIngresos += $mov['monto'];
            } elseif ($tipo === 'venta') {
                $saldo += $mov['monto'];
                $totalVentas += $mov['monto'];
            } elseif ($tipo === 'egreso') {
                $saldo -= $mov['monto'];
                $totalEgresos += $mov['monto'];
            } elseif ($tipo === 'compra') {
                $saldo -= $mov['monto'];
                $totalCompras += $mov['monto'];
            }

            $mov['saldo'] = $saldo;
            return $mov;
        });

        // Orden descendente solo para mostrar los más nuevos arriba
        $todosOrdenados = $todosAsc->sortByDesc('orden')->values();

        // =============================
        // CÁLCULOS ADICIONALES
        // =============================
        $saldoFinal = $saldo;

        // 🔸 Ventas pendientes (clientes que deben)
        $deudaClientes = Venta::where('estado_pago', 'pendiente')->sum('saldo_pendiente');

        // 🔸 GANANCIA REAL (desde ventas)
        $gananciaReal = Venta::sum('ganancia_ars') ?: 0;

        // ✅ Guardar el saldo actual calculado en la base
        Caja::query()->update(['saldo_actual' => $saldoFinal]);

        return view('caja.index', [
            'todos'            => $todosOrdenados,
            'saldo'            => $saldoFinal,
            'totalIngresos'    => $totalIngresos,
            'totalEgresos'     => $totalEgresos,
            'totalVentas'      => $totalVentas,
            'totalCompras'     => $totalCompras,
            'gananciaEstimada' => $gananciaReal,
            'deudaClientes'    => $deudaClientes,
        ]);
    }

    // =============================
    // FORMULARIO NUEVO MOVIMIENTO
    // =============================
    public function create()
    {
        return view('caja.create');
    }

    // =============================
    // GUARDAR MOVIMIENTO
    // =============================
    public function store(Request $request)
    {
        $request->validate([
            'tipo'  => 'required|in:ingreso,egreso',
            'monto' => 'required|numeric|min:0',
            'fecha' => 'required|date',
        ]);

        $fecha = str_replace('T', ' ', $request->fecha);

        Caja::create([
            'tipo'     => $request->tipo,
            'monto'    => $request->monto,
            'motivo'   => $request->motivo,
            'fecha'    => $fecha,
            'editable' => true,
        ]);

        // ✅ Recalcular el saldo actual después de guardar
        $saldoActual = self::obtenerSaldoActual();
        Caja::query()->latest('id')->update(['saldo_actual' => $saldoActual]);

        return redirect()->route('caja.index')->with('success', 'Movimiento registrado correctamente.');
    }

    // =============================
    // EDITAR MOVIMIENTO
    // =============================
    public function edit(Caja $caja)
    {
        if (!$caja->editable) {
            return back()->with('error', 'Este movimiento no puede ser editado (proviene de una venta o compra).');
        }

        return view('caja.edit', compact('caja'));
    }

    // =============================
    // ACTUALIZAR MOVIMIENTO
    // =============================
    public function update(Request $request, Caja $caja)
    {
        if (!$caja->editable) {
            return back()->with('error', 'Este movimiento no puede ser modificado.');
        }

        $request->validate([
            'tipo'  => 'required|in:ingreso,egreso',
            'monto' => 'required|numeric|min:0',
            'fecha' => 'required|date|before_or_equal:now',
        ]);

        $fecha = str_replace('T', ' ', $request->fecha);

        $caja->update([
            'tipo'   => $request->tipo,
            'monto'  => $request->monto,
            'motivo' => $request->motivo,
            'fecha'  => $fecha,
        ]);

        // ✅ Actualizar saldo_actual
        $saldoActual = self::obtenerSaldoActual();
        Caja::query()->latest('id')->update(['saldo_actual' => $saldoActual]);

        return redirect()->route('caja.index')->with('success', 'Movimiento actualizado correctamente.');
    }

    // =============================
    // ELIMINAR MOVIMIENTO
    // =============================
    public function destroy(Caja $caja)
    {
        if (!$caja->editable) {
            return back()->with('error', 'No se puede eliminar un registro automático.');
        }

        $caja->delete();

        // ✅ Actualizar saldo_actual después de borrar
        $saldoActual = self::obtenerSaldoActual();
        Caja::query()->latest('id')->update(['saldo_actual' => $saldoActual]);

        return redirect()->route('caja.index')->with('success', 'Movimiento eliminado correctamente.');
    }

    // =============================
    // CÁLCULO DEL SALDO ACTUAL
    // =============================
    public static function obtenerSaldoActual()
    {
        $saldo = 0;

        // Movimientos manuales
        $movimientos = \App\Models\Caja::all();
        foreach ($movimientos as $mov) {
            if ($mov->tipo === 'ingreso') {
                $saldo += $mov->monto;
            } elseif ($mov->tipo === 'egreso') {
                $saldo -= $mov->monto;
            }
        }

        // Ventas y compras
        $saldo += \App\Models\Venta::sum('total_venta_ars');
        $saldo -= \App\Models\Compra::sum('total_ars');

        return $saldo;
    }
}
