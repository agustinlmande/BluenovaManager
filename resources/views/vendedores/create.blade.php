@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Nuevo vendedor</h1>

    <form action="{{ route('vendedores.store') }}" method="POST">
        @csrf

        @include('vendedores.form')

        <button type="submit" class="btn btn-success">Guardar</button>
        <a href="{{ route('vendedores.index') }}" class="btn btn-secondary">Cancelar</a>
    </form>
</div>
@endsection