@extends('layouts.app')
<head>
    <title>Cotizaciones</title>
</head>


@section('content')
<div class="container py-3 py-md-4">
    <header class="d-flex flex-md-row justify-content-between align-items-center align-items-md-center">
        <h1 class="mb-md-4">Cotizaciones</h1>

        <div class="d-flex gap-2 align-items-center mb-2">
            <button class="btn btn-sm btn-outline-primary me-2" type="button" data-bs-toggle="collapse" 
                    data-bs-target="#collapseSearch" aria-expanded="false" aria-controls="collapseSearch"
                    id="searchToggleBtn">
                <x-heroicon-o-magnifying-glass style="width: 16px; height: 16px;" class="me-1"/>
                <span class="d-none d-sm-inline">Buscar</span>
            </button>
            
            <a href="{{ route('cotizaciones.index', ['view' => 'lista']) }}" 
               class="btn btn-sm {{ $viewType === 'lista' ? 'btn-primary' : 'btn-outline-secondary' }}">
               <x-heroicon-o-list-bullet style="width: 20px; height: 20px;" />
            </a>
            <a href="{{ route('cotizaciones.index', ['view' => 'calendario']) }}" 
               class="btn btn-sm {{ $viewType === 'calendario' ? 'btn-primary' : 'btn-outline-secondary' }}">
               <x-heroicon-o-calendar-days style="width: 20px; height: 20px;" />
            </a>
            <a href="{{ route('cotizaciones.index', ['view' => 'documento']) }}" 
               class="btn btn-sm {{ $viewType === 'documento' ? 'btn-primary' : 'btn-outline-secondary' }}">
               <x-heroicon-o-document style="width: 20px; height: 20px;" />
            </a>
            
            {{-- <a href="{{ url('/?verTodas=1') }}" class="btn btn-sm btn-primary ms-2">
                Ver todas
            </a> --}}
        </div>
    </header>

    <div class="collapse mb-4" id="collapseSearch">
        <div class="card shadow-sm">
            <div class="card-body">
                <form method="GET" action="{{ route('cotizaciones.index') }}" class="row g-3">
                    <input type="hidden" name="view" value="{{ $viewType }}">
                    
                    <div class="col-md-4">
                        <label for="search" class="form-label">Buscar cotización</label>
                        <input type="text" class="form-control" id="search" name="search" 
                               placeholder="Número, empresa o establecimiento" 
                               value="{{ request('search') }}">
                    </div>
                    
                    <div class="col-md-2">
                        <label for="estado" class="form-label">Estado</label>
                        <select class="form-select" id="estado" name="estado">
                            <option value="">Todos</option>
                            <option value="A" {{ request('estado') == 'A' ? 'selected' : '' }}>Aprobado</option>
                            <option value="E" {{ request('estado') == 'E' ? 'selected' : '' }}>En espera</option>
                            <option value="S" {{ request('estado') == 'S' ? 'selected' : '' }}>Rechazado</option>
                        </select>
                    </div>

                    
                    <div class="col-md-2">
                        <label for="matriz" class="form-label">Matriz</label>
                        <select class="form-select" id="matriz" name="matriz">
                            <option value="">Todas</option>
                            @foreach($matrices as $matriz)
                                <option value="{{ $matriz->matriz_codigo }}" 
                                    {{ request('matriz') == $matriz->matriz_codigo ? 'selected' : '' }}>
                                    {{ $matriz->matriz_descripcion }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="col-md-2">
                        <label for="fecha_inicio" class="form-label">Desde</label>
                        <input type="date" class="form-control" id="fecha_inicio" 
                               name="fecha_inicio" value="{{ request('fecha_inicio') }}">
                    </div>

                    <div class="col-md-2">
                        <label for="fecha_fin" class="form-label">Hasta</label>
                        <input type="date" class="form-control" id="fecha_fin" 
                               name="fecha_fin" value="{{ request('fecha_fin') }}">
                    </div>


                    <div class="col-md-3">
                        <label for="provincia" class="form-label">Provincia</label>
                        <select name="provincia" id="provincia" class="form-select" onchange="this.form.submit()">
                            <option value="">-- Seleccione Provincia --</option>
                            @foreach($provincias as $prov)
                                <option value="{{ $prov->codigo }}" {{ request('provincia') == $prov->codigo ? 'selected' : '' }}>
                                    {{ $prov->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="col-md-3">
                        <label for="localidad" class="form-label">Localidad (Opcional)</label>
                        <select name="localidad" id="localidad" class="form-select" {{ empty(request('provincia')) ? 'disabled' : '' }}>
                            <option value="">-- Seleccione Localidad --</option>
                            @foreach($localidades as $loc)
                                <option value="{{ $loc->codigo }}" {{ request('localidad') == $loc->codigo ? 'selected' : '' }}>
                                    {{ $loc->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    
                    <div class="col-12">
                        <div class="d-flex justify-content-end gap-2">
                            <button type="submit" class="btn btn-primary">
                                <x-heroicon-o-magnifying-glass class="me-1" style="width: 16px; height: 16px;" />
                                Buscar
                            </button>
                            <a href="{{ route('cotizaciones.index', ['view' => $viewType]) }}" class="btn btn-outline-secondary">
                                Limpiar
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    @if(($cotizaciones ?? collect())->isEmpty())
        <div class="alert alert-warning">
            No hay cotizaciones disponibles.
        </div>
    @else


        @switch($viewType)
            @case('lista')
                @include('cotizaciones.partials.lista')
                @break

            @case('calendario')
                @include('cotizaciones.partials.calendario')
                @break

            @case('documento')
                @include('cotizaciones.partials.documento')
                @break
        @endswitch
    @endif
</div>
@endsection



<script>
    document.addEventListener('DOMContentLoaded', function() {
        const viewAsDropdown = document.getElementById('viewAsUserDropdown');
        if (viewAsDropdown) {
            viewAsDropdown.addEventListener('shown.bs.dropdown', function() {
                document.querySelector('[name="user_to_view"]').focus();
            });
        }
        
        document.getElementById('prev-month')?.addEventListener('click', function() {
            navigateMonth(-1);
        });
        
        document.getElementById('next-month')?.addEventListener('click', function() {
            navigateMonth(1);
        });
        
        function navigateMonth(monthsToAdd) {
            const url = new URL(window.location.href);
            const currentMonth = new Date();
            
            if (url.searchParams.has('month')) {
                currentMonth = new Date(url.searchParams.get('month'));
            }
            
            currentMonth.setMonth(currentMonth.getMonth() + monthsToAdd);
            url.searchParams.set('month', currentMonth.toISOString().split('T')[0]);
            window.location.href = url.toString();
        }
    });

</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchCollapse = document.getElementById('collapseSearch');
        const searchToggleBtn = document.getElementById('searchToggleBtn');
        
        const hasFilters = {{ request()->hasAny(['search', 'estado', 'fecha_inicio', 'fecha_fin']) }};
        
        if (hasFilters) {
            new bootstrap.Collapse(searchCollapse, { toggle: true });
            searchToggleBtn.setAttribute('aria-expanded', 'true');
            searchToggleBtn.classList.add('active');
        }
        
        searchCollapse.addEventListener('show.bs.collapse', function() {
            searchToggleBtn.classList.add('active');
        });
        
        searchCollapse.addEventListener('hide.bs.collapse', function() {
            searchToggleBtn.classList.remove('active');
        });
        
        document.querySelectorAll('[data-view-type]').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const viewType = this.getAttribute('data-view-type');
                const url = new URL(this.href);
                
                @if(request()->hasAny(['search', 'estado', 'fecha_inicio', 'fecha_fin']))
                    url.searchParams.set('search', @json(request('search')));
                    url.searchParams.set('estado', @json(request('estado')));
                    url.searchParams.set('fecha_inicio', @json(request('fecha_inicio')));
                    url.searchParams.set('fecha_fin', @json(request('fecha_fin')));
                @endif
                
                window.location.href = url.toString();
            });
        });
    });
