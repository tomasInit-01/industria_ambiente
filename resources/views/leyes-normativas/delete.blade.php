@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Eliminar Ley/Normativa</h2>
        <a href="{{ route('leyes-normativas.index') }}" class="btn btn-outline-secondary">
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
                        <h6>Está a punto de eliminar la siguiente ley/normativa:</h6>
                        
                        <div class="card mt-3">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <strong>Código:</strong> <code>{{ $leyNormativa->codigo }}</code>
                                    </div>
                                    <div class="col-md-6">
                                        <strong>Estado:</strong> 
                                        @if($leyNormativa->activo)
                                            <span class="badge bg-success">Activa</span>
                                        @else
                                            <span class="badge bg-secondary">Inactiva</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="mt-2">
                                    <strong>Nombre:</strong> {{ $leyNormativa->nombre }}
                                </div>
                                <div class="row mt-2">
                                    <div class="col-md-6">
                                        <strong>Grupo:</strong> <span class="badge bg-info">{{ $leyNormativa->grupo }}</span>
                                    </div>
                                    @if($leyNormativa->articulo)
                                        <div class="col-md-6">
                                            <strong>Artículo:</strong> {{ $leyNormativa->articulo }}
                                        </div>
                                    @endif
                                </div>
                                @if($leyNormativa->descripcion)
                                    <div class="mt-2">
                                        <strong>Descripción:</strong> {{ Str::limit($leyNormativa->descripcion, 100) }}
                                    </div>
                                @endif
                                @if($leyNormativa->organismo_emisor)
                                    <div class="mt-2">
                                        <strong>Organismo Emisor:</strong> {{ $leyNormativa->organismo_emisor }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    @if($leyNormativa->cotios->count() > 0)
                        <div class="alert alert-danger">
                            <h6><i class="fas fa-ban"></i> No se puede eliminar</h6>
                            <p class="mb-0">
                                Esta normativa está siendo usada en <strong>{{ $leyNormativa->cotios->count() }}</strong> cotización(es). 
                                Para eliminarla, primero debe remover todas las referencias a esta normativa.
                            </p>
                        </div>
                        
                        <div class="d-flex justify-content-end">
                            <a href="{{ route('leyes-normativas.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Volver al Listado
                            </a>
                        </div>
                    @else
                        <div class="alert alert-info">
                            <p class="mb-0">
                                <i class="fas fa-info-circle"></i>
                                Esta normativa no está siendo usada actualmente y puede ser eliminada de forma segura.
                            </p>
                        </div>

                        <form method="POST" action="{{ route('leyes-normativas.destroy', $leyNormativa) }}">
                            @csrf
                            @method('DELETE')
                            
                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('leyes-normativas.index') }}" class="btn btn-secondary">
                                    <x-heroicon-o-x-mark style="width: 16px; height: 16px;" class="me-1" /> Cancelar
                                </a>
                                <button type="button" class="btn btn-danger" onclick="confirmDelete()">
                                    <x-heroicon-o-trash style="width: 16px; height: 16px;" class="me-1" /> Sí, Eliminar Normativa
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
            form.action = '{{ route("leyes-normativas.destroy", $leyNormativa) }}';
            
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
