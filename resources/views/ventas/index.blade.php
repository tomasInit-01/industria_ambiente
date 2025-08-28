@extends('layouts.app')

@section('content')

<div class="container">
    <div class="d-flex justify-content-between align-items-center">
        <h1>Ventas</h1>
        <a class="btn btn-primary" href="{{ route('ventas.create') }}">Crear Cotización</a>
    </div>
    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Descripción</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach($cotizaciones as $cotizacion)
                <tr>
                    <td>{{ $cotizacion->coti_num }}</td>
                    <td>{{ $cotizacion->coti_nombre }}</td>
                    <td>{{ $cotizacion->coti_descripcion }}</td>
                    <td>
                        <a href="{{ route('ventas.edit', $cotizacion->coti_num) }}" class="btn btn-primary">Editar</a>
                        <a href="{{ route('ventas.destroy', $cotizacion->coti_num) }}" class="btn btn-danger">Eliminar</a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection