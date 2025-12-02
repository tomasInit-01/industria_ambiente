@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Eliminar Método de Muestreo</h2>
        <a href="{{ route('metodos-muestreo.index') }}" class="btn btn-outline-secondary">
            <x-heroicon-o-arrow-left style="width: 16px; height: 16px;" class="me-1" /> Volver
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card border-danger">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0">
                        <x-heroicon-o-exclamation-triangle style="width: 20px; height: 20px;" class="me-1" />
                        Confirmar Eliminación
                    </h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning">
                        <strong>¡Atención!</strong> Esta acción no se puede deshacer.
                    </div>

                    <div class="mb-4">
                        <h6>Está a punto de eliminar el siguiente método de muestreo:</h6>
                        
                        <div class="card mt-3">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <strong>Código:</strong> <code>{{ $metodoMuestreo->codigo }}</code>
                                    </div>
                                    <div class="col-md-6">
                                        <strong>Estado:</strong> 
                                        @if($metodoMuestreo->activo)
                                            <span class="badge bg-success">Activo</span>
                                        @else
                                            <span class="badge bg-secondary">Inactivo</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="mt-2">
                                    <strong>Nombre:</strong> {{ $metodoMuestreo->nombre }}
                                </div>
                                @if($metodoMuestreo->descripcion)
                                    <div class="mt-2">
                                        <strong>Descripción:</strong> {{ Str::limit($metodoMuestreo->descripcion, 100) }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    @if($metodoMuestreo->cotios->count() > 0)
                        <div class="alert alert-danger">
                            <h6><x-heroicon-o-no-symbol style="width: 16px; height: 16px;" class="me-1" /> No se puede eliminar</h6>
                            <p class="mb-0">
                                Este método está siendo usado en <strong>{{ $metodoMuestreo->cotios->count() }}</strong> cotización(es). 
                                Para eliminarlo, primero debe remover todas las referencias a este método.
                            </p>
                        </div>
                        
                        <div class="d-flex justify-content-end">
                            <a href="{{ route('metodos-muestreo.index') }}" class="btn btn-secondary">
                                <x-heroicon-o-arrow-left style="width: 16px; height: 16px;" class="me-1" /> Volver al Listado
                            </a>
                        </div>
                    @else
                        <div class="alert alert-info">
                            <p class="mb-0">
                                <x-heroicon-o-information-circle style="width: 16px; height: 16px;" class="me-1" />
                                Este método no está siendo usado actualmente y puede ser eliminado de forma segura.
                            </p>
                        </div>

                        <form method="POST" action="{{ route('metodos-muestreo.destroy', $metodoMuestreo) }}">
                            @csrf
                            @method('DELETE')
                            
                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('metodos-muestreo.index') }}" class="btn btn-secondary">
                                    <x-heroicon-o-x-mark style="width: 16px; height: 16px;" class="me-1" /> Cancelar
                                </a>
                                <button type="button" class="btn btn-danger" onclick="confirmDelete()">
                                    <x-heroicon-o-trash style="width: 16px; height: 16px;" class="me-1" /> Sí, Eliminar Método
                                </button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function confirmDelete() {
    Swal.fire({
        title: '¿Estás seguro?',
        text: "Esta acción no se puede deshacer",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            // Crear y enviar formulario
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("metodos-muestreo.destroy", $metodoMuestreo) }}';
            
            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = '{{ csrf_token() }}';
            
            const methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            methodInput.value = 'DELETE';
            
            form.appendChild(csrfToken);
            form.appendChild(methodInput);
            document.body.appendChild(form);
            form.submit();
        }
    });
}
</script>
@endsection
