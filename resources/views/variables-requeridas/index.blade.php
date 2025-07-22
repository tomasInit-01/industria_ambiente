@extends('layouts.app')


@section('content')
<div class="container">
    <!-- Encabezado -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 font-weight-bold">Variables Requeridas</h1>
        <a href="{{ route('variables-requeridas.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus mr-1"></i> Crear Nueva
        </a>
    </div>

    <!-- Mensaje de éxito -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Buscador -->
<div class="card shadow-sm mb-4">
    <div class="card-body">
        <form action="{{ route('variables-requeridas.index') }}" method="GET" class="row g-3 align-items-end">
            <div class="col-md-7 col-sm-12">
                <label for="search" class="form-label visually-hidden">Buscar</label>
                <input type="text" name="search" id="search" class="form-control" 
                       placeholder="Buscar por descripción o nombre..." 
                       value="{{ request('search') }}">
            </div>
            <div class="col-md-3 col-sm-12">
                <label for="obligatorio" class="form-label visually-hidden">Obligatorio</label>
                <select name="obligatorio" id="obligatorio" class="form-select">
                    <option value="">Todos</option>
                    <option value="1" {{ request('obligatorio') == '1' ? 'selected' : '' }}>Obligatorios</option>
                    <option value="0" {{ request('obligatorio') == '0' ? 'selected' : '' }}>Opcionales</option>
                </select>
            </div>
            <div class="col-md-2 col-sm-12">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-search mr-1"></i> Buscar
                </button>
            </div>
        </form>
    </div>
</div>

    <!-- Acordeón de variables -->
    <div class="accordion" id="variablesAccordion">
        @foreach($groupedVariables as $cotioDescripcion => $variables)
        <div class="card shadow-sm mb-3">
            <div class="card-header bg-light" id="heading{{ $loop->index }}">
                <h2 class="mb-0">
                    <div class="d-flex justify-content-between align-items-center w-100">
                        <button class="btn btn-link text-left text-dark text-decoration-none p-0 border-0 bg-transparent" 
                                type="button" 
                                data-bs-toggle="collapse" 
                                data-bs-target="#collapse{{ $loop->index }}" 
                                aria-expanded="{{ $loop->first ? 'true' : 'false' }}" 
                                aria-controls="collapse{{ $loop->index }}">
                            <span>{{ $cotioDescripcion }}</span>
                            <span class="badge bg-primary rounded-pill ms-2">{{ count($variables) }} variables</span>
                        </button>
                        <div class="d-flex gap-2">
                            <a href="{{ route('variables-requeridas.edit-group', urlencode($cotioDescripcion)) }}" 
                               class="btn btn-sm btn-outline-primary"
                               title="Editar grupo">
                                <i class="fas fa-edit"></i> Editar grupo
                            </a>
                        </div>
                    </div>
                </h2>
            </div>

            <div id="collapse{{ $loop->index }}" 
                 class="collapse {{ $loop->first ? 'show' : '' }}" 
                 aria-labelledby="heading{{ $loop->index }}" 
                 data-bs-parent="#variablesAccordion">
                <div class="card-body p-0">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th scope="col" class="col-1">ID</th>
                                <th scope="col" class="col-5">Nombre</th>
                                <th scope="col" class="col-2">Obligatorio</th>
                                <th scope="col" class="col-2">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($variables as $variable)
                            <tr>
                                <td>{{ $variable->id }}</td>
                                <td>{{ $variable->nombre }}</td>
                                <td>
                                    <span class="badge {{ $variable->obligatorio ? 'bg-success' : 'bg-warning' }} text-white">
                                        {{ $variable->obligatorio ? 'Sí' : 'No' }}
                                    </span>
                                </td>
                                <td class="col-2">
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('variables-requeridas.edit', $variable->id) }}" 
                                           class="btn btn-sm btn-outline-warning" 
                                           title="Editar" 
                                           aria-label="Editar variable {{ $variable->nombre }}">
                                            <x-heroicon-o-pencil style="width: 16px; height: 16px;"/>
                                        </a>
                                        <button type="button" 
                                                class="btn btn-sm btn-outline-danger" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#deleteModal{{ $variable->id }}"
                                                title="Eliminar"
                                                aria-label="Eliminar variable {{ $variable->nombre }}">
                                            <x-heroicon-o-trash style="width: 16px; height: 16px;"/>
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <!-- Modal de confirmación de eliminación -->
                            <div class="modal fade" id="deleteModal{{ $variable->id }}" tabindex="-1" aria-labelledby="deleteModalLabel{{ $variable->id }}" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="deleteModalLabel{{ $variable->id }}">Confirmar eliminación</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            ¿Estás seguro de que deseas eliminar la variable "{{ $variable->nombre }}"?
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                            <form action="{{ route('variables-requeridas.destroy', $variable->id) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger">Eliminar</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Paginación -->
    {{-- @if($groupedVariables->hasPages())
        <div class="d-flex justify-content-center mt-4">
            {{ $groupedVariables->appends(request()->query())->links('pagination::bootstrap-5') }}
        </div>
    @endif --}}
