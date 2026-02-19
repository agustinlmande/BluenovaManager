@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Billeteras y Bancos</h1>
        <div>
            <button class="btn btn-outline-primary me-2" data-bs-toggle="modal" data-bs-target="#modalTransferir">🔄 Transferir dinero</button>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalNuevaCuenta">+ Nueva Cuenta</button>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card shadow-sm">
                <div class="card-body">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Nombre de la Cuenta</th>
                                <th class="text-end">Saldo Actual (ARS)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $totalGlobal = 0; @endphp
                            @forelse($cuentas as $cuenta)
                            @php $totalGlobal += $cuenta->saldo; @endphp
                            <tr>
                                <td class="fs-5"><strong>🏦 {{ $cuenta->nombre }}</strong></td>
                                <td class="text-end text-success fs-5 fw-bold">
                                    ${{ number_format($cuenta->saldo, 2, ',', '.') }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="2" class="text-center text-muted">Aún no hay cuentas registradas.</td>
                            </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr class="table-dark">
                                <th class="fs-5">TOTAL DINERO DISPONIBLE:</th>
                                <th class="text-end fs-4 text-warning">${{ number_format($totalGlobal, 2, ',', '.') }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalNuevaCuenta" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('cuentas.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Crear Nueva Cuenta</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Nombre de la cuenta</label>
                        <input type="text" name="nombre" class="form-control" placeholder="Ej: Banco Galicia, MercadoPago Sofi" required>
                    </div>
                    <div class="mb-3">
                        <label>Saldo Inicial (ARS)</label>
                        <input type="number" step="0.01" name="saldo_inicial" class="form-control" value="0">
                        <small class="text-muted">Si esta cuenta ya tiene dinero real, ingresa el monto aquí para sincronizarlo.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">Guardar Cuenta</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalTransferir" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('cuentas.transferir') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">🔄 Transferir Dinero</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Desde la cuenta (Origen)</label>
                        <select name="origen_id" class="form-control" required>
                            <option value="">Seleccionar...</option>
                            @foreach($cuentas as $cuenta)
                            <option value="{{ $cuenta->id }}">{{ $cuenta->nombre }} (Saldo: ${{ number_format($cuenta->saldo, 2, ',', '.') }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Hacia la cuenta (Destino)</label>
                        <select name="destino_id" class="form-control" required>
                            <option value="">Seleccionar...</option>
                            @foreach($cuentas as $cuenta)
                            <option value="{{ $cuenta->id }}">{{ $cuenta->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Monto a transferir (ARS)</label>
                        <input type="number" step="0.01" name="monto" class="form-control" min="0.01" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Confirmar Transferencia</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection