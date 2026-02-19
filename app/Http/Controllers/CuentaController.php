<?php

namespace App\Http\Controllers;

use App\Models\Cuenta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; // ✅ Asegúrate de que esta línea esté aquí

class CuentaController extends Controller
{
    // 🔹 Mostrar todas las cuentas y sus saldos
    public function index()
    {
        $cuentas = Cuenta::orderBy('nombre')->get();
        return view('cuentas.index', compact('cuentas'));
    }

    // 🔹 Guardar una cuenta nueva
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255|unique:cuentas,nombre',
            'saldo_inicial' => 'nullable|numeric|min:0'
        ]);

        Cuenta::create([
            'nombre' => $request->nombre,
            'saldo' => $request->saldo_inicial ?? 0
        ]);

        return redirect()->route('cuentas.index')->with('success', 'Cuenta creada exitosamente.');
    }

    // 🔹 NUEVO: Transferir dinero entre cuentas
    public function transferir(Request $request)
    {
        $request->validate([
            'origen_id' => 'required|exists:cuentas,id',
            'destino_id' => 'required|exists:cuentas,id|different:origen_id',
            'monto' => 'required|numeric|min:0.01',
        ]);

        $origen = Cuenta::find($request->origen_id);
        $destino = Cuenta::find($request->destino_id);

        // Verificamos que la cuenta de origen tenga plata suficiente
        if ($origen->saldo < $request->monto) {
            return back()->with('error', '⚠️ Saldo insuficiente en la cuenta de origen para realizar la transferencia.');
        }

        // Hacemos el movimiento seguro
        DB::transaction(function () use ($origen, $destino, $request) {
            $origen->decrement('saldo', $request->monto);
            $destino->increment('saldo', $request->monto);
        });

        return redirect()->route('cuentas.index')->with('success', '✅ Transferencia de $' . number_format($request->monto, 2, ',', '.') . ' realizada con éxito de ' . $origen->nombre . ' a ' . $destino->nombre . '.');
    }
}
