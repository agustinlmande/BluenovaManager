@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Ventas</h1>

    <a href="{{ route('ventas.create') }}" class="btn btn-primary mb-3">Nueva venta</a>

    @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-striped align-middle text-center">
        <thead class="table-dark">
            <tr>
                <th>Fecha</th>
                <th>Vendedor</th>
                <th>Total ARS</th>
                <th>Ganancia (ARS)</th> {{-- ✅ NUEVA COLUMNA --}}
                <th>Monto pagado</th>
                <th>Saldo pendiente</th>
                <th>Estado</th>
                <th>Método pago</th>
                <th>Entrega</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach($ventas as $venta)
            <tr>
                <td>{{ \Carbon\Carbon::parse($venta->fecha)->format('d/m/Y') }}</td>
                <td>{{ $venta->vendedor_nombre ?? ($venta->vendedor->nombre ?? 'Venta propia') }}</td>

                <td>${{ number_format($venta->total_venta_ars, 2, ',', '.') }}</td>

                {{-- ✅ Mostrar ganancia o guión si no hay --}}
                <td>
                    @if($venta->ganancia_ars > 0)
                        <span class="text-success fw-bold">
                            ${{ number_format($venta->ganancia_ars, 2, ',', '.') }}
                        </span>
                    @else
                        <span class="text-muted">—</span>
                    @endif
                </td>

                <td>${{ number_format($venta->monto_pagado, 2, ',', '.') }}</td>
                <td>${{ number_format($venta->saldo_pendiente, 2, ',', '.') }}</td>

                <td>
                    @if($venta->estado_pago === 'pagado')
                        <span class="badge bg-success">Pagado</span>
                    @else
                        <span class="badge bg-warning text-dark">Pendiente</span>
                    @endif
                </td>

                <td>{{ ucfirst($venta->metodo_pago) }}</td>
                <td>{{ $venta->tipo_entrega ?? '-' }}</td>

                <td class="text-nowrap">
                    <a href="{{ route('ventas.show', $venta->id) }}" class="btn btn-sm btn-secondary">
                        Ver / Imprimir
                    </a>
                    <a href="{{ route('ventas.edit', $venta->id) }}" class="btn btn-sm btn-warning">
                        Editar
                    </a>
                    <form action="{{ route('ventas.destroy', $venta->id) }}" method="POST" style="display:inline-block"
                        onsubmit="return confirm('¿Seguro que deseas eliminar esta venta?');">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-sm btn-danger">Eliminar</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
