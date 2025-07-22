@extends('layouts.app')
<head>
    <title>Editar Inventario de Muestreo</title>
</head> 



@section('content')
<div class="container py-4">
    <h1 class="mb-4">Editando: {{ $inventario->equipamiento }}</h1>

    <div class="row">
        <div class="col-md-8">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <div class="card shadow-sm border-0">
                <div class="card-header bg-dark text-white">
                    <strong>Formulario de Edición</strong>
                </div>
                <div class="card-body">
                    <form action="{{ url('/inventarios-muestreo/' . $inventario->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="equipamiento" class="form-label">Equipamiento</label>
                            <input type="text" name="equipamiento" id="equipamiento" class="form-control" value="{{ old('equipamiento', $inventario->equipamiento) }}" required>
                        </div>

                        <div class="mb-3">
                            <label for="marca_modelo" class="form-label">Marca/Modelo</label>
                            <input type="text" name="marca_modelo" id="marca_modelo" class="form-control" value="{{ old('marca_modelo', $inventario->marca_modelo) }}">
                        </div>

                        <div class="mb-3">
                            <label for="n_serie_lote" class="form-label">Número de Serie/Lote</label>
                            <input type="text" name="n_serie_lote" id="n_serie_lote" class="form-control" value="{{ old('n_serie_lote', $inventario->n_serie_lote) }}">
                        </div>

                        <div class="mb-3">
                            <label for="observaciones" class="form-label">Observaciones</label>
                            <textarea name="observaciones" id="observaciones" class="form-control" rows="3">{{ old('observaciones', $inventario->observaciones) }}</textarea>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ url('/inventarios-muestreo') }}" class="btn btn-secondary">← Cancelar</a>
                            <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
