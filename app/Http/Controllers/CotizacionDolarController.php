<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CotizacionDolar;

class CotizacionDolarController extends Controller
{
    public function index()
    {
        $cotizaciones = CotizacionDolar::orderBy('fecha', 'desc')->get();
        return view('cotizacion.index', compact('cotizaciones'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'valor_usd' => 'required|numeric|min:0',
        ]);

        CotizacionDolar::create([
            'valor_usd' => $request->valor_usd,
            'fecha' => now(),
        ]);

        return redirect()->route('cotizacion.index')->with('success', 'Cotización agregada correctamente.');
    }
}
