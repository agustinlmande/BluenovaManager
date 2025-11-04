<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Categoria;
use Illuminate\Http\Request;

class ProductoController extends Controller
{
    // Mostrar todos los productos
    public function index(Request $request)
    {
        $categorias = Categoria::orderBy('nombre')->get();

        $query = Producto::with('categoria')->orderBy('id', 'desc');

        // 🔹 Filtro por categoría
        if ($request->filled('categoria_id')) {
            $query->where('categoria_id', $request->categoria_id);
        }

        // 🔹 Búsqueda por nombre, descripción, ID o precio
        if ($request->filled('buscar')) {
            $busqueda = $request->buscar;
            $query->where(function ($q) use ($busqueda) {
                $q->where('nombre', 'like', "%$busqueda%")
                    ->orWhere('descripcion', 'like', "%$busqueda%")
                    ->orWhere('id', 'like', "%$busqueda%")
                    ->orWhere('precio_venta_ars', 'like', "%$busqueda%");
            });
        }

        $productos = $query->get();

        return view('productos.index', compact('productos', 'categorias'));
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
        ]);

        $precio_compra_usd = $request->precio_compra_usd;
        $cotizacion = $request->cotizacion_compra;
        $precio_compra_ars = $precio_compra_usd * $cotizacion;

        $precio_venta_ars = $request->precio_venta_ars;
        $precio_venta_usd = $request->precio_venta_usd;
        $porcentaje_ganancia = $request->porcentaje_ganancia;

        // 🔹 Calcular valores faltantes
        if ($precio_venta_ars && !$precio_venta_usd) {
            $precio_venta_usd = $precio_venta_ars / $cotizacion;
            $porcentaje_ganancia = (($precio_venta_ars - $precio_compra_ars) / $precio_compra_ars) * 100;
        } elseif ($precio_venta_usd && !$precio_venta_ars) {
            $precio_venta_ars = $precio_venta_usd * $cotizacion;
            $porcentaje_ganancia = (($precio_venta_ars - $precio_compra_ars) / $precio_compra_ars) * 100;
        } elseif ($porcentaje_ganancia && !$precio_venta_ars && !$precio_venta_usd) {
            $precio_venta_ars = $precio_compra_ars + ($precio_compra_ars * $porcentaje_ganancia / 100);
            $precio_venta_usd = $precio_venta_ars / $cotizacion;
        }

        // Redondear valores
        $precio_venta_ars = round($precio_venta_ars, 2);
        $precio_venta_usd = round($precio_venta_usd, 2);
        $porcentaje_ganancia = round($porcentaje_ganancia, 2);

        Producto::create([
            'nombre' => $request->nombre,
            'categoria_id' => $request->categoria_id,
            'descripcion' => $request->descripcion,
            'imagen' => $request->imagen,
            'stock' => $request->stock ?? 0,
            'precio_compra_usd' => $precio_compra_usd,
            'cotizacion_compra' => $cotizacion,
            'precio_compra_ars' => $precio_compra_ars,
            'precio_venta_usd' => $precio_venta_usd,
            'precio_venta_ars' => $precio_venta_ars,
            'porcentaje_ganancia' => $porcentaje_ganancia,
        ]);

        return redirect()->route('productos.index')->with('success', 'Producto creado correctamente.');
    }

    public function storeAjax(Request $request)
{
    $request->validate([
        'nombre' => 'required|string|max:255',
        'categoria_id' => 'required|exists:categorias,id',
        'precio_compra_usd' => 'required|numeric|min:0',
        'cotizacion_compra' => 'required|numeric|min:0',
        'precio_venta_ars' => 'nullable|numeric|min:0',
        'precio_venta_usd' => 'nullable|numeric|min:0',
        'porcentaje_ganancia' => 'nullable|numeric|min:0',
        'stock' => 'nullable|integer|min:0',
    ]);

    $precio_compra_ars = $request->precio_compra_usd * $request->cotizacion_compra;

    $producto = Producto::create([
        'nombre' => $request->nombre,
        'categoria_id' => $request->categoria_id,
        'descripcion' => $request->descripcion,
        'stock' => $request->stock ?? 0,
        'precio_compra_usd' => $request->precio_compra_usd,
        'cotizacion_compra' => $request->cotizacion_compra,
        'precio_compra_ars' => $precio_compra_ars,
        'precio_venta_usd' => $request->precio_venta_usd ?? 0,
        'precio_venta_ars' => $request->precio_venta_ars ?? 0,
        'porcentaje_ganancia' => $request->porcentaje_ganancia ?? 0,
    ]);

    return response()->json([
        'success' => true,
        'producto' => $producto,
    ]);
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
        ]);

        $precio_compra_usd = $request->precio_compra_usd;
        $cotizacion = $request->cotizacion_compra;
        $precio_compra_ars = $precio_compra_usd * $cotizacion;

        $precio_venta_ars = $request->precio_venta_ars;
        $precio_venta_usd = $request->precio_venta_usd;
        $porcentaje_ganancia = $request->porcentaje_ganancia;

        if ($precio_venta_ars && !$precio_venta_usd) {
            $precio_venta_usd = $precio_venta_ars / $cotizacion;
            $porcentaje_ganancia = (($precio_venta_ars - $precio_compra_ars) / $precio_compra_ars) * 100;
        } elseif ($precio_venta_usd && !$precio_venta_ars) {
            $precio_venta_ars = $precio_venta_usd * $cotizacion;
            $porcentaje_ganancia = (($precio_venta_ars - $precio_compra_ars) / $precio_compra_ars) * 100;
        } elseif ($porcentaje_ganancia && !$precio_venta_ars && !$precio_venta_usd) {
            $precio_venta_ars = $precio_compra_ars + ($precio_compra_ars * $porcentaje_ganancia / 100);
            $precio_venta_usd = $precio_venta_ars / $cotizacion;
        }

        $precio_venta_ars = round($precio_venta_ars, 2);
        $precio_venta_usd = round($precio_venta_usd, 2);
        $porcentaje_ganancia = round($porcentaje_ganancia, 2);

        $producto->update([
            'nombre' => $request->nombre,
            'categoria_id' => $request->categoria_id,
            'descripcion' => $request->descripcion,
            'imagen' => $request->imagen,
            'stock' => $request->stock ?? 0,
            'precio_compra_usd' => $precio_compra_usd,
            'cotizacion_compra' => $cotizacion,
            'precio_compra_ars' => $precio_compra_ars,
            'precio_venta_usd' => $precio_venta_usd,
            'precio_venta_ars' => $precio_venta_ars,
            'porcentaje_ganancia' => $porcentaje_ganancia,
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