</div>

<style>
    .accordion .card-header .btn-link {
        text-decoration: none;
        color: #212529;
        font-weight: 500;
    }
    .accordion .card-header .btn-link:hover {
        color: #0d6efd;
    }
    .badge {
        font-size: 0.9rem;
        padding: 0.5em 1em;
    }
    .table th, .table td {
        vertical-align: middle;
    }
    .table-hover tbody tr:hover {
        background-color: rgba(0, 123, 255, 0.05);
    }
    @media (max-width: 576px) {
        .btn-sm {
            font-size: 0.8rem;
            padding: 0.25rem 0.5rem;
        }
        .badge {
            font-size: 0.75rem;
        }
    }
</style>

<script>
    // Asegurar que los modales funcionen correctamente
    document.addEventListener('DOMContentLoaded', function () {
        var modals = document.querySelectorAll('.modal');
        modals.forEach(function(modal) {
            modal.addEventListener('shown.bs.modal', function () {
                modal.querySelector('.btn-close').focus();
            });
        });
    });
</script>


<script>
    document.addEventListener('DOMContentLoaded', function () {
        const searchForm = document.getElementById('searchForm');
        const searchInput = document.getElementById('search');
        const obligatorioSelect = document.getElementById('obligatorio');
        const accordion = document.getElementById('variablesAccordion');
        const loadingSpinner = document.getElementById('loadingSpinner');
        const noResults = document.getElementById('noResults');
    
        function performSearch() {
            const search = searchInput.value.trim();
            const obligatorio = obligatorioSelect.value;
    
            // Mostrar spinner
            loadingSpinner.classList.remove('d-none');
            noResults.classList.add('d-none');
    
            // Realizar búsqueda AJAX
            fetch('{{ route('variables-requeridas.index') }}?' + new URLSearchParams({
                search: search,
                obligatorio: obligatorio,
                ajax: 1 // Indicador para el backend
            }))
                .then(response => response.json())
                .then(data => {
                    // Ocultar spinner
                    loadingSpinner.classList.add('d-none');
    
                    // Actualizar acordeón
                    accordion.innerHTML = data.html;
    
                    // Mostrar mensaje si no hay resultados
                    if (data.groupedVariables.length === 0) {
                        noResults.classList.remove('d-none');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    loadingSpinner.classList.add('d-none');
                    noResults.classList.remove('d-none');
                    noResults.textContent = 'Ocurrió un error al realizar la búsqueda.';
                });
        }
    
        // Escuchar cambios en el formulario
        searchForm.addEventListener('submit', function (e) {
            e.preventDefault();
            if (searchInput.value.length >= 3 || obligatorioSelect.value !== '') {
                performSearch();
            }
        });
    
        // Búsqueda en tiempo real (opcional, descomentar si se desea)
        /*
        searchInput.addEventListener('input', function () {
            if (this.value.length >= 3 || this.value === '') {
                performSearch();
            }
        });
        obligatorioSelect.addEventListener('change', performSearch);
        */
    });
    </script>


@endsection