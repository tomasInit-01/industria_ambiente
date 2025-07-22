@extends('layouts.app')
<head>
    <title>Inventario de Laboratorio</title>
</head>

@section('content')
<div class="container py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="fs-4">Inventario de Laboratorio</h1>
        <div class="btn-group">
            <a href="{{ route('inventarios.create') }}" class="btn btn-primary btn-sm d-lg-btn-md">
                <x-heroicon-o-plus class="me-1" style="width: 16px; height: 16px;" />
                Crear Inventario
            </a>
            <button class="btn btn-outline-primary btn-sm d-lg-btn-md" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSearch" 
                aria-expanded="false" aria-controls="collapseSearch" id="searchToggleBtn">
                <x-heroicon-o-magnifying-glass class="me-1" style="width: 16px; height: 16px;" />
                Buscar
            </button>
        </div>

    </div>

    <div class="collapse mb-4" id="collapseSearch">
        <div class="card shadow-sm">
            <div class="card-body">
                <form method="GET" action="{{ route('inventarios.index') }}" class="row g-3">
                    <div class="col-md-4">
                        <label for="search" class="form-label">Buscar equipo</label>
                        <input type="text" class="form-control" id="search" name="search" 
                               placeholder="Equipo, marca, modelo, etc." 
                               value="{{ request('search') }}">
                    </div>
                    
                    <div class="col-md-2">
                        <label for="activo" class="form-label">Estado</label>
                        <select class="form-select" id="activo" name="activo">
                            <option value="">Todos</option>
                            <option value="true" {{ request('activo') == 'true' ? 'selected' : '' }}>Activo</option>
                            <option value="false" {{ request('activo') == 'false' ? 'selected' : '' }}>Inactivo</option>
                        </select>
                    </div>
                    
                    <div class="col-md-3">
                        <label for="n_serie_lote" class="form-label">N° de Serie/Lote</label>
                        <input type="text" class="form-control" id="n_serie_lote" 
                               name="n_serie_lote" value="{{ request('n_serie_lote') }}">
                    </div>
                    
                    <div class="col-12">
                        <div class="d-flex justify-content-end gap-2">
                            <button type="submit" class="btn btn-primary">
                                <x-heroicon-o-magnifying-glass class="me-1" style="width: 16px; height: 16px;" />
                                Buscar
                            </button>
                            <a href="{{ route('inventarios.index') }}" class="btn btn-outline-secondary">
                                Limpiar
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <style>
        #searchToggleBtn.active {
            background-color: var(--bs-primary);
            color: white;
        }
        #searchToggleBtn.active:hover {
            background-color: var(--bs-primary-dark);
        }
        
        @media (max-width: 768px) {
            .btn-group {
                margin-top: 0.5rem;
                width: 100%;
            }
            .btn-group .btn {
                flex: 1;
            }
            #searchToggleBtn {
                margin-right: 0 !important;
                width: 100%;
            }
            .card-body .row {
                gap: 12px 0;
            }
            .card-body .col-md-6,
            .card-body .col-md-3 {
                width: 100%;
                flex: 0 0 100%;
                max-width: 100%;
            }
        }

        #searchToggleBtn .heroicon {
            transition: transform 0.3s ease;
        }
        
        #searchToggleBtn[aria-expanded="true"] .heroicon {
            transform: rotate(90deg);
        }
        
        @media (max-width: 768px) {
            .d-flex.justify-content-between {
                flex-direction: column;
                align-items: flex-start !important;
            }
            
            #searchToggleBtn {
                margin-top: 1rem;
                width: 100%;
            }
        }

    </style>

    
    @if($inventarios->isEmpty())
        <div class="alert alert-warning">
            No hay Inventarios disponibles.
        </div>
    @else

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <!-- Vista en pantallas grandes (tables-like) -->
    <div class="d-none d-lg-block">
        <table class="table table-bordered table-hover">
            <thead class="table-dark">
                <tr>
                    <th>Equipamiento</th>
                    <th>Marca/Modelo</th>
                    <th>Número de Serie/Lote</th>
                    <th>Activo</th>
                    <th>Fecha de Calibración</th>
                    {{-- <th>Estado</th> --}}
                    <th>Observaciones</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($inventarios as $inventario)
                    <tr>    
                        <td>{{ $inventario->equipamiento }}</td>
                        <td>{{ $inventario->marca_modelo }}</td>
                        <td>{{ $inventario->n_serie_lote }}</td>
                        <td class="<?php echo $inventario->activo === true ? 'bg-success text-white' : 'bg-danger text-white'; ?>">{{ $inventario->activo === true ? 'Activo' : 'Inactivo' }}</td>
                        {{-- <td class="<?php echo $inventario->estado === 'libre' ? 'bg-success' : 'bg-danger'; ?>">{{ $inventario->estado === 'libre' ? 'Libre' : 'Ocupado' }}</td> --}}
                        <td>{{ $inventario->fecha_calibracion }}</td>
                        <td>{{ $inventario->observaciones ?? 'N/A' }}</td>
                        <td class="d-flex align-items-start gap-2">
                            <a class="btn btn-sm btn-primary d-flex align-items-center justify-content-center" 
                               href="{{ url('/inventarios/' . $inventario->id . '/edit') }}" 
                               title="Editar inventario"
                               aria-label="Editar inventario">
                                <x-heroicon-o-pencil style="width: 16px; height: 16px;" />
                            </a>
                            <form action="{{ url('/inventarios/' . $inventario->id) }}" 
                                  method="POST" 
                                  class="d-inline delete-form">
                                @csrf
                                @method('DELETE')
                                <button type="submit" 
                                        class="btn btn-sm btn-danger d-flex align-items-center justify-content-center" 
                                        title="Eliminar inventario"
                                        aria-label="Eliminar inventario">
                                    <x-heroicon-o-trash style="width: 16px; height: 16px;" />
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        {{ $inventarios->links() }}
    </div>

    <!-- Vista en pantallas pequeñas (cards) -->
    <div class="d-block d-lg-none">
        <div class="row">
            @foreach($inventarios as $inventario)
                <div class="col-12 mb-4">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title">{{ $inventario->equipamiento }}</h5>
                            <p><strong>Marca/Modelo:</strong> {{ $inventario->marca_modelo }}</p>
                            <p><strong>Activo:</strong> {{ $inventario->activo ? 'Activo' : 'Inactivo' }}</p>
                            {{-- <p><strong>Estado:</strong> {{ $inventario->estado ? 'Libre' : 'Ocupado' }}</p> --}}
                            <p><strong>Fecha de Calibración:</strong> {{ $inventario->fecha_calibracion }}</p>
                            <a class="btn btn-primary" href="{{ url('/inventarios/' . $inventario->id) }}">
                                <x-heroicon-o-pencil class="me-1" style="width: 16px; height: 16px;" />
                            </a>
                            <form action="{{ url('/inventarios/' . $inventario->id) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger">
                                    <x-heroicon-o-trash class="me-1" style="width: 16px; height: 16px;" />
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    {{ $inventarios->links() }}

    @endif

</div>



<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchToggleBtn = document.getElementById('searchToggleBtn');
        const collapseSearch = document.getElementById('collapseSearch');
        
        collapseSearch.addEventListener('show.bs.collapse', function() {
            searchToggleBtn.classList.remove('btn-outline-primary');
            searchToggleBtn.classList.add('btn-primary');
        });
        
        collapseSearch.addEventListener('hide.bs.collapse', function() {
            searchToggleBtn.classList.remove('btn-primary');
            searchToggleBtn.classList.add('btn-outline-primary');
        });
    });


    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.delete-form').forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                Swal.fire({
                    title: '¿Estás seguro?',
                    text: 'No podrás revertir esta acción.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: 'Eliminando...',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });
                        form.submit();
                    }
                });
            });
        });
    });
</script>

@endsection