</script>

<style>
    #searchToggleBtn.active {
        background-color: var(--bs-primary);
        color: white;
    }
    #searchToggleBtn.active:hover {
        background-color: var(--bs-primary-dark);
    }
    
    @media (max-width: 768px) {
        header {
            flex-direction: column !important;
            align-items: flex-start !important;
        }
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

    .dropdown-menu form {
        min-width: 250px;
    }

    .view-as-alert {
        padding: 0.5rem 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .task-item {
        transition: transform 0.2s;
    }

    .task-item:hover {
        transform: translateX(2px);
    }
</style>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const provinciaSelect = document.getElementById('provincia');
        const localidadSelect = document.getElementById('localidad');
    
        if (provinciaSelect && localidadSelect) {
            provinciaSelect.addEventListener('change', function() {
                const provinciaCodigo = this.value;
                
                if (provinciaCodigo) {
                    // Habilitar el select de localidades
                    localidadSelect.disabled = false;
                    
                    // Limpiar y mostrar mensaje de carga
                    localidadSelect.innerHTML = '<option value="">Cargando localidades...</option>';
                    
                    // Enviar el formulario para recargar la página con la nueva provincia
                    const form = provinciaSelect.closest('form');
                    // Asegurarnos de que no se envíen otros parámetros de búsqueda
                    form.querySelectorAll('input, select').forEach(el => {
                        if (el !== provinciaSelect) {
                            el.disabled = true;
                        }
                    });
                    form.submit();
                } else {
                    // Deshabilitar y limpiar el select de localidades si no hay provincia seleccionada
                    localidadSelect.disabled = true;
                    localidadSelect.innerHTML = '<option value="">-- Seleccione Localidad --</option>';
                    
                    // Enviar el formulario para recargar sin provincia
                    const form = provinciaSelect.closest('form');
                    form.querySelectorAll('input, select').forEach(el => {
                        if (el !== provinciaSelect) {
                            el.disabled = true;
                        }
                    });
                    form.submit();
                }
            });
    
            // Si hay una provincia seleccionada al cargar la página, habilitar localidades
            if (provinciaSelect.value) {
                localidadSelect.disabled = false;
            }
        }
    });
    </script>
@endpush