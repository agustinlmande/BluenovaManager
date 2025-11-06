<?php

namespace App\Http\Controllers;

use App\Models\Caja;
use Illuminate\Http\Request;

class CajaController extends Controller
{
    public function index()
    {
        $movimientos = Caja::orderBy('fecha', 'desc')->get();
        return view('caja.index', compact('movimientos'));
    }

    public function create()
    {
        return view('caja.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'tipo' => 'required|in:ingreso,egreso',
            'monto' => 'required|numeric|min:0',
            'fecha' => 'required|date|before_or_equal:today',
        ]);

        Caja::create([
            'tipo' => $request->tipo,
            'monto' => $request->monto,
            'motivo' => $request->motivo,
            'fecha' => $request->fecha,
        ]);

        return redirect()->route('caja.index')->with('success', 'Movimiento registrado correctamente.');
    }

    public function edit(Caja $caja)
    {
        if (!$caja->editable) {
            return back()->with('error', 'Este movimiento no puede ser editado (proviene de una venta o compra).');
        }
        return view('caja.edit', compact('caja'));
    }

    public function update(Request $request, Caja $caja)
    {
        if (!$caja->editable) {
            return back()->with('error', 'Este movimiento no puede ser modificado.');
        }

        $request->validate([
            'tipo' => 'required|in:ingreso,egreso',
            'monto' => 'required|numeric|min:0',
            'fecha' => 'required|date|before_or_equal:today',
        ]);

        $caja->update($request->only('tipo', 'monto', 'motivo', 'fecha'));

        return redirect()->route('caja.index')->with('success', 'Movimiento actualizado correctamente.');
    }

    public function destroy(Caja $caja)
    {
        if (!$caja->editable) {
            return back()->with('error', 'No se puede eliminar un registro automático.');
        }

        $caja->delete();
        return redirect()->route('caja.index')->with('success', 'Movimiento eliminado.');
    }
}
