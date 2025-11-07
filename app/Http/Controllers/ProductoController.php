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
            'nombre' => 'required|string|max:255',
            'categoria_id' => 'required|exists:categorias,id',
        ]);

        Producto::create([
            'nombre' => $request->nombre,
            'categoria_id' => $request->categoria_id,
            'descripcion' => '',
            'stock' => 0,
            'precio_compra_usd' => 0,
            'cotizacion_compra' => 0,
            'precio_compra_ars' => 0,
            'precio_venta_usd' => 0,
            'precio_venta_ars' => 0,
            'porcentaje_ganancia' => 0,
        ]);

        return redirect()->route('productos.index')->with('success', 'Producto creado correctamente.');
    }


    public function storeAjax(Request $request)
    {
        try {
            $request->validate([
                'nombre' => 'required|string|max:255',
                'categoria_id' => 'nullable|exists:categorias,id',
                'precio_compra_usd' => 'nullable|numeric|min:0',
                'cotizacion_compra' => 'nullable|numeric|min:0',
                'precio_venta_ars' => 'nullable|numeric|min:0',
                'precio_venta_usd' => 'nullable|numeric|min:0',
                'porcentaje_ganancia' => 'nullable|numeric|min:0',
                'stock' => 'nullable|integer|min:0',
            ]);

            // ✅ Asignar valores por defecto si no se enviaron
            $precio_compra_usd = $request->precio_compra_usd ?? 0;
            $cotizacion_compra = $request->cotizacion_compra ?? 0;
            $precio_compra_ars = $cotizacion_compra > 0
                ? $precio_compra_usd * $cotizacion_compra
                : 0;

            $producto = Producto::create([
                'nombre' => $request->nombre,
                'categoria_id' => $request->categoria_id,
                'descripcion' => $request->descripcion ?? '',
                'stock' => $request->stock ?? 0,
                'precio_compra_usd' => $precio_compra_usd,
                'cotizacion_compra' => $cotizacion_compra,
                'precio_compra_ars' => $precio_compra_ars,
                'precio_venta_usd' => $request->precio_venta_usd ?? 0,
                'precio_venta_ars' => $request->precio_venta_ars ?? 0,
                'porcentaje_ganancia' => $request->porcentaje_ganancia ?? 0,
            ]);

            return response()->json([
                'success' => true,
                'producto' => $producto,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }



    // Mostrar formulario de edición
    public function edit(Producto $producto)
    {
        $producto->load(['categoria', 'ultimoProveedor']); // 👈 cargamos el último proveedor
        $categorias = Categoria::all();

        return view('productos.edit', compact('producto', 'categorias'));
    }


    // Actualizar producto
    // Actualizar producto
    public function update(Request $request, Producto $producto)
    {
        $request->validate([
            'cotizacion_dolar'      => 'required|numeric|min:0',
            'precio_unitario_usd'   => 'required|numeric|min:0',
            'costo_envio_ars'       => 'required|numeric|min:0',
            'ganancia_porcentaje'   => 'nullable|numeric|min:0',
            'precio_venta_usd'      => 'nullable|numeric|min:0',
            'precio_venta_ars'      => 'nullable|numeric|min:0',
        ]);

        // === Variables base ===
        $cotizacion = $request->cotizacion_dolar;
        $precioUSD  = $request->precio_unitario_usd;
        $envioARS   = $request->costo_envio_ars;
        $ganancia   = $request->ganancia_porcentaje ?? 0;
        $ventaUSD   = $request->precio_venta_usd;
        $ventaARS   = $request->precio_venta_ars;

        // === COSTO TOTAL REAL (en ARS y USD) ===
        $costoARS = ($precioUSD * $cotizacion) + $envioARS; // ejemplo: 1*1000 + 1000 = 2000
        $costoUSD = $costoARS / $cotizacion;                // 2000/1000 = 2 USD

        // === Calcular valores faltantes ===
        if ($ganancia > 0 && (empty($ventaUSD) || empty($ventaARS))) {
            // Si solo hay % ganancia → calcular venta completa
            $ventaUSD = $costoUSD * (1 + $ganancia / 100);
            $ventaARS = $ventaUSD * $cotizacion;
        } elseif (!empty($ventaUSD) && empty($ventaARS)) {
            // Si hay venta USD → calcular ARS y ganancia
            $ventaARS = $ventaUSD * $cotizacion;
            $ganancia = (($ventaUSD - $costoUSD) / $costoUSD) * 100;
        } elseif (!empty($ventaARS) && empty($ventaUSD)) {
            // Si hay venta ARS → calcular USD y ganancia
            $ventaUSD = $ventaARS / $cotizacion;
            $ganancia = (($ventaUSD - $costoUSD) / $costoUSD) * 100;
        }

        // === Validaciones de coherencia ===
        if ($ventaUSD < $costoUSD) {
            return back()->withErrors([
                'precio_venta_usd' => 'El precio de venta no puede ser menor al costo total (producto + envío).'
            ]);
        }

        // === Guardar valores en columnas correctas ===
        $producto->update([
            'cotizacion_compra'     => round($cotizacion, 2),
            'precio_compra_usd'     => round($precioUSD, 2),
            'precio_compra_ars'     => round($precioUSD * $cotizacion, 2),
            'porcentaje_ganancia'   => round($ganancia, 2),
            'precio_venta_usd'      => round($ventaUSD, 2),
            'precio_venta_ars'      => round($ventaARS, 2),
        ]);

        return redirect()
            ->route('productos.index')
            ->with('success', 'Producto actualizado correctamente.');
    }
}
