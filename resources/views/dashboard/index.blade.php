@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="text-center mb-4">Resumen rápido</h1>

    <div class="row text-center">
        <div class="col-md-4 mb-3">
            <div class="card shadow-sm p-4 border-success">
                <h5>💰 Total ganado</h5>
                <h2 class="text-success">${{ number_format($totalGanado, 2, ',', '.') }}</h2>
            </div>
        </div>

        <div class="col-md-4 mb-3">
            <div class="card shadow-sm p-4 border-primary">
                <h5>💵 Total en caja</h5>
                <h2 class="text-primary">${{ number_format($totalEnCaja, 2, ',', '.') }}</h2>
            </div>
        </div>

        <div class="col-md-4 mb-3">
            <div class="card shadow-sm p-4 border-danger">
                <h5>🧾 Pendiente de cobro</h5>
                <h2 class="text-danger">${{ number_format($pendienteCobro, 2, ',', '.') }}</h2>
            </div>
        </div>
    </div>
</div>
@endsection