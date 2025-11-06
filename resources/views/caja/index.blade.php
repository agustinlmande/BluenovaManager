@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h1 class="mb-4">Caja General</h1>

    {{-- Mensajes --}}
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @elseif(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    {{-- Reporte resumen --}}
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card text-bg-light border-success">
                <div class="card-body">
                    <h6 class="text-success">Saldo actual</h6>
                    <h3 class="fw-bold text-success mb-0">${{ number_format($saldo, 2, ',', '.') }}</h3>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card text-bg-light border-primary">
                <div class="card-body">
                    <h6 class="text-primary">Ingresos (manuales + ventas)</h6>
                    <h5 class="fw-bold mb-0">${{ number_format($totalIngresos + $totalVentas, 2, ',', '.') }}</h5>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card text-bg-light border-danger">
                <div class="card-body">
                    <h6 class="text-danger">Egresos (manuales + compras)</h6>
                    <h5 class="fw-bold mb-0">${{ number_format($totalEgresos + $totalCompras, 2, ',', '.') }}</h5>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card text-bg-light border-info">
                <div class="card-body">
                    <h6 class="text-info">Ganancia estimada</h6>
                    <h5 class="fw-bold mb-0 text-info">${{ number_format($gananciaEstimada, 2, ',', '.') }}</h5>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card text-bg-light border-warning">
                <div class="card-body">
                    <h6 class="text-warning">Deuda de clientes (pendiente)</h6>
                    <h5 class="fw-bold mb-0 text-warning">${{ number_format($deudaClientes, 2, ',', '.') }}</h5>
                </div>
            </div>
        </div>
    </div>

    {{-- Botón nuevo movimiento --}}
    <div class="d-flex justify-content-between mb-3">
        <a href="{{ route('caja.create') }}" class="btn btn-primary">+ Nuevo movimiento</a>
    </div>

    {{-- Tabla de movimientos --}}
    <div class="table-responsive">
        <table class="table table-striped align-middle">
            <thead class="table-dark">
                <tr>
                    <th>Fecha</th>
                    <th>Tipo</th>
                    <th>Motivo</th>
                    <th>Monto</th>
                    <th>Saldo</th>
                    <th>Origen</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($todos as $mov)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($mov['fecha'])->format('d/m/Y H:i') }}</td>
                        <td>
                            @if(strtolower($mov['tipo']) === 'ingreso' || strtolower($mov['tipo']) === 'venta')
                                <span class="text-success fw-bold">{{ ucfirst($mov['tipo']) }}</span>
                            @else
                                <span class="text-danger fw-bold">{{ ucfirst($mov['tipo']) }}</span>
                            @endif
                        </td>
                        <td>{{ $mov['motivo'] }}</td>
                        <td>${{ number_format($mov['monto'], 2, ',', '.') }}</td>
                        <td>${{ number_format($mov['saldo'], 2, ',', '.') }}</td>
                        <td>{{ $mov['origen'] }}</td>
                        <td>
                            @if($mov['editable'])
                                <a href="{{ route('caja.edit', $mov['id']) }}" class="btn btn-sm btn-warning">Editar</a>
                                <form action="{{ route('caja.destroy', $mov['id']) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger"
                                            onclick="return confirm('¿Eliminar este movimiento?')">
                                        Eliminar
                                    </button>
                                </form>
                            @else
                                <small class="text-muted">Automático</small>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center">No hay movimientos registrados.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
