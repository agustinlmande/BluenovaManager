<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Categoria;
use Illuminate\Http\Request;

class ProductoController extends Controller
{
    // Mostrar todos los productos
    public function index()
    {
        $productos = Producto::with('categoria')->orderBy('id', 'desc')->get();
        return view('productos.index', compact('productos'));
    }

    // Mostrar formulario de creación
    public function create()
    {
        $categorias = Categoria::all();
        return view('productos.create', compact('categorias'));
    }

    // Guardar nuevo producto
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required',
            'categoria_id' => 'required|exists:categorias,id',
            'precio_compra_usd' => 'required|numeric',
            'cotizacion_compra' => 'required|numeric',
            'precio_venta_ars' => 'required|numeric',
            'modo_calculo' => 'required',
        ]);

        $precio_compra_ars = $request->precio_compra_usd * $request->cotizacion_compra;

        $precio_venta_ars = $request->precio_venta_ars;
        $precio_venta_usd = $request->precio_venta_usd;
        $porcentaje_ganancia = $request->porcentaje_ganancia;

        // Si falta alguno de los tres, lo calculamos
        if (!$precio_venta_ars && $porcentaje_ganancia) {
            $precio_venta_ars = $precio_compra_ars + ($precio_compra_ars * $porcentaje_ganancia / 100);
            $precio_venta_usd = $precio_venta_ars / $request->cotizacion_compra;
        } elseif (!$precio_venta_ars && $precio_venta_usd) {
            $precio_venta_ars = $precio_venta_usd * $request->cotizacion_compra;
            $porcentaje_ganancia = (($precio_venta_ars - $precio_compra_ars) / $precio_compra_ars) * 100;
        } elseif (!$porcentaje_ganancia && $precio_venta_ars) {
            $porcentaje_ganancia = (($precio_venta_ars - $precio_compra_ars) / $precio_compra_ars) * 100;
            $precio_venta_usd = $precio_venta_ars / $request->cotizacion_compra;
        }


        Producto::create([
            'nombre' => $request->nombre,
            'categoria_id' => $request->categoria_id,
            'descripcion' => $request->descripcion,
            'imagen' => $request->imagen,
            'stock' => $request->stock ?? 0,
            'precio_compra_usd' => $request->precio_compra_usd,
            'cotizacion_compra' => $request->cotizacion_compra,
            'precio_compra_ars' => $precio_compra_ars,
            'precio_venta_usd' => $request->precio_venta_usd,
            'precio_venta_ars' => $request->precio_venta_ars,
            'porcentaje_ganancia' => $request->porcentaje_ganancia,
            'modo_calculo' => $request->modo_calculo,
        ]);

        return redirect()->route('productos.index')->with('success', 'Producto creado correctamente.');
    }

    // Mostrar formulario de edición
    public function edit(Producto $producto)
    {
        $categorias = Categoria::all();
        return view('productos.edit', compact('producto', 'categorias'));
    }

    // Actualizar producto
    public function update(Request $request, Producto $producto)
    {
        $request->validate([
            'nombre' => 'required',
            'categoria_id' => 'required|exists:categorias,id',
            'precio_compra_usd' => 'required|numeric',
            'cotizacion_compra' => 'required|numeric',
            'precio_venta_ars' => 'required|numeric',
            'modo_calculo' => 'required',
        ]);

        $precio_compra_ars = $request->precio_compra_usd * $request->cotizacion_compra;

        $producto->update([
            'nombre' => $request->nombre,
            'categoria_id' => $request->categoria_id,
            'descripcion' => $request->descripcion,
            'imagen' => $request->imagen,
            'stock' => $request->stock ?? 0,
            'precio_compra_usd' => $request->precio_compra_usd,
            'cotizacion_compra' => $request->cotizacion_compra,
            'precio_compra_ars' => $precio_compra_ars,
            'precio_venta_usd' => $request->precio_venta_usd,
            'precio_venta_ars' => $request->precio_venta_ars,
            'porcentaje_ganancia' => $request->porcentaje_ganancia,
            'modo_calculo' => $request->modo_calculo,
        ]);

        return redirect()->route('productos.index')->with('success', 'Producto actualizado correctamente.');
    }

    // Eliminar producto
    public function destroy(Producto $producto)
    {
        $producto->delete();
        return redirect()->route('productos.index')->with('success', 'Producto eliminado correctamente.');
    }
}
