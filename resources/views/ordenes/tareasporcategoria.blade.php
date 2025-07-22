@extends('layouts.app')
{{-- @dd($instanciaActual) --}}
<head>
    <title>Cotización {{$cotizacion->coti_num}} | {{$categoria->cotio_descripcion}}</title>
</head>

@section('content')
<div class="container py-4">
    <div class="d-flex flex-column gap-2 flex-md-row justify-content-between align-items-center mb-4">
        <a href="{{ url('/tareas/'.$cotizacion->coti_num) }}" class="btn btn-outline-secondary d-flex align-items-center gap-2">
            Volver a la cotización
        </a>
        <div class="d-flex flex-column flex-md-row gap-2">
            <button type="button" class="btn btn-secondary d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#asignarModal" disabled>
                Asignar elementos
            </button>
            {{-- <button type="button" class="btn btn-primary d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#asignarFrecuenciaModal">
                Ajustar Frecuencia
            </button> --}}
        </div>
    </div>

    @include('cotizaciones.info')

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="alert alert-warning mb-3">
                <strong>Módulo de OT - Asignación de analistas</strong>
            </div>
            
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="fw-bold mb-3">{{ $categoria->cotio_descripcion }} ({{ $instanciaActual->instance_number ?? ''}} / {{ $categoria->cotio_cantidad ?? ''}})</h2>
                <div class="d-flex gap-2">
                    <a class="btn btn-outline-primary"
                        href="https://www.google.com/maps/search/?api=1&query={{ $cotizacion->coti_direccioncli }}, {{ $cotizacion->coti_localidad }}, {{ $cotizacion->coti_partido }}">
                        <x-heroicon-o-map class="me-1" style="width: 18px; height: 18px;" />
                        <span class="d-none d-md-inline">Ver en Maps</span>
                    </a>
                    @if($instanciaActual->latitud && $instanciaActual->longitud)
                        <a class="btn btn-outline-primary"
                            href="https://www.google.com/maps/search/?api=1&query={{ $instanciaActual->latitud }}, {{ $instanciaActual->longitud }}">
                            <x-heroicon-o-map-pin class="me-1" style="width: 18px; height: 18px;" />
                            <span class="d-none d-md-inline">Ver Georeferencia</span>
                        </a>
                    @endif
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <h4 class="text-muted mb-5">
                        Cotización: <strong>{{ $cotizacion->coti_num }}</strong>
                    </h4>
                    <p class="text-muted mb-1">
                        Estado: 
                        @php
                        // dd($instanciaActual);
                            $estado = strtolower($instanciaActual->cotio_estado_analisis);
                            $badgeClass = match ($estado) {
                                'coordinado analisis' => 'warning',
                                'en revision analisis' => 'info',
                                'analizado' => 'success',
                                'suspension' => 'danger',
                                default => 'secondary'
                            };
                        @endphp
                        <span class="badge bg-{{ $badgeClass }}">{{ $instanciaActual->cotio_estado_analisis }}</span>
                        <button type="button" class="btn btn-sm btn-link" data-bs-toggle="modal" data-bs-target="#estadoModal" data-tipo="categoria">
                            <x-heroicon-o-pencil style="width: 20px; height: 20px;" />
                        </button>
                    </p>

                    {{-- Mostrar todos los responsables de las tareas --}}
                    @if(isset($todosResponsablesTareas) && $todosResponsablesTareas->count() > 0)
                        <p class="text-muted mb-1">
                            <strong>Asignada a:</strong> 
                            @foreach ($todosResponsablesTareas as $responsable)
                                <span class="badge bg-info d-inline-flex align-items-center me-2 mb-1">
                                    {{ $responsable->usu_descripcion }}
                                    <button type="button" 
                                            class="btn btn-sm btn-link text-danger p-0 ms-1" 
                                            style="font-size: 0.75rem; line-height: 1;"
                                            onclick="eliminarResponsableTodasTareas('{{ $responsable->usu_codigo }}')"
                                            title="Eliminar de todas las tareas">
                                        <x-heroicon-o-x-mark style="width: 12px; height: 12px;" />
                                    </button>
                                </span>
                            @endforeach
                        </p>
                    @endif
                </div>
                <div class="col-md-6">
                    <p class="text-muted mb-1">
                        <strong>Frecuencia:</strong> 
                        @if ($instanciaActual->es_frecuente)
                            Frecuente
                        @else
                            Puntual
                        @endif
                    </p>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-12">
                    <div class="d-flex align-items-center gap-3">
                        <p class="text-muted mb-0 fecha-wrapper" data-fecha-fin="{{ $instanciaActual->fecha_fin_ot ? $instanciaActual->fecha_fin_ot : '' }}">
                            <strong>Inicio:</strong> 
                            <span class="{{ $instanciaActual->fecha_inicio_ot ? 'bg-light text-dark px-2 py-1 rounded' : '' }}">
                                {{ $instanciaActual->fecha_inicio_ot ? $instanciaActual->fecha_inicio_ot : 'Faltante' }}
                            </span>
                            &nbsp;&nbsp;|&nbsp;&nbsp;
                            <strong>Fin:</strong> 
                            <span class="fecha-fin {{ $instanciaActual->fecha_fin_ot ? 'bg-light text-dark px-2 py-1 rounded' : '' }}">
                                {{ $instanciaActual->fecha_fin_ot ? $instanciaActual->fecha_fin_ot : 'Faltante' }}
                            </span>
                        </p>
                    </div>
                </div>
            </div>


            @if($instanciaActual->cotio_identificacion)
                <div class="alert alert-info">
                    <strong>Identificador de muestra:</strong> {{ $instanciaActual->cotio_identificacion }}
                </div>
            @endif

            @if($instanciaActual->image)
                <div class="mt-3">
                    <img src="{{ Storage::url('images/' . $instanciaActual->image) }}" alt="Imagen de la muestra" class="img-fluid w-25 rounded">
                </div>
            @endif


            @if($instanciaActual->cotio_estado_analisis == 'finalizado' || $instanciaActual->cotio_estado_analisis == 'analizado' && $instanciaActual->enable_inform == false)
                <form action="{{ route('ordenes.enable-informe', [
                    'cotio_numcoti' => $instanciaActual->cotio_numcoti,
                    'cotio_item' => $instanciaActual->cotio_item,
                    'cotio_subitem' => $instanciaActual->cotio_subitem,
                    'instance' => $instance
                ]) }}" method="POST">
                    @csrf
                    <input type="hidden" name="cotio_numcoti" value="{{ $instanciaActual->cotio_numcoti }}">
                    <input type="hidden" name="cotio_item" value="{{ $instanciaActual->cotio_item }}">
                    <input type="hidden" name="cotio_subitem" value="{{ $instanciaActual->cotio_subitem }}">
                    <input type="hidden" name="instance" value="{{ $instance }}">
                    <button class="btn btn-success mt-2">Pasar a Informe</button>
                </form>
            @endif

            @if($instanciaActual->enable_inform == true)
                <form action="{{ route('ordenes.disable-informe', [
                    'cotio_numcoti' => $instanciaActual->cotio_numcoti,
                    'cotio_item' => $instanciaActual->cotio_item,
                    'cotio_subitem' => $instanciaActual->cotio_subitem,
                    'instance' => $instance
                ]) }}" method="POST">
                @csrf
                    <input type="hidden" name="cotio_numcoti" value="{{ $instanciaActual->cotio_numcoti }}">
                    <input type="hidden" name="cotio_item" value="{{ $instanciaActual->cotio_item }}">
                    <input type="hidden" name="cotio_subitem" value="{{ $instanciaActual->cotio_subitem }}">
                    <input type="hidden" name="instance" value="{{ $instance }}">
                    <button class="btn btn-danger mt-2">Deshabilitar Informe</button>
                </form>
            @endif

            @if($instanciaActual && $instanciaActual->herramientasLab && $instanciaActual->herramientasLab->count())
                <div class="card shadow-sm border-0 mt-5">
                    <div class="card-header bg-light d-flex align-items-center">
                        <x-heroicon-o-wrench-screwdriver class="me-2" style="width: 1rem; height: 1rem;" />
                        <h6 class="card-title mb-0">Herramientas de Análisis</h6>
                    </div>
                    <div class="card-body p-2">
                        <ul class="list-group list-group-flush">
                            @foreach ($instanciaActual->herramientasLab as $herramienta)
                                <li class="list-group-item d-flex justify-content-between align-items-center py-2 px-3 border-0">
                                    <div class="d-flex align-items-center">
                                        <x-heroicon-o-beaker class="text-muted me-2" style="width: 0.875rem; height: 0.875rem;" />
                                        <span>
                                            {{ $herramienta->equipamiento }}
                                            @if($herramienta->marca_modelo)
                                                <small class="text-muted">({{ $herramienta->marca_modelo }})</small>
                                            @endif
                                        </span>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        @if(isset($herramienta->pivot_observaciones) && $herramienta->pivot_observaciones)
                                            <span class="badge bg-light text-dark me-2" title="{{ $herramienta->pivot_observaciones }}">
                                                <x-heroicon-o-information-circle style="width: 0.875rem; height: 0.875rem;" />
                                            </span>
                                        @endif
                                        @if(isset($herramienta->cantidad) && $herramienta->cantidad > 1)
                                            <span class="badge bg-primary rounded-pill">{{ $herramienta->cantidad }}</span>
                                        @endif
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif


            @if($instanciaActual && $variablesMuestra->isNotEmpty())
            <div class="card shadow-sm my-5">
                <div class="card-header">
                    <h5 style="cursor: pointer; color: black; padding: 10px;" data-bs-toggle="collapse" data-bs-target="#variablesCollapse" aria-expanded="false" aria-controls="variablesCollapse">
                        Variables de Medición y Observaciones
                    </h5>
                </div>
            
                <div id="variablesCollapse" class="collapse show">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Variable</th>
                                        <th>Valor</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($variablesMuestra as $variable)
                                        <tr>
                                            <td>{{ $variable->variable }}</td>
                                            <td>
                                                <input type="text" class="form-control variable-value" 
                                                       value="{{ $variable->valor }}" 
                                                       data-id="{{ $variable->id }}"
                                                       readonly>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="mt-4">
                            <label for="observaciones" class="form-label"><strong>Observaciones del Coordinador:</strong></label>
                            <textarea class="form-control" id="observaciones" rows="3" 
                                      readonly>{{ $instanciaActual->observaciones_medicion_coord_muestreo }}</textarea>
                        </div>

                        <div class="mt-4">
                            <label for="observaciones_muestreador" class="form-label"><strong>Observaciones del Muestreador:</strong></label>
                            <textarea class="form-control" id="observaciones_muestreador" rows="3" readonly
                                      style="background-color: #fff8e1; border-left: 4px solid #ffc107; padding-left: 12px;">
                                {{ $instanciaActual->observaciones_medicion_muestreador }}
                            </textarea>
                        </div>
                        
                        @if($instanciaActual->cotio_estado != 'muestreado')
                            <div class="mt-3 text-center">
                                <button class="btn btn-primary btn-lg save-all-data" 
                                        data-instancia-id="{{ $instanciaActual->id }}">
                                    <i class="fas fa-save"></i> Guardar Variables y Observaciones
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
         @endif


        </div>
    </div>

    @if($tareas->count())
        <div class="card shadow-sm">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Análisis de la muestra</h5>
                    <div class="d-flex gap-2">
                    <form class="p-2 mb-0" action="{{ route('ordenes.finalizar-todas', [
                        'cotio_numcoti' => $categoria->cotio_numcoti,
                        'cotio_item' => $categoria->cotio_item,
                        'cotio_subitem' => $categoria->cotio_subitem,
                        'instance_number' => $instanciaActual->instance_number
                    ]) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-primary">
                        <x-heroicon-o-check style="width: 20px; height: 20px;"/>
                        Finalizar todos
                    </button>
                    </form>
                </div>
            </div>
            <div class="card-body">
                <div class="row row-cols-1 row-cols-md-2 g-3">
                    @foreach ($tareas as $tarea)
                        <div class="col">
                            <div class="card h-100 shadow-sm border-0">
                                <div class="card-body p-0">
                                    <!-- Card Header with Checkbox and Title -->
                                    <div class="d-flex justify-content-between align-items-center p-3" style="background-color: #A6C5E3; border-radius: 0.375rem 0.375rem 0 0;">
                                        <div class="form-check mb-0">
                                            <input class="form-check-input tarea-checkbox" 
                                                    type="checkbox" 
                                                    name="tareas_seleccionadas[]" 
                                                    value="{{ $tarea->cotio_item }}_{{ $tarea->cotio_subitem }}"
                                                    id="tarea_{{ $tarea->cotio_item }}_{{ $tarea->cotio_subitem }}"
                                                    data-fecha-inicio="{{ $tarea->instancia && $tarea->instancia->fecha_inicio_ot ? $tarea->instancia->fecha_inicio_ot->format('Y-m-d\TH:i') : '' }}"
                                                    data-fecha-fin="{{ $tarea->instancia && $tarea->instancia->fecha_fin_ot ? $tarea->instancia->fecha_fin_ot->format('Y-m-d\TH:i') : '' }}">
                                            <label class="form-check-label" for="tarea_{{ $tarea->cotio_item }}_{{ $tarea->cotio_subitem }}">
                                                <h5 class="card-title mb-0 d-flex align-items-center">
                                                    <x-heroicon-o-clipboard-document-list class="me-2" style="width: 1.25rem; height: 1.25rem;" />
                                                    {{ $tarea->cotio_descripcion }}
                                                </h5>
                                            </label>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-outline-dark"
                                            data-bs-toggle="modal"
                                            data-bs-target="#estadoModal"
                                            data-tipo="tarea"
                                            data-item="{{ $tarea->cotio_item }}"
                                            data-subitem="{{ $tarea->cotio_subitem }}">
                                            <x-heroicon-o-pencil-square style="width: 1rem; height: 1rem;" />
                                        </button>
                                    </div>
                        
                                    <!-- Card Content -->
                                    <div class="p-3">
                                        <!-- Observación Section -->
                                        @if($tarea->instancia && $tarea->instancia->observacion_resultado)
                                            <div class="d-flex align-items-start mb-2">
                                                <x-heroicon-o-chat-bubble-bottom-center-text class="text-info me-2 mt-1" style="width: 1rem; height: 1rem;" />
                                                <div>
                                                    <span class="me-2"><strong>Observación:</strong></span>
                                                    <span class="badge bg-info text-dark rounded-pill">{{ $tarea->instancia->observacion_resultado }}</span>
                                                </div>
                                            </div>
                                        @endif
                        
                                        <!-- Estado Section -->
                                        <div class="d-flex flex-row flex-column-sm align-items-center justify-content-between mb-3">
                                            <div>
                                                <x-heroicon-o-flag class="me-2" style="width: 1rem; height: 1rem;" />
                                                <span class="me-2"><strong>Estado:</strong></span>
                                                @php
                                                    $estado = strtolower($tarea->instancia->cotio_estado_analisis);
                                                    $badgeClass = match ($estado) {
                                                        'pendiente' => 'warning',
                                                        'coordinado muestreo' => 'warning',
                                                        'coordinado analisis' => 'warning',
                                                        'en proceso' => 'info',
                                                        'en revision muestreo' => 'info',
                                                        'en revision analisis' => 'info',
                                                        'finalizado' => 'success',
                                                        'muestreado' => 'success',
                                                        'analizado' => 'success',
                                                        'suspension' => 'danger',
                                                        default => 'secondary'
                                                    };
                                                @endphp
                                                <span class="badge bg-{{ $badgeClass }} rounded-pill me-2">{{ $tarea->instancia->cotio_estado_analisis }}</span>
                                            </div>

                                            <div id="fecha_carga_ot_{{ $tarea->instancia->id }}">
                                                <x-heroicon-o-calendar class="me-2" style="width: 1rem; height: 1rem;" />
                                                <span class="me-2"><strong>Fecha de carga:</strong></span>
                                                <span class="badge bg-secondary rounded-pill me-2">{{ $tarea->instancia->fecha_carga_ot ?? 'Faltante' }}</span>
                                            </div>
                                        </div>
                        
                                        <!-- Asignado a Section -->
                                        <div class="d-flex align-items-center flex-wrap mb-3">
                                            <x-heroicon-o-user-circle class="me-2" style="width: 1rem; height: 1rem;" />
                                            <span class="me-2"><strong>Asignada a:</strong></span>
                                            @if ($tarea->instancia->responsablesAnalisis->count() > 0)
                                                @foreach ($tarea->instancia->responsablesAnalisis as $responsable)
                                                    <span class="badge bg-primary rounded-pill d-flex align-items-center me-2 mb-1">
                                                        {{ $responsable->usu_descripcion }}
                                                    </span>
                                                @endforeach
                                            @else
                                                <span class="badge bg-secondary rounded-pill">Sin asignar</span>
                                            @endif
                                        </div>
                        
                                        <!-- Fechas Section -->
                                        <div class="d-flex flex-column flex-md-row justify-content-between mb-3 fecha-wrapper" data-fecha-fin="{{ $tarea->instancia && $tarea->instancia->fecha_fin_ot ? $tarea->instancia->fecha_fin_ot->format('Y-m-d\TH:i') : '' }}">
                                            <div class="d-flex align-items-center mb-2 mb-md-0">
                                                <x-heroicon-o-calendar class="me-2" style="width: 1rem; height: 1rem;" />
                                                <span class="me-2"><strong>Inicio:</strong></span>
                                                <span class="{{ $tarea->instancia && $tarea->instancia->fecha_inicio_ot ? 'bg-light text-dark px-2 py-1 rounded' : 'text-muted' }}">
                                                    {{ $tarea->instancia && $tarea->instancia->fecha_inicio_ot ? $tarea->instancia->fecha_inicio_ot->format('d/m/Y H:i') : 'Faltante' }}
                                                </span>
                                            </div>
                                            <div class="d-flex align-items-center">
                                                <x-heroicon-o-clock class="me-2" style="width: 1rem; height: 1rem;" />
                                                <span class="me-2"><strong>Fin:</strong></span>
                                                <span class="fecha-fin {{ $tarea->instancia && $tarea->instancia->fecha_fin_ot ? 'bg-light text-dark px-2 py-1 rounded' : 'text-muted' }}">
                                                    {{ $tarea->instancia && $tarea->instancia->fecha_fin_ot ? $tarea->instancia->fecha_fin_ot->format('d/m/Y H:i') : 'Faltante' }}
                                                </span>
                                            </div>
                                        </div>
                        
                                        <!-- Resultados Section -->
                                        @php
                                            $accordionId = "resultadosAccordion_{$tarea->cotio_item}_{$tarea->cotio_subitem}";
                                            $headingId = "headingResultados_{$tarea->cotio_item}_{$tarea->cotio_subitem}";
                                            $collapseId = "collapseResultados_{$tarea->cotio_item}_{$tarea->cotio_subitem}";
                                        @endphp
                                        
                                        <div class="accordion mt-4" id="{{ $accordionId }}">
                                            <div class="accordion-item">
                                                <h2 class="accordion-header" id="{{ $headingId }}">
                                                    <button class="accordion-button collapsed"
                                                        type="button"
                                                        data-bs-toggle="collapse"
                                                        data-bs-target="#{{ $collapseId }}"
                                                        aria-expanded="false"
                                                        aria-controls="{{ $collapseId }}">
                                                        <x-heroicon-o-document-chart-bar class="text-primary me-2" style="width: 1.25rem; height: 1.25rem;" />
                                                        <strong>Resultados de Análisis</strong>
                                                    </button>
                                                </h2>
                                                <div id="{{ $collapseId }}"
                                                    class="accordion-collapse collapse"
                                                    aria-labelledby="{{ $headingId }}"
                                                    data-bs-parent="#{{ $accordionId }}">
                                                    <div class="accordion-body">
                                                        <form class="resultados-form" 
                                                            action="{{ route('tareas.updateResultado', ['cotio_numcoti' => $tarea->cotio_numcoti, 'cotio_item' => $tarea->cotio_item, 'cotio_subitem' => $tarea->cotio_subitem, 'instance' => $tarea->instancia->instance_number]) }}" 
                                                            method="POST"
                                                            data-cotio-numcoti="{{ $cotizacion->coti_num }}"
                                                            data-cotio-item="{{ $tarea->cotio_item }}"
                                                            data-cotio-subitem="{{ $tarea->cotio_subitem }}"
                                                            data-instance="{{ $tarea->instancia->instance_number }}">
                                                        @csrf
                                                        @method('PUT')
                                                            
                                                            @php
                                                                $resultados = [
                                                                    ['titulo' => 'Resultado Primario', 'valor' => $tarea->instancia->resultado ?? '', 'obs' => $tarea->instancia->observacion_resultado ?? '', 'badge' => 'primary', 'label' => 'R1', 'field' => 'resultado', 'obs_field' => 'observacion_resultado'],
                                                                    ['titulo' => 'Resultado Secundario', 'valor' => $tarea->instancia->resultado_2 ?? '', 'obs' => $tarea->instancia->observacion_resultado_2 ?? '', 'badge' => 'info', 'label' => 'R2', 'field' => 'resultado_2', 'obs_field' => 'observacion_resultado_2'],
                                                                    ['titulo' => 'Resultado Terciario', 'valor' => $tarea->instancia->resultado_3 ?? '', 'obs' => $tarea->instancia->observacion_resultado_3 ?? '', 'badge' => 'warning', 'label' => 'R3', 'field' => 'resultado_3', 'obs_field' => 'observacion_resultado_3'],
                                                                    ['titulo' => 'Resultado Final', 'valor' => $tarea->instancia->resultado_final ?? '', 'obs' => $tarea->instancia->observacion_resultado_final ?? '', 'badge' => 'dark', 'label' => 'Final', 'field' => 'resultado_final', 'obs_field' => 'observacion_resultado_final']
                                                                ];
                                                            @endphp
                                        
                                                            @foreach ($resultados as $r)
                                                                <div class="mb-4 p-3 border rounded">
                                                                    <div class="d-flex align-items-center mb-2">
                                                                        <span class="badge bg-{{ $r['badge'] }} bg-opacity-10 text-{{ $r['badge'] }} rounded-pill me-2">{{ $r['label'] }}</span>
                                                                        <strong>{{ $r['titulo'] }}</strong>
                                                                    </div>
                                                                    
                                                                    <div class="row">
                                                                        <div class="col-md-12">
                                                                            <label for="{{ $r['field'] }}_{{ $tarea->cotio_item }}_{{ $tarea->cotio_subitem }}" class="form-label">Resultado</label>
                                                                            <input 
                                                                                class="form-control resultado-input" 
                                                                                type="text"
                                                                                id="{{ $r['field'] }}_{{ $tarea->cotio_item }}_{{ $tarea->cotio_subitem }}"
                                                                                name="{{ $r['field'] }}"
                                                                                value="{{ $r['valor'] }}"
                                                                                placeholder="Ingrese el resultado..."
                                                                            >
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            @endforeach

                                                            
                                                                <div class="mt-3 text-end">
                                                                    <button type="submit" 
                                                                            class="btn btn-success guardar-todos-resultados"
                                                                            data-form-id="{{ $accordionId }}">
                                                                        <x-heroicon-o-check-circle style="width: 1rem; height: 1rem;" />
                                                                        Guardar
                                                                    </button>
                                                                </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @else
        <div class="alert alert-info">
            <x-heroicon-o-information-circle style="width: 20px; height: 20px;" /> No hay tareas asignadas a esta muestra.
        </div>
    @endif



