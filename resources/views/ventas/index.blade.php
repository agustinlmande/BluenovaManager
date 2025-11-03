@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Ventas</h1>

    <a href="{{ route('ventas.create') }}" class="btn btn-primary mb-3">Nueva venta</a>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-striped">
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Vendedor</th>
                <th>Total ARS</th>
                <th>Método pago</th>
                <th>Entrega</th>
            </tr>
        </thead>
        <tbody>
            @foreach($ventas as $venta)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($venta->fecha)->format('d/m/Y') }}</td>
                    <td>{{ $venta->vendedor->nombre ?? 'Venta propia' }}</td>
                    <td>${{ number_format($venta->total_venta_ars, 2, ',', '.') }}</td>
                    <td>{{ ucfirst($venta->metodo_pago) }}</td>
                    <td>{{ $venta->tipo_entrega ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
