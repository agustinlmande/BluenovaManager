@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Vendedores</h1>

    <a href="{{ route('vendedores.create') }}" class="btn btn-primary mb-3"> Nuevo vendedor</a>

    @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Contacto</th>
                <th>Comisión (%)</th>
                <th>Observaciones</th>
                <th>Alta</th>
                <th>Última edición</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach($vendedores as $v)
            <tr>
                <td>{{ $v->nombre }}</td>
                <td>{{ $v->contacto ?? '-' }}</td>
                <td>{{ $v->comision_por_defecto }}%</td>
                <td>{{ $v->observaciones ?? '-' }}</td>
                <td>{{ $v->created_at ? $v->created_at->format('d/m/Y H:i') : '-' }}</td>
                <td>{{ $v->updated_at ? $v->updated_at->format('d/m/Y H:i') : '-' }}</td>
                <td>
                    <a href="{{ route('vendedores.edit', $v) }}" class="btn btn-primary btn-sm">Editar</a>
                    <form action="{{ route('vendedores.destroy', $v) }}" method="POST" style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-danger btn-sm" onclick="return confirm('¿Eliminar vendedor?')">Eliminar</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection