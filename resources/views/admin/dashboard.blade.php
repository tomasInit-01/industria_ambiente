@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Panel de Administración</h2>
        <div class="text-muted">
            <x-heroicon-o-calendar style="width: 16px; height: 16px;" class="me-1" />
            {{ now()->format('d/m/Y H:i') }}
        </div>
    </div>

    <!-- Estadísticas Generales -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h3 class="mb-0">{{ $estadisticas['metodos_muestreo'] }}</h3>
                            <p class="mb-0">Métodos de Muestreo</p>
                            <small class="opacity-75">{{ $estadisticas['metodos_muestreo_activos'] }} activos</small>
                        </div>
                        <div class="align-self-center">
                            <x-heroicon-o-beaker style="width: 48px; height: 48px;" class="opacity-75" />
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-primary bg-opacity-75">
                    <a href="{{ route('admin.metodos-muestreo.index') }}" class="text-white text-decoration-none">
                        Ver todos <x-heroicon-o-arrow-right style="width: 16px; height: 16px;" class="ms-1" />
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h3 class="mb-0">{{ $estadisticas['metodos_analisis'] }}</h3>
                            <p class="mb-0">Métodos de Análisis</p>
                            <small class="opacity-75">{{ $estadisticas['metodos_analisis_activos'] }} activos</small>
                        </div>
                        <div class="align-self-center">
                            <x-heroicon-o-magnifying-glass style="width: 48px; height: 48px;" class="opacity-75" />
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-success bg-opacity-75">
                    <a href="{{ route('admin.metodos-analisis.index') }}" class="text-white text-decoration-none">
                        Ver todos <x-heroicon-o-arrow-right style="width: 16px; height: 16px;" class="ms-1" />
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h3 class="mb-0">{{ $estadisticas['leyes_normativas'] }}</h3>
                            <p class="mb-0">Leyes y Normativas</p>
                            <small class="opacity-75">{{ $estadisticas['leyes_normativas_activas'] }} activas</small>
                        </div>
                        <div class="align-self-center">
                            <x-heroicon-o-scale style="width: 48px; height: 48px;" class="opacity-75" />
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-info bg-opacity-75">
                    <a href="{{ route('admin.leyes-normativas.index') }}" class="text-white text-decoration-none">
                        Ver todas <x-heroicon-o-arrow-right style="width: 16px; height: 16px;" class="ms-1" />
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Acciones Rápidas -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Acciones Rápidas</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="d-grid">
                                <a href="{{ route('admin.metodos-muestreo.create') }}" class="btn btn-outline-primary">
                                    <x-heroicon-o-plus style="width: 16px; height: 16px;" class="me-1" /> Nuevo Método de Muestreo
                                </a>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-grid">
                                <a href="{{ route('admin.metodos-analisis.create') }}" class="btn btn-outline-success">
                                    <x-heroicon-o-plus style="width: 16px; height: 16px;" class="me-1" /> Nuevo Método de Análisis
                                </a>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-grid">
                                <a href="{{ route('admin.leyes-normativas.create') }}" class="btn btn-outline-info">
                                    <x-heroicon-o-plus style="width: 16px; height: 16px;" class="me-1" /> Nueva Ley/Normativa
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Elementos Recientes -->
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Métodos Agregados Recientemente</h5>
                </div>
                <div class="card-body">
                    @if($recientes['metodos']->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($recientes['metodos'] as $metodo)
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1">{{ $metodo->nombre }}</h6>
                                        <small class="text-muted">
                                            <code>{{ $metodo->codigo }}</code> • 
                                            {{ $metodo->created_at->diffForHumans() }}
                                        </small>
                                    </div>
                                    <div>
                                        @if($metodo instanceof App\Models\MetodoMuestreo)
                                            <span class="badge bg-primary">Muestreo</span>
                                        @else
                                            <span class="badge bg-success">Análisis</span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted text-center py-3">No hay métodos recientes</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Normativas Agregadas Recientemente</h5>
                </div>
                <div class="card-body">
                    @if($recientes['normativas']->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($recientes['normativas'] as $normativa)
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1">{{ Str::limit($normativa->nombre, 40) }}</h6>
                                        <small class="text-muted">
                                            <code>{{ $normativa->codigo }}</code> • 
                                            {{ $normativa->created_at->diffForHumans() }}
                                        </small>
                                    </div>
                                    <div>
                                        <span class="badge bg-info">{{ $normativa->grupo }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted text-center py-3">No hay normativas recientes</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Elementos Más Usados -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Elementos Más Utilizados en Cotizaciones</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Métodos de Muestreo</h6>
                            @if($masUsados['metodos_muestreo']->count() > 0)
                                <div class="list-group list-group-flush">
                                    @foreach($masUsados['metodos_muestreo'] as $metodo)
                                        <div class="list-group-item d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong>{{ $metodo->nombre }}</strong><br>
                                                <small class="text-muted"><code>{{ $metodo->codigo }}</code></small>
                                            </div>
                                            <span class="badge bg-primary rounded-pill">{{ $metodo->cotios_count }} usos</span>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-muted">No hay datos de uso disponibles</p>
                            @endif
                        </div>

                        <div class="col-md-6">
                            <h6>Métodos de Análisis</h6>
                            @if($masUsados['metodos_analisis']->count() > 0)
                                <div class="list-group list-group-flush">
                                    @foreach($masUsados['metodos_analisis'] as $metodo)
                                        <div class="list-group-item d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong>{{ $metodo->nombre }}</strong><br>
                                                <small class="text-muted"><code>{{ $metodo->codigo }}</code></small>
                                            </div>
                                            <span class="badge bg-success rounded-pill">{{ $metodo->cotios_count }} usos</span>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-muted">No hay datos de uso disponibles</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