<div class="modal fade" id="estadoModal" tabindex="-1" aria-labelledby="estadoModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="estadoModalLabel">Ajustar estado de tarea</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="estadoForm">
                    @csrf
                    <input type="hidden" name="cotio_numcoti" id="modal_cotio_numcoti" value="{{ $cotizacion->coti_num }}">
                    <input type="hidden" name="cotio_item" id="modal_cotio_item" value="{{ $categoria->cotio_item }}">
                    <input type="hidden" name="cotio_subitem" id="modal_cotio_subitem" value="0">
                    <input type="hidden" name="instance_number" id="modal_instance_number" value="{{ $instance }}">
                    
                    <div class="mb-3">
                        <label for="modal_estado" class="form-label">Estado</label>
                        <select class="form-select" id="modal_estado" name="estado" required>
                            <option value="coordinado analisis" {{ ($instanciaActual->cotio_estado_analisis ?? 'coordinado analisis') == 'coordinado analisis' ? 'selected' : '' }}>coordinado analisis</option>
                            <option value="en revision analisis" {{ ($instanciaActual->cotio_estado_analisis ?? 'en revision analisis') == 'en revision analisis' ? 'selected' : '' }}>En revision analisis</option>
                            <option value="analizado" {{ ($instanciaActual->cotio_estado_analisis ?? 'analizado') == 'analizado' ? 'selected' : '' }}>analizado</option>
                            <option value="suspension" {{ ($instanciaActual->cotio_estado_analisis ?? 'suspension') == 'suspension' ? 'selected' : '' }}>Suspension</option>
                        </select>

                    </div>
         
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="confirmarEstado">Ajustar</button>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="fechaModal" tabindex="-1" aria-labelledby="fechaModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="fechaModalLabel">Ajustar fechas</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="fechaForm">

                    <input type="hidden" name="cotio_numcoti" id="cotio_numcoti" value="{{ $categoria->cotio_numcoti }}">
                    <input type="hidden" name="cotio_item" id="cotio_item" value="{{ $categoria->cotio_item }}">
                    <input type="hidden" name="cotio_subitem" id="cotio_subitem" value="{{ $categoria->cotio_subitem }}">

                    <div class="mb-3">
                        <label for="fecha_inicio_gral" class="form-label">Fecha de inicio</label>
                        <input type="datetime-local" class="form-control" id="fecha_inicio_gral" name="fecha_inicio_gral" required>
                    </div>
                    <div class="mb-3">
                        <label for="fecha_fin_gral" class="form-label">Fecha de finalización</label>
                        <input type="datetime-local" class="form-control" id="fecha_fin_gral" name="fecha_fin_gral" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="enviarFechas()">Ajustar</button>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="asignarFrecuenciaModal" tabindex="-1" aria-labelledby="asignarFrecuenciaModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="asignarFrecuenciaModalLabel">Ajustar Frecuencia</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <form id="asignarFrecuenciaForm">
                    <input type="hidden" name="cotio_numcoti" value="{{ $categoria->cotio_numcoti }}">
                    <input type="hidden" name="cotio_item" value="{{ $categoria->cotio_item }}">
                    <input type="hidden" name="cotio_subitem" value="0">
                    

                    <div class="mb-3">
                        <label for="es_frecuente" class="form-label">¿Es frecuente?</label>
                        <select class="form-select" id="es_frecuente" name="es_frecuente">
                            <option value="1" {{ $categoria->es_frecuente ? 'selected' : '' }}>Sí</option>
                            <option value="0" {{ !$categoria->es_frecuente ? 'selected' : '' }}>No</option>
                        </select>
                    </div>


                    <div class="mb-3">
                        <label for="frecuencia_dias" class="form-label">Frecuencia en días</label>
                        <select class="form-select" id="frecuencia_dias" name="frecuencia_dias" required>
                            <option value="">Seleccione una opción</option>
                            <option value="diario" {{ $categoria->frecuencia_dias === 'diario' ? 'selected' : '' }}>Diario</option>
                            <option value="semanal" {{ $categoria->frecuencia_dias === 'semanal' ? 'selected' : '' }}>Semanal</option>
                            <option value="quincenal" {{ $categoria->frecuencia_dias === 'quincenal' ? 'selected' : '' }}>Quincenal</option>
                            <option value="mensual" {{ $categoria->frecuencia_dias === 'mensual' ? 'selected' : '' }}>Mensual</option>
                            <option value="trimestral" {{ $categoria->frecuencia_dias === 'trimestral' ? 'selected' : '' }}>Trimestral</option>
                            <option value="cuatr" {{ $categoria->frecuencia_dias === 'cuatr' ? 'selected' : '' }}>Cuatrimestral</option>
                            <option value="semestral" {{ $categoria->frecuencia_dias === 'semestral' ? 'selected' : '' }}>Semestral</option>
                            <option value="anual" {{ $categoria->frecuencia_dias === 'anual' ? 'selected' : '' }}>Anual</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="enviarFrecuencia()">Ajustar</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="asignarModal" tabindex="-1" aria-labelledby="asignarModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <form id="asignarForm">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="asignarModalLabel">Asignar Responsable</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
          </div>
          <div class="modal-body">


            <div class="mb-3">
                <label for="responsable_codigo" class="form-label">Responsable</label>
                <select class="form-select" id="responsable_codigo" name="responsable_codigo">
                    <option value="">-- Sin cambios --</option>
                    <option value="NULL">-- Quitar responsable --</option>
                    @foreach($usuarios as $usuario)
                        <option value="{{ $usuario->usu_codigo }}">
                            {{ $usuario->usu_descripcion }} ({{ $usuario->usu_codigo }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label for="herramientas" class="form-label">Herramientas/Equipos</label>
                <select class="form-select select2-multiple" id="herramientas" name="herramientas[]" multiple="multiple">
                    @foreach($inventario as $item)
                        <option value="{{ $item->id }}">
                            {{ $item->equipamiento }} ({{ $item->marca_modelo }}) - {{ $item->n_serie_lote }}
                        </option>
                    @endforeach
                </select>
                <small class="text-muted">Seleccione múltiples herramientas con Ctrl+Click</small>
            </div>


            <div class="mb-3">
                <label for="fecha_inicio_ot" class="form-label">Fecha y Hora de Inicio</label>
                <input 
                    type="datetime-local" 
                    class="form-control" 
                    id="fecha_inicio_ot" 
                    name="fecha_inicio_ot"
                    value="{{ $categoria->fecha_inicio_ot ? date('Y-m-d\TH:i', strtotime($categoria->fecha_inicio_ot)) : '' }}"
                >
            </div>
            
            <div class="mb-3">
                <label for="fecha_fin_ot" class="form-label">Fecha y Hora de Fin</label>
                <input 
                    type="datetime-local" 
                    class="form-control" 
                    id="fecha_fin_ot" 
                    name="fecha_fin_ot"
                    value="{{ $categoria->fecha_fin_ot ? date('Y-m-d\TH:i', strtotime($categoria->fecha_fin_ot)) : '' }}"
                >
            </div>

          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-primary">Asignar</button>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>


<style>
    .fecha-verde {
        background-color: #d4edda !important;
    }

    .fecha-roja {
        background-color: #f8d7da !important;
    }

    .btn[disabled] {
        cursor: not-allowed;
        opacity: 0.65;
    }
    
    /* Estilos para campos editables de resultados */
    .resultado-input:focus,
    .observacion-input:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
    }
    
    .resultado-input.border-warning,
    .observacion-input.border-warning {
        border-color: #ffc107 !important;
        box-shadow: 0 0 0 0.2rem rgba(255, 193, 7, 0.25);
    }
    
    .resultado-input.border-success,
    .observacion-input.border-success {
        border-color: #198754 !important;
        box-shadow: 0 0 0 0.2rem rgba(25, 135, 84, 0.25);
    }
    
    .guardar-resultado {
        transition: all 0.3s ease;
    }
    
    .guardar-resultado:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    
    .guardar-resultado:disabled {
        transform: none;
        box-shadow: none;
    }
    
    /* Animación para el ícono de carga */
    .animate-spin {
        animation: spin 1s linear infinite;
    }
    
    @keyframes spin {
        from {
            transform: rotate(0deg);
        }
        to {
            transform: rotate(360deg);
        }
    }
    
    /* Mejoras visuales para el accordion de resultados */
    .accordion-body {
        background-color: #f8f9fa;
    }
    
    .resultados-form .border.rounded {
        background-color: white;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        transition: box-shadow 0.3s ease;
    }
    
    .resultados-form .border.rounded:hover {
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }

    .guardar-todos-resultados {
        transition: all 0.3s ease;
        font-weight: 500;
    }
    
    .guardar-todos-resultados:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 12px rgba(0,0,0,0.15);
    }
    
    .guardar-todos-resultados:disabled {
        transform: none;
        box-shadow: none;
    }
