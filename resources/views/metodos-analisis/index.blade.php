@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Métodos de Análisis</h2>
        <a href="{{ route('metodos-analisis.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Nuevo Método
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('metodos-analisis.index') }}">
                <div class="row">
                    <div class="col-md-3">
                        <label for="search" class="form-label">Buscar</label>
                        <input type="text" class="form-control" id="search" name="search" 
                               value="{{ request('search') }}" placeholder="Código o nombre...">
                    </div>
                    <div class="col-md-2">
                        <label for="activo" class="form-label">Estado</label>
                        <select class="form-select" id="activo" name="activo">
                            <option value="">Todos</option>
                            <option value="1" {{ request('activo') == '1' ? 'selected' : '' }}>Activos</option>
                            <option value="0" {{ request('activo') == '0' ? 'selected' : '' }}>Inactivos</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="requiere_calibracion" class="form-label">Calibración</label>
                        <select class="form-select" id="requiere_calibracion" name="requiere_calibracion">
                            <option value="">Todas</option>
                            <option value="1" {{ request('requiere_calibracion') == '1' ? 'selected' : '' }}>Requiere</option>
                            <option value="0" {{ request('requiere_calibracion') == '0' ? 'selected' : '' }}>No requiere</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-outline-primary me-2">Filtrar</button>
                        <a href="{{ route('metodos-analisis.index') }}" class="btn btn-outline-secondary">Limpiar</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabla -->
    <div class="card">
        <div class="card-body">
            @if($metodos->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Nombre</th>
                                <th>Unidad</th>
                                <th>Tiempo Est.</th>
                                <th>Calibración</th>
                                <th>Costo Base</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($metodos as $metodo)
                                <tr>
                                    <td><code>{{ $metodo->codigo }}</code></td>
                                    <td>{{ $metodo->nombre }}</td>
                                    <td>{{ $metodo->unidad_medicion ?? '-' }}</td>
                                    <td>{{ $metodo->tiempo_estimado_formateado }}</td>
                                    <td>
                                        @if($metodo->requiere_calibracion)
                                            <span class="badge bg-warning">Sí</span>
                                        @else
                                            <span class="badge bg-secondary">No</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($metodo->costo_base)
                                            ${{ number_format($metodo->costo_base, 2) }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        @if($metodo->activo)
                                            <span class="badge bg-success">Activo</span>
                                        @else
                                            <span class="badge bg-secondary">Inactivo</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('metodos-analisis.show', $metodo) }}" 
                                               class="btn btn-sm btn-outline-info" title="Ver">
                                                <x-heroicon-o-eye style="width: 16px; height: 16px;" />
                                            </a>
                                            <a href="{{ route('metodos-analisis.edit', $metodo) }}" 
                                               class="btn btn-sm btn-outline-primary" title="Editar">
                                                <x-heroicon-o-pencil style="width: 16px; height: 16px;" />
                                            </a>
                                            <a href="{{ route('metodos-analisis.delete', $metodo) }}" 
                                               class="btn btn-sm btn-outline-danger" title="Eliminar">
                                                <x-heroicon-o-trash style="width: 16px; height: 16px;" />
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                <div class="d-flex justify-content-center">
                    {{ $metodos->appends(request()->query())->links() }}
                </div>
            @else
                <div class="text-center py-4">
                    <i class="fas fa-microscope fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No se encontraron métodos de análisis.</p>
                    <a href="{{ route('metodos-analisis.create') }}" class="btn btn-primary">
                        Crear el primer método
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
