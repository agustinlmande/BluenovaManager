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
    <table class="table table-striped mt-3">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th class="text-center align-middle">
                    <div class="d-flex align-items-center gap-2 ">
                        <span class="fw-semibold mb-0">Categoría</span>
                        <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2"
                            data-bs-toggle="modal" data-bs-target="#modalFiltroCategoria">
                            <i class="bi bi-funnel">Filtrar</i>
                        </button>
                    </div>
                </th>
                <th>Stock</th>
                <th>Precio Venta (ARS)</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach($productos as $producto)
            <tr>
                <td>{{ $producto->id }}</td>
                <td>{{ $producto->nombre }}</td>
                <td>{{ $producto->categoria->nombre ?? '-' }}</td>
                <td>{{ $producto->stock }}</td>
                <td>${{ number_format($producto->precio_venta_ars, 2, ',', '.') }}</td>
                <td>
                    <a href="{{ route('productos.edit', $producto) }}" class="btn btn-warning btn-sm">Editar</a>
                    <form action="{{ route('productos.destroy', $producto) }}" method="POST" style="display:inline">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-danger btn-sm" onclick="return confirm('¿Eliminar producto?')">
                            Eliminar
                        </button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

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