</style>


<script>
document.addEventListener('DOMContentLoaded', function() {
    const estadoModal = document.getElementById('estadoModal');
    const estadoButtons = document.querySelectorAll('[data-bs-target="#estadoModal"]');
    
    estadoModal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        const tipo = button.dataset.tipo;
        const card = button.closest('.card-body');
        
        // Establecer valores comunes
        document.getElementById('modal_cotio_numcoti').value = '{{ $cotizacion->coti_num }}';
        document.getElementById('modal_instance_number').value = '{{ $instance }}';
        
        if (tipo === 'categoria') {
            // Configuración para categoría
            document.getElementById('modal_cotio_item').value = '{{ $categoria->cotio_item }}';
            document.getElementById('modal_cotio_subitem').value = '0';
            
            // Obtener estado actual de la categoría
            const estadoActual = card.querySelector('.badge').textContent.trim().toLowerCase();
            document.getElementById('modal_estado').value = estadoActual;
        } else {
            // Configuración para tarea
            document.getElementById('modal_cotio_item').value = button.dataset.item;
            document.getElementById('modal_cotio_subitem').value = button.dataset.subitem;

            
            // Obtener estado actual de la tarea
            const estadoActual = card.querySelector('.badge').textContent.trim().toLowerCase();
            document.getElementById('modal_estado').value = estadoActual;
        }
    });

    document.getElementById('confirmarEstado').addEventListener('click', async function() {
        const form = document.getElementById('estadoForm');
        const formData = new FormData(form);
        
        try {
            const response = await fetch('{{ route("ordenes.actualizar-estado") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Éxito',
                    text: data.message,
                    confirmButtonColor: '#3085d6',
                }).then(() => {
                    location.reload();
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: data.message || 'Error al actualizar el estado',
                    confirmButtonColor: '#3085d6',
                });
            }
        } catch (error) {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Ocurrió un error al actualizar el estado',
                confirmButtonColor: '#3085d6',
            });
        }
    });
});
</script>



<script>
document.addEventListener('DOMContentLoaded', function() {
    $('#herramientas').select2({
        placeholder: "Seleccione herramientas",
        width: '100%',
        dropdownParent: $('#asignarModal')
    });



document.getElementById('asignarForm').addEventListener('submit', async function (e) {
        e.preventDefault();

        const herramientasSeleccionadas = $('#herramientas').select2('data').map(item => item.id);

        const tareasSeleccionadas = Array.from(document.querySelectorAll('.tarea-checkbox:checked'))
            .map(checkbox => {
                const [item, subitem] = checkbox.value.split('_');
                return { item, subitem };
            });

        const formData = {
            cotio_numcoti: "{{ $categoria->cotio_numcoti }}",
            cotio_item: "{{ $categoria->cotio_item }}",
            instance_number: "{{ $instance }}", // Asegúrate de pasar la instancia actual
            vehiculo_asignado: document.getElementById('vehiculo_asignado')?.value || null,
            responsable_codigo: document.getElementById('responsable_codigo')?.value || null,
            fecha_inicio_ot: document.getElementById('fecha_inicio_ot')?.value || null,
            fecha_fin_ot: document.getElementById('fecha_fin_ot')?.value || null,
            herramientas: herramientasSeleccionadas,
            tareas_seleccionadas: tareasSeleccionadas
        };

        try {
            const response = await fetch("{{ route('asignar.detalles-analisis') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': "{{ csrf_token() }}"
                },
                body: JSON.stringify(formData)
            });

            const data = await response.json();
            if (response.ok) {
                Swal.fire({
                    icon: 'success',
                    title: 'Éxito',
                    text: data.message,
                    confirmButtonColor: '#3085d6',
                }).then(() => {
                    location.reload();
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: "Error: " + (data.message || "Hubo un problema."),
                    confirmButtonColor: '#3085d6',
                });
                console.error(data);
            }
        } catch (err) {
            console.error(err);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: "Error al asignar detalles. Ver consola para más información.",
                confirmButtonColor: '#3085d6',
            });
        }
    });
});


function removerResponsable(event, instanciaId, usuarioCodigo) {
        event.preventDefault();
        
        const url = '/muestras/remover-responsable';
        
        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                instancia_id: instanciaId,
                usuario_codigo: usuarioCodigo
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message);
            }
        })
        .catch(error => {
            console.error('Error al remover responsable:', error);
            alert('Error al remover responsable');
        });
}   



document.addEventListener('DOMContentLoaded', function() {
    const asignarBtn = document.querySelector('.btn[data-bs-target="#asignarModal"]');
    const checkboxes = document.querySelectorAll('.tarea-checkbox');
    const fechaInicioInput = document.getElementById('fecha_inicio_ot');
    const fechaFinInput = document.getElementById('fecha_fin_ot');
    
    function verificarCheckboxes() {
        const checkedBoxes = Array.from(checkboxes).filter(checkbox => checkbox.checked);
        const alMenosUnoMarcado = checkedBoxes.length > 0;
        
        if (alMenosUnoMarcado) {
            asignarBtn.disabled = false;
            asignarBtn.classList.remove('btn-secondary');
            asignarBtn.classList.add('btn-primary');
            
            if (checkedBoxes.length === 1) {
                const tareaSeleccionada = checkedBoxes[0];
                fechaInicioInput.value = tareaSeleccionada.dataset.fechaInicio || '';
                fechaFinInput.value = tareaSeleccionada.dataset.fechaFin || '';
            } else {
                fechaInicioInput.value = "{{ $categoria->fecha_inicio_ot ? date('Y-m-d\TH:i', strtotime($categoria->fecha_inicio_ot)) : '' }}";
                fechaFinInput.value = "{{ $categoria->fecha_fin_ot ? date('Y-m-d\TH:i', strtotime($categoria->fecha_fin_ot)) : '' }}";
            }
        } else {
            asignarBtn.disabled = true;
            asignarBtn.classList.remove('btn-primary');
            asignarBtn.classList.add('btn-secondary');
            
            fechaInicioInput.value = '';
            fechaFinInput.value = '';
        }
    }
    
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', verificarCheckboxes);
    });
    
    verificarCheckboxes();
});

