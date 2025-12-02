@extends('layouts.app')

@section('title', 'Métodos')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">Métodos</h1>
        <a href="{{ route('metodos.create') }}" class="btn btn-primary">Nuevo método</a>
    </div>

    @if(session('success'))
        <div id="flash-success" data-message="{{ session('success') }}" style="display:none"></div>
    @endif

    <form method="GET" action="{{ route('metodos.index') }}" class="row g-2 mb-3">
        <div class="col-auto">
            <input type="text" name="q" value="{{ $search }}" class="form-control" placeholder="Buscar código o descripción">
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-outline-secondary">Buscar</button>
        </div>
    </form>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped mb-0">
                    <thead>
                        <tr>
                            <th style="width: 160px;">Código</th>
                            <th>Descripción</th>
                            <th style="width: 160px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($metodos as $metodo)
                            <tr>
                                <td class="align-middle">{{ $metodo->metodo_codigo }}</td>
                                <td class="align-middle">{{ $metodo->metodo_descripcion }}</td>
                                <td class="text-end">
                                    <a href="{{ route('metodos.edit', trim($metodo->metodo_codigo)) }}" class="btn btn-sm btn-outline-primary">Editar</a>
                                    <form action="{{ route('metodos.delete', trim($metodo->metodo_codigo)) }}" method="POST" class="d-inline js-delete-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Eliminar</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center py-4 text-muted">No hay métodos registrados.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($metodos->hasPages())
            <div class="card-footer">{{ $metodos->links() }}</div>
        @endif
    </div>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const flash = document.getElementById('flash-success');
        if (flash && flash.dataset.message) {
            Swal.fire({
                icon: 'success',
                title: 'Éxito',
                text: flash.dataset.message,
                timer: 2000,
                showConfirmButton: false
            });
        }

        document.querySelectorAll('.js-delete-form').forEach(function(form) {
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
        });
    });
    </script>
</div>
@endsection


