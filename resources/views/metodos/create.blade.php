@extends('layouts.app')

@section('title', 'Nuevo Método')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">Nuevo Método</h1>
        <a href="{{ route('metodos.index') }}" class="btn btn-outline-secondary">Volver</a>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('metodos.store') }}">
                @csrf
                <div class="mb-3">
                    <label for="metodo_codigo" class="form-label">Código</label>
                    <input type="text" id="metodo_codigo" value="{{ $siguiente }}" class="form-control" readonly>
                </div>

                <div class="mb-3">
                    <label for="metodo_descripcion" class="form-label">Descripción</label>
                    <input type="text" name="metodo_descripcion" id="metodo_descripcion" value="{{ old('metodo_descripcion') }}" class="form-control @error('metodo_descripcion') is-invalid @enderror" required>
                    @error('metodo_descripcion')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Guardar</button>
                    <a href="{{ route('metodos.index') }}" class="btn btn-light">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
    @if ($errors->any())
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
            icon: 'error',
            title: 'Corrige los errores',
            html: `{!! implode('<br>', $errors->all()) !!}`
        });
    });
    </script>
    @endif
</div>
@endsection