async function enviarFrecuencia() {
    const form = document.getElementById('asignarFrecuenciaForm');
    const formData = new FormData(form);
    const data = Object.fromEntries(formData.entries());
    
    data.es_frecuente = data.es_frecuente === '1';

    try {
        const response = await fetch("{{ route('asignar.frecuencia') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': "{{ csrf_token() }}"
            },
            body: JSON.stringify(data)
        });

        const result = await response.json();
        if (response.ok) {
            Swal.fire({
                icon: 'success',
                title: 'Éxito',
                text: result.message,
                confirmButtonColor: '#3085d6',
            }).then(() => {
                location.reload();
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: "Error: " + (result.message || "Hubo un problema al asignar la frecuencia."),
                confirmButtonColor: '#3085d6',
            });
        }
    } catch (err) {
        console.error(err);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: "Error al procesar la solicitud.",
            confirmButtonColor: '#3085d6',
        });
    }
}


async function enviarFechas() {
    const fechaInicio = document.getElementById('fecha_inicio_gral').value;
    const fechaFin = document.getElementById('fecha_fin_gral').value;
    const cotio_numcoti = document.getElementById('cotio_numcoti').value;
    const cotio_item = document.getElementById('cotio_item').value;
    const cotio_subitem = document.getElementById('cotio_subitem').value;

    const data = {
        fecha_inicio_ot: fechaInicio,
        fecha_fin_ot: fechaFin,
        cotio_numcoti: cotio_numcoti,
        cotio_item: cotio_item,
        cotio_subitem: cotio_subitem,
    };

    try {
        const response = await fetch("{{ route('asignar.fechas') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': "{{ csrf_token() }}"
            },
            body: JSON.stringify(data)
        });

        const result = await response.json();

        if (response.ok) {
            Swal.fire({
                icon: 'success',
                title: 'Éxito',
                text: result.message,
                confirmButtonColor: '#3085d6',
            }).then(() => {
                location.reload();
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: "Error: " + (result.message || "Hubo un problema al asignar las fechas."),
                confirmButtonColor: '#3085d6',
            });
        }
    } catch (err) {
        console.error(err);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: "Error al procesar la solicitud.",
            confirmButtonColor: '#3085d6',
        });
    }
}



