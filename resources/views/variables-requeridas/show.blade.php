@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Detalles de Variable Requerida</h1>
    
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">ID: {{ $variableRequerida->id }}</h5>
            <p class="card-text"><strong>Cotio Descripción:</strong> {{ $variableRequerida->cotio_descripcion }}</p>
            <p class="card-text"><strong>Nombre:</strong> {{ $variableRequerida->nombre }}</p>
            <p class="card-text"><strong>Obligatorio:</strong> {{ $variableRequerida->obligatorio ? 'Sí' : 'No' }}</p>
            <p class="card-text"><strong>Creado:</strong> {{ $variableRequerida->created_at }}</p>
            <p class="card-text"><strong>Actualizado:</strong> {{ $variableRequerida->updated_at }}</p>
            
            <a href="{{ route('variables-requeridas.edit', $variableRequerida->id) }}" class="btn btn-warning">Editar</a>
            <form action="{{ route('variables-requeridas.destroy', $variableRequerida->id) }}" method="POST" style="display: inline-block;">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger" onclick="return confirm('¿Estás seguro?')">Eliminar</button>
            </form>
            <a href="{{ route('variables-requeridas.index') }}" class="btn btn-secondary">Volver</a>
        </div>
    </div>
</div>
@endsection