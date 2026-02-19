@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h1>Nuevo Movimiento</h1>

    <form action="{{ route('caja.store') }}" method="POST" class="mt-4">
        @csrf

        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Tipo de movimiento</label>
                    <select name="tipo" class="form-select" required>
                        <option value="">Seleccionar...</option>
                        <option value="ingreso">Ingreso (Suma a la cuenta)</option>
                        <option value="egreso">Egreso (Resta de la cuenta)</option>
                    </select>
                </div>
            </div>

            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">¿A qué cuenta afecta?</label>
                    <select name="cuenta_id" class="form-select" required>
                        <option value="">Seleccionar cuenta...</option>
                        @foreach($cuentas as $cuenta)
                        <option value="{{ $cuenta->id }}">
                            {{ $cuenta->nombre }} (Saldo: ${{ number_format($cuenta->saldo, 2, ',', '.') }})
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Monto (ARS)</label>
                    <input type="number" name="monto" class="form-control" step="0.01" min="0" required>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Fecha</label>
                    <input type="datetime-local" name="fecha" class="form-control"
                        max="{{ now()->format('Y-m-d\TH:i') }}" required>
                </div>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label">Motivo / Descripción</label>
            <input type="text" name="motivo" class="form-control"
                placeholder="Ej: Pago de flete, compra de artículos de limpieza, etc." required>
        </div>

        <div class="d-flex gap-2 mt-4">
            <button type="submit" class="btn btn-success btn-lg">Guardar Movimiento</button>
            <a href="{{ route('caja.index') }}" class="btn btn-secondary btn-lg">Cancelar</a>
        </div>
    </form>
</div>
@endsection