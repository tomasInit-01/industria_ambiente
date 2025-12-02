@extends('layouts.app')

@section('title', 'Editar Método')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">Editar Método</h1>
        <a href="{{ route('metodos.index') }}" class="btn btn-outline-secondary">Volver</a>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('metodos.update', trim($metodo->metodo_codigo)) }}">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label for="metodo_codigo" class="form-label">Código</label>
                    <input type="text" id="metodo_codigo" value="{{ trim($metodo->metodo_codigo) }}" class="form-control" readonly>
                </div>

                <div class="mb-3">
                    <label for="metodo_descripcion" class="form-label">Descripción</label>
                    <input type="text" name="metodo_descripcion" id="metodo_descripcion" value="{{ old('metodo_descripcion', $metodo->metodo_descripcion) }}" class="form-control @error('metodo_descripcion') is-invalid @enderror" required>
                    @error('metodo_descripcion')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex gap-2 align-items-center">
                    <button type="submit" class="btn btn-primary">Actualizar</button>
                    <a href="{{ route('metodos.index') }}" class="btn btn-light">Cancelar</a>
                </div>
            </form>

            <div class="mt-3 d-flex">
                <form action="{{ route('metodos.delete', trim($metodo->metodo_codigo)) }}" method="POST" class="js-delete-form">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger">Eliminar</button>
                </form>
            </div>
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

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.querySelector('.js-delete-form');
        if (form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                Swal.fire({
                    title: '¿Eliminar este método?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: '#d33'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        }
    });
    </script>
</div>
@endsection


