@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="m-0">Categorías</h1>
        <div class="d-flex gap-2">
            <a href="{{ route('categorias.create') }}" class="btn btn-primary">
                + Nueva categoría
            </a>
            <a href="{{ route('productos.index') }}" class="btn btn-secondary">
                Regresar
            </a>
        </div>
    </div>

    @if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-bordered table-hover">
        <thead class="table-light">
            <tr>
                <th>Nombre</th>
                <th>Descripción</th>
                <th width="180">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($categorias as $categoria)
            <tr>
                <td>{{ $categoria->nombre }}</td>
                <td>{{ $categoria->descripcion ?? '-' }}</td>
                <td>
                    <a href="{{ route('categorias.edit', $categoria) }}" class="btn btn-warning btn-sm">Editar</a>
                    <form action="{{ route('categorias.destroy', $categoria) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-danger btn-sm" onclick="return confirm('¿Eliminar categoría?')">Eliminar</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

</div>
@endsection