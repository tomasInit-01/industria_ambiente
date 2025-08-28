@extends('layouts.app')

@section('content')

<div class="container">
    <div class="d-flex justify-content-between align-items-center">
        <h1>Clientes</h1>
        <a class="btn btn-primary" href="{{ route('clientes.create') }}">Crear Cliente</a>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>Código</th>
                <th>Nombre</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach($clientes as $cliente)
                <tr>
                    <td>{{ $cliente->cli_codigo }}</td>
                    <td>{{ $cliente->cli_razonsocial }}</td>
                    <td>
                        <a href="{{ route('clientes.edit', $cliente->cli_codigo) }}" class="btn btn-primary">Editar</a>
                        <a href="{{ route('clientes.destroy', $cliente->cli_codigo) }}" class="btn btn-danger">Eliminar</a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection