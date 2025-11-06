@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Editar vendedor</h1>

    <form action="{{ route('vendedores.update', $vendedor->id) }}" method="POST">
        @csrf
        @method('PUT')

        @include('vendedores.form')

        <button type="submit" class="btn btn-primary">Actualizar</button>
        <a href="{{ route('vendedores.index') }}" class="btn btn-secondary">Cancelar</a>
    </form>
</div>
@endsection