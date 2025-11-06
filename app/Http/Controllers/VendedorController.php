<?php

namespace App\Http\Controllers;

use App\Models\Vendedor;
use Illuminate\Http\Request;

class VendedorController extends Controller
{
    public function index()
    {
        $vendedores = Vendedor::orderBy('created_at', 'desc')->get();
        return view('vendedores.index', compact('vendedores'));
    }

    public function create()
    {
        return view('vendedores.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'contacto' => 'nullable|string|max:255',
            'comision_por_defecto' => 'required|numeric|min:0|max:100',
            'observaciones' => 'nullable|string|max:500',
        ]);

        Vendedor::create($request->all());

        return redirect()->route('vendedores.index')->with('success', 'Vendedor agregado correctamente.');
    }

    public function edit(Vendedor $vendedor)
    {
        return view('vendedores.edit', compact('vendedor'));
    }

    public function update(Request $request, Vendedor $vendedor)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'contacto' => 'nullable|string|max:255',
            'comision_por_defecto' => 'required|numeric|min:0|max:100',
            'observaciones' => 'nullable|string|max:500',
        ]);

        $vendedor->update($request->all());

        return redirect()->route('vendedores.index')->with('success', 'Vendedor actualizado correctamente.');
    }

    public function destroy(Vendedor $vendedor)
    {
        $vendedor->delete();
        return redirect()->route('vendedores.index')->with('success', 'Vendedor eliminado correctamente.');
    }
}
