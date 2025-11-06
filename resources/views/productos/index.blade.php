@extends('layouts.app')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-3">
    <h1>Stock de productos</h1>
    <div>
        <a href="{{ route('categorias.index') }}" class="btn btn-outline-primary">Gestionar categorías</a>
    </div>
</div>

<div class="container">

    <a href="{{ route('productos.create') }}" class="btn btn-primary mb-3">Nuevo Producto</a>

    @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="d-flex align-items-center mb-3">
        <form action="{{ route('productos.index') }}" method="GET" class="w-100 d-flex align-items-center gap-2">
            {{-- Mantener categoría seleccionada --}}
            @if(request('categoria_id'))
            <input type="hidden" name="categoria_id" value="{{ request('categoria_id') }}">
            @endif

            {{-- Campo de búsqueda ocupa todo el ancho --}}
            <input type="text" name="buscar" class="form-control w-100" placeholder="Buscar producto..."
                value="{{ request('buscar') }}">

            {{-- Botón buscar --}}
            <button type="submit" class="btn btn-outline-primary">
                <i class="bi bi-search">Buscar</i>
            </button>

            <!-- {{-- Botón filtro --}}
            <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal"
                data-bs-target="#modalFiltroCategoria" title="Filtrar por categoría">
                <i class="bi bi-funnel"></i>
            </button> -->
        </form>
    </div>

    {{-- 🏷️ Filtros activos --}}
    @if(request('buscar') || request('categoria_id'))
    <div class="mt-2 d-flex flex-wrap align-items-center gap-2">
        @if(request('buscar'))
        <span class="badge bg-light text-dark border d-flex align-items-center">
            <strong class="me-1">Búsqueda:</strong> "{{ request('buscar') }}"
            <a href="{{ route('productos.index', collect(request()->except('buscar'))->toArray()) }}"
                class="ms-2 text-danger text-decoration-none fw-bold">&times;</a>
        </span>
        @endif

        @if(request('categoria_id'))
        @php
        $categoriaNombre = $categorias->firstWhere('id', request('categoria_id'))->nombre ?? 'Desconocida';
        @endphp
        <span class="badge bg-light text-dark border d-flex align-items-center">
            <strong class="me-1">Filtro:</strong> {{ $categoriaNombre }}
            <a href="{{ route('productos.index', collect(request()->except('categoria_id'))->toArray()) }}"
                class="ms-2 text-danger text-decoration-none fw-bold">&times;</a>
        </span>
        @endif

        <a href="{{ route('productos.index') }}" class="badge bg-danger text-white text-decoration-none ms-2">
            Limpiar todo ✕
        </a>
    </div>
    @endif

    {{-- 🧾 Tabla de productos --}}
    <table class="table table-striped align-middle">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Categoría</th>
                <th>Stock</th>
                <th>Precio Venta (ARS)</th>
                <th>Precio Compra (USD)</th>
                <th>Precio Venta (USD)</th>
                <th>% Ganancia</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($productos as $producto)
            <tr>
                <td>{{ $producto->id }}</td>
                <td>{{ $producto->nombre }}</td>
                <td>{{ $producto->categoria->nombre ?? '-' }}</td>
                <td>{{ $producto->stock }}</td>
                <td>$ {{ number_format($producto->precio_venta_ars, 2, ',', '.') }}</td>
                <td>U$D {{ number_format($producto->precio_compra_usd, 2, ',', '.') }}</td>
                <td>U$D {{ number_format($producto->precio_venta_usd, 2, ',', '.') }}</td>
                <td>{{ number_format($producto->porcentaje_ganancia, 2, ',', '.') }}%</td>
                <td class="text-nowrap">
                    <a href="{{ route('productos.edit', $producto) }}" class="btn btn-sm btn-warning">Editar</a>
                    <form action="{{ route('productos.destroy', $producto) }}" method="POST" style="display:inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar producto?')">
                            Eliminar
                        </button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="9" class="text-center">No hay productos registrados</td>
            </tr>
            @endforelse
        </tbody>
    </table>


    <!-- Modal Filtro Categoría -->
    <div class="modal fade" id="modalFiltroCategoria" tabindex="-1" aria-labelledby="modalFiltroCategoriaLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalFiltroCategoriaLabel">Filtrar por categoría</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body text-center">
                    <form method="GET" action="{{ route('productos.index') }}">
                        {{-- Mantener búsqueda activa si existe --}}
                        @if(request('buscar'))
                        <input type="hidden" name="buscar" value="{{ request('buscar') }}">
                        @endif

                        <select name="categoria_id" class="form-select mb-3 text-center" onchange="this.form.submit()">
                            <option value="">Todas</option>
                            @foreach($categorias as $cat)
                            <option value="{{ $cat->id }}" {{ request('categoria_id') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->nombre }}
                            </option>
                            @endforeach
                        </select>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    @endsection