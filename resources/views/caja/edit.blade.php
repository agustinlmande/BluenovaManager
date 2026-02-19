@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h1>Editar Movimiento</h1>

    <form action="{{ route('caja.update', $caja->id) }}" method="POST" class="mt-4">
        @csrf
        @method('PUT')

        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Tipo de movimiento</label>
                    <select name="tipo" class="form-select" required>
                        <option value="ingreso" {{ $caja->tipo == 'ingreso' ? 'selected' : '' }}>Ingreso</option>
                        <option value="egreso" {{ $caja->tipo == 'egreso' ? 'selected' : '' }}>Egreso</option>
                    </select>
                </div>
            </div>

            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">¿A qué cuenta afecta?</label>
                    <select name="cuenta_id" class="form-select" required>
                        @foreach($cuentas as $cuenta)
                        <option value="{{ $cuenta->id }}" {{ $caja->cuenta_id == $cuenta->id ? 'selected' : '' }}>
                            {{ $cuenta->nombre }} (Saldo actual: ${{ number_format($cuenta->saldo, 2, ',', '.') }})
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
                    <input type="number" name="monto" class="form-control" step="0.01" min="0" value="{{ $caja->monto }}" required>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Fecha</label>
                    <input type="datetime-local" name="fecha" class="form-control"
                        value="{{ date('Y-m-d\TH:i', strtotime($caja->fecha)) }}"
                        max="{{ now()->format('Y-m-d\TH:i') }}" required>
                </div>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label">Motivo / Descripción</label>
            <input type="text" name="motivo" class="form-control" value="{{ $caja->motivo }}" required>
        </div>

        <div class="d-flex gap-2 mt-4">
            <button type="submit" class="btn btn-primary btn-lg">Actualizar Movimiento</button>
            <a href="{{ route('caja.index') }}" class="btn btn-secondary btn-lg">Cancelar</a>
        </div>
    </form>
</div>
@endsection