document.addEventListener('DOMContentLoaded', () => {
    const fechaWrapper = document.querySelector('.fecha-wrapper');
    const fechaFin = fechaWrapper?.dataset?.fechaFin;

    if (fechaFin) {
        const fechaFinDate = new Date(fechaFin);
        const hoy = new Date();

        const diffTiempo = fechaFinDate.getTime() - hoy.getTime();
        const diffDias = Math.ceil(diffTiempo / (1000 * 60 * 60 * 24));

        const fechaFinSpan = document.querySelector('.fecha-fin');

        if (diffDias <= 3) {
            fechaFinSpan.classList.add('fecha-roja');
        } else {
            fechaFinSpan.classList.add('fecha-verde');
        }
    }
});


document.addEventListener('DOMContentLoaded', function () {
    const checkbox = document.getElementById('es_frecuente');
    const container = document.getElementById('frecuencia_container');

    container.style.display = checkbox.checked ? 'block' : 'none';

    checkbox.addEventListener('change', function () {
        container.style.display = this.checked ? 'block' : 'none';
    });
});

</script>


<script>
    document.getElementById('seleccionarTodas').addEventListener('click', function() {
        const checkboxes = document.querySelectorAll('.tarea-checkbox');
        checkboxes.forEach(checkbox => {
            if(!checkbox.checked){
                checkbox.checked = true;
            } else {
                checkbox.checked = false;
            }
        });
    });

</script>


<script>
// Activar tooltips de resultados
document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });
});
</script>

<script>
// Manejo de guardado de resultados editables
document.addEventListener('DOMContentLoaded', function() {
    // Función para calcular el promedio para un formulario específico
    function calcularPromedio(form) {
        console.log('Calculando promedios...');
        
        const resultadoInput = form.querySelector('input[name="resultado"]');
        const resultado2Input = form.querySelector('input[name="resultado_2"]');
        const resultado3Input = form.querySelector('input[name="resultado_3"]');
        const resultadoFinalInput = form.querySelector('input[name="resultado_final"]');
        
        let valores = [];
        
        // Validar y recolectar valores numéricos
        if (resultadoInput && resultadoInput.value && !isNaN(parseFloat(resultadoInput.value))) {
            valores.push(parseFloat(resultadoInput.value));
        }
        if (resultado2Input && resultado2Input.value && !isNaN(parseFloat(resultado2Input.value))) {
            valores.push(parseFloat(resultado2Input.value));
        }
        if (resultado3Input && resultado3Input.value && !isNaN(parseFloat(resultado3Input.value))) {
            valores.push(parseFloat(resultado3Input.value));
        }
        
        console.log('Valores recolectados:', valores);
        
        // Calcular promedio si hay valores válidos
        if (valores.length > 0 && resultadoFinalInput) {
            const suma = valores.reduce((a, b) => a + b, 0);
            const promedio = suma / valores.length;
            resultadoFinalInput.value = promedio.toFixed(2);
            console.log('Promedio calculado:', promedio.toFixed(2));
        } else if (resultadoFinalInput) {
            resultadoFinalInput.value = '';
            console.log('Sin valores válidos para calcular promedio');
        }
    }
    
    // Función para inicializar los eventos de un formulario
    function inicializarFormulario(form) {
        console.log('Inicializando formulario:', form);
        
        const resultadoInput = form.querySelector('input[name="resultado"]');
        const resultado2Input = form.querySelector('input[name="resultado_2"]');
        const resultado3Input = form.querySelector('input[name="resultado_3"]');
        
        // Función para validar entrada numérica
        function validarNumerico(input) {
            if (input.value && isNaN(input.value)) {
                input.value = input.value.replace(/[^0-9.]/g, '');
                if (isNaN(input.value)) {
                    input.value = '';
                }
            }
        }
        
        // Agregar event listeners a los inputs relevantes
        [resultadoInput, resultado2Input, resultado3Input].forEach(input => {
            if (!input) return;
            
            input.addEventListener('input', function() {
                console.log('Input cambiado:', this.name, 'valor:', this.value);
                validarNumerico(this);
                calcularPromedio(form);
            });
            
            input.addEventListener('blur', function() {
                validarNumerico(this);
                calcularPromedio(form);
            });
        });
        
        // Calcular promedio inicial si hay valores
        calcularPromedio(form);
        
        // Manejar el envío del formulario
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            console.log('Formulario enviado');
            
            // Recalcular por si acaso hay cambios no detectados
            calcularPromedio(form);
            
            // Obtener datos del formulario
            const cotioNumcoti = this.dataset.cotioNumcoti;
            const cotioItem = this.dataset.cotioItem;
            const cotioSubitem = this.dataset.cotioSubitem;
            const instance = this.dataset.instance;
            
            console.log('Datos del formulario:', {
                cotioNumcoti,
                cotioItem,
                cotioSubitem,
                instance
            });
            
            // Recopilar todos los datos del formulario
            const formData = new FormData();
            formData.append('_token', '{{ csrf_token() }}');
            formData.append('_method', 'PUT');
            
            // Agregar todos los campos de resultado y observación
            const inputs = this.querySelectorAll('input, textarea');
            inputs.forEach(input => {
                if (input.name && input.value !== undefined) {
                    formData.append(input.name, input.value);
                    console.log('Agregando campo:', input.name, 'valor:', input.value);
                }
            });
            
            const submitBtn = this.querySelector('.guardar-todos-resultados');
            
            // Mostrar loader
            if (submitBtn) {
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Guardando...';
                submitBtn.disabled = true;
            }
            
            // Construir la URL correcta
            const url = `{{ route('tareas.updateResultado', ['cotio_numcoti' => ':cotio_numcoti', 'cotio_item' => ':cotio_item', 'cotio_subitem' => ':cotio_subitem', 'instance' => ':instance']) }}`
                .replace(':cotio_numcoti', cotioNumcoti)
                .replace(':cotio_item', cotioItem)
                .replace(':cotio_subitem', cotioSubitem)
                .replace(':instance', instance);
            
            console.log('URL de envío:', url);
            
            fetch(url, {
                method: 'POST', // Usar POST porque Laravel maneja PUT internamente
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                console.log('Respuesta recibida:', response);
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('Datos de respuesta:', data);
                showAlert('success', 'Resultados guardados correctamente');
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('danger', 'Error al guardar los resultados: ' + error.message);
            })
            .finally(() => {
                if (submitBtn) {
                    submitBtn.innerHTML = originalBtnText;
                    submitBtn.disabled = false;
                }
            });
        });
    }
    
    // Inicializar todos los formularios de resultados
    document.querySelectorAll('.resultados-form').forEach(form => {
        inicializarFormulario(form);
    });
    
    // Función para mostrar alertas (mejorada)
    function showAlert(type, message) {
        // Eliminar alertas existentes primero
        document.querySelectorAll('.custom-alert').forEach(alert => alert.remove());
        
        const alertDiv = document.createElement('div');
        alertDiv.className = `custom-alert alert alert-${type} alert-dismissible fade show fixed-top mx-auto mt-3`;
        alertDiv.style.maxWidth = '500px';
        alertDiv.style.zIndex = '1100';
        alertDiv.role = 'alert';
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        
        document.body.appendChild(alertDiv);
        
        // Auto-eliminar después de 5 segundos
        setTimeout(() => {
            alertDiv.remove();
        }, 5000);
        
        // Permitir cerrar manualmente
        alertDiv.querySelector('.btn-close').addEventListener('click', () => {
            alertDiv.remove();
        });
    }
    
    // Función para eliminar responsable de todas las tareas
    window.eliminarResponsableTodasTareas = function(usuCodigo) {
        if (!confirm('¿Estás seguro de que quieres eliminar este responsable de todas las tareas?')) {
            return;
        }
        
        // Obtener los datos necesarios de la página
        const cotioNumcoti = '{{ $cotizacion->coti_num }}';
        const cotioItem = '{{ $categoria->cotio_item }}';
        const instance = '{{ $instance }}';
        const instanciaId = '{{ $instanciaActual->id ?? "" }}';
        
        console.log('Datos para eliminar responsable:', {
            cotioNumcoti,
            cotioItem,
            instance,
            instanciaId,
            usuCodigo
        });
        
        // Crear el formulario de datos
        const formData = new FormData();
        formData.append('_token', '{{ csrf_token() }}');
        formData.append('instancia_id', instanciaId);
        formData.append('user_codigo', usuCodigo);
        formData.append('todos', 'true'); // Enviar como string 'true' o 'false'
        
        // Construir la URL
        const url = '{{ route("ordenes.remover-responsable", ["ordenId" => $cotizacion->coti_num]) }}';
        
        console.log('Enviando petición a:', url);
        
        fetch(url, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            console.log('Respuesta recibida:', response);
            return response.json();
        })
        .then(data => {
            console.log('Datos de respuesta:', data);
            if (data.success) {
                showAlert('success', data.message);
                // Recargar la página para mostrar los cambios
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                showAlert('danger', data.message || 'Error al eliminar el responsable');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('danger', 'Error al eliminar el responsable: ' + error.message);
        });
    };
});
</script>


@endsection
