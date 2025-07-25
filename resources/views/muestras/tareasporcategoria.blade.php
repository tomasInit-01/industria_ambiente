@extends('layouts.app')
<head>
    <title>Cotización {{$cotizacion->coti_num}} | {{$categoria->cotio_descripcion}} - Muestra {{$instance}}</title>
</head>

{{-- @dd($instanciaActual) --}}


@section('content')
<div class="container py-4">
    <div class="d-flex flex-column gap-2 flex-md-row justify-content-between align-items-center mb-4">
        <a href="{{ url('/show/'.$cotizacion->coti_num) }}" class="btn btn-outline-secondary d-flex align-items-center gap-2">
            Volver a la cotización
        </a>
        <div class="d-flex flex-column flex-md-row gap-2">
            <div class="dropdown">
                <button class="btn btn-secondary dropdown-toggle" type="button" id="instanceDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    Muestra {{$instance}} de {{$categoria->cotio_cantidad}}
                </button>
                <ul class="dropdown-menu" aria-labelledby="instanceDropdown">
                    @for($i = 1; $i <= $categoria->cotio_cantidad; $i++)
                        <li>
                            <a class="dropdown-item {{$i == $instance ? 'active' : ''}}" 
                               href="{{ route('muestras.ver', [
                                   'cotizacion' => $cotizacion->coti_num,
                                   'item' => $categoria->cotio_item,
                                   'instance' => $i
                               ]) }}">
                                Muestra {{$i}}
                                @if($instanciasMuestra[$i]->fecha_muestreo ?? false)
                                    <small class="text-muted">(Muestreada)</small>
                                @endif
                            </a>
                        </li>
                    @endfor
                </ul>
            </div>
        </div>
    </div>

    @include('cotizaciones.info')


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

    @if(!$instanciaActual) 
            <div class="alert alert-info mb-3">
                <strong>Muestra {{$instance}} de {{$categoria->cotio_cantidad}}</strong>
                No hay una muestras.
            </div>
        @else
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <div class="alert alert-info mb-3">
                    <strong>Muestra {{$instance}} de {{$categoria->cotio_cantidad}}</strong>
                    @if($instanciaActual->fecha_muestreo)
                        - Coordinada el {{ $instanciaActual->fecha_muestreo->format('d/m/Y H:i') }}
                        @if($instanciaActual->coordinador)
                            por {{ $instanciaActual->coordinador->usu_descripcion }}
                        @endif
                    @endif
                </div>

                <div class="d-flex justify-content-between align-items-center">
                    <h2 class="fw-bold">{{ $categoria->cotio_descripcion }} ({{ $instanciaActual->instance_number ?? ''}} / {{ $categoria->cotio_cantidad ?? ''}})</h2>
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
                                $estado = strtolower($instanciaActual->cotio_estado ?? $categoria->cotio_estado);
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
                            <span class="badge bg-{{ $badgeClass }}">{{ ucfirst($instanciaActual->cotio_estado ?? $categoria->cotio_estado) }}</span>
                            @if($instanciaActual->enable_ot == false)
                                <button type="button" class="btn btn-sm btn-link" data-bs-toggle="modal" data-bs-target="#estadoModal" data-tipo="categoria">
                                    <x-heroicon-o-pencil style="width: 20px; height: 20px;" />
                                </button>
                            @endif
                        </p>
                        <p class="text-muted mb-1">
                            <strong>Asignada a:</strong> 
                            @if ($instanciaActual->responsablesMuestreo->count() > 0)
                                @foreach ($instanciaActual->responsablesMuestreo as $responsable)
                                    <span class="badge bg-info d-inline-flex align-items-center me-2 mb-1">
                                        {{ $responsable->usu_descripcion }}

                                    @if($instanciaActual->enable_ot == false)
                                        <button type="button" 
                                            class="btn btn-sm btn-link text-danger p-0 ms-1" 
                                            style="font-size: 0.75rem; line-height: 1;"
                                            onclick="eliminarResponsableTodasTareas('{{ $responsable->usu_codigo }}')"
                                            title="Eliminar de todas las tareas">
                                            <x-heroicon-o-x-mark style="width: 12px; height: 12px;" />
                                        </button>
                                    @endif
                            
                                </span>
                                @endforeach
                            @else
                                <span class="badge bg-secondary">Sin asignar</span>
                            @endif
                        </p>
                        
                    </div>
                    <div class="col-md-6">
                        <p class="text-muted mb-1">
                            <strong>Vehículo:</strong> 
                            @if ($instanciaActual->vehiculo_asignado)
                                {{ $instanciaActual->vehiculo->marca ?? 'N/A' }} {{ $instanciaActual->vehiculo->modelo ?? 'N/A' }} ({{ $instanciaActual->vehiculo->patente ?? 'N/A' }})
                            @else
                                Sin asignar
                            @endif
                        </p>
                        <p class="text-muted mb-1">
                            <strong>Frecuencia:</strong> 
                            @if ($instanciaActual->es_frecuente)
                                Frecuencia asignada
                            @else
                                Puntual
                            @endif
                        </p>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-12">
                        <p class="text-muted mb-1 fecha-wrapper" data-fecha-fin="{{ $instanciaActual->fecha_fin_muestreo ?? $categoria->fecha_fin ?? '' }}">
                            <strong>Inicio:</strong> 
                            <span class="{{ $instanciaActual->fecha_inicio_muestreo ?? $categoria->fecha_inicio ? 'bg-light text-dark px-2 py-1 rounded' : '' }}">
                                {{ $instanciaActual->fecha_inicio_muestreo ?? $categoria->fecha_inicio ?? 'Faltante' }}
                            </span>
                            &nbsp;&nbsp;|&nbsp;&nbsp;
                            <strong>Fin:</strong> 
                            <span class="fecha-fin {{ $instanciaActual->fecha_fin_muestreo ?? $categoria->fecha_fin ? 'bg-light text-dark px-2 py-1 rounded' : '' }}">
                                {{ $instanciaActual->fecha_fin_muestreo ?? $categoria->fecha_fin ?? 'Faltante' }}
                            </span>
                        </p>
                    </div>
                </div>


                {{-- herramientas asignadas --}}
                @if(isset($herramientasMuestra) && $herramientasMuestra->count())
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-light">
                            <h5 class="card-title mb-0 p-2">Herramientas de Muestreo</h5>
                        </div>
                        <div class="card-body">
                            <ul class="list-group">
                                @foreach ($herramientasMuestra as $herramienta)
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        {{ $herramienta->equipamiento }}
                                        @if($herramienta->marca_modelo)
                                            <small class="text-muted">({{ $herramienta->marca_modelo }})</small>
                                        @endif
                                        @if($herramienta->pivot_observaciones)
                                            <small class="text-muted">{{ $herramienta->pivot_observaciones }}</small>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif


                @if($instanciaActual && $variablesMuestra->isNotEmpty())
                <div class="card shadow-sm my-5">
                    <div class="card-header bg-secondary text-white">
                        <h5 style="cursor: pointer; color: black; padding: 10px;" data-bs-toggle="collapse" data-bs-target="#variablesCollapse" aria-expanded="false" aria-controls="variablesCollapse">
                            Variables de Muestra y Observaciones
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
                                            <th style="width: 100px; text-align: center; white-space: nowrap;">Historial de Cambios</th>
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
                                                           @if($instanciaActual->cotio_estado == 'muestreado') readonly @endif>
                                                </td>
                                                <td style="display: flex; justify-content: center; align-items: center;">
                                                    @if(isset($historialCambios[$variable->id]))
                                                        <button class="btn btn-sm btn-link btn-historial" 
                                                                data-variable-id="{{ $variable->id }}" 
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#historialModal">
                                                            <x-heroicon-o-clock style="width: 20px; height: 20px;" />
                                                        </button>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            
                            <div class="mt-4">
                                <label for="observaciones" class="form-label"><strong>Tu observación:</strong></label>
                                <textarea class="form-control" id="observaciones" rows="3" 
                                          @if($instanciaActual->cotio_estado == 'muestreado') readonly @endif>{{ $instanciaActual->observaciones_medicion_coord_muestreo }}</textarea>
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

                @if($instanciaActual->cotio_estado == 'suspension')
                    <div class="alert alert-danger">
                        <strong>Motivos de suspensión:</strong> {{ $instanciaActual->cotio_observaciones_suspension ?? 'N/A' }}
                    </div>
                @endif

                @if($instanciaActual->cotio_identificacion ?? 'N/A' )
                    <div class="alert alert-info">
                        <strong>Identificador de muestra:</strong> {{ $instanciaActual->cotio_identificacion ?? 'N/A' }}
                    </div>
                @endif

                @if($instanciaActual->image)
                    <div class="mt-3">
                        <img src="{{ Storage::url('images/' . $instanciaActual->image) }}" alt="Imagen de la muestra" class="img-fluid w-25 rounded">
                    </div>
                @endif

                {{-- añadir boton para 'habilit en otro analisis' solo si la instancia actual y los analisis tienen un estado 'finalizado' --}}
                @if($instanciaActual->cotio_estado == 'finalizado' || $instanciaActual->cotio_estado == 'muestreado' && $instanciaActual->enable_ot == false)
                    <form action="{{ route('categorias.enable-ot', [
                        'cotio_numcoti' => $categoria->cotio_numcoti,
                        'cotio_item' => $categoria->cotio_item,
                        'cotio_subitem' => $categoria->cotio_subitem,
                        'instance' => $instance
                    ]) }}" method="POST">
                        @csrf
                        <input type="hidden" name="cotio_numcoti" value="{{ $categoria->cotio_numcoti }}">
                        <input type="hidden" name="cotio_item" value="{{ $categoria->cotio_item }}">
                        <input type="hidden" name="cotio_subitem" value="{{ $categoria->cotio_subitem }}">
                        <input type="hidden" name="instance" value="{{ $instance }}">
                        <button class="btn btn-success mt-2">Pasar a OT</button>
                    </form>
                @endif

                @if($instanciaActual->enable_ot == true)
                    <form action="{{ route('categorias.disable-ot', [
                        'cotio_numcoti' => $categoria->cotio_numcoti,
                        'cotio_item' => $categoria->cotio_item,
                        'cotio_subitem' => $categoria->cotio_subitem,
                        'instance' => $instance
                    ]) }}" method="POST">
                        @csrf
                        <input type="hidden" name="cotio_numcoti" value="{{ $categoria->cotio_numcoti }}">
                        <input type="hidden" name="cotio_item" value="{{ $categoria->cotio_item }}">
                        <input type="hidden" name="cotio_subitem" value="{{ $categoria->cotio_subitem }}">
                        <input type="hidden" name="instance" value="{{ $instance }}">
                        <button class="btn btn-danger mt-2">Deshabilitar OT</button>
                    </form>
                @endif



            </div>
        </div>



        @if($tareas->count())
            <div class="card shadow-sm">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 p-2">Análisis agregados a la muestra</h5>
                    <div class="d-flex gap-2">
                        <form class="p-2 mb-0" action="{{ route('muestras.finalizar-todas', [
                            'cotio_numcoti' => $categoria->cotio_numcoti,
                            'cotio_item' => $categoria->cotio_item,
                            'cotio_subitem' => $categoria->cotio_subitem,
                            'instance_number' => $instanciaActual->instance_number
                        ]) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-primary">
                                <x-heroicon-o-check style="width: 20px; height: 20px;"/>
                                Finalizar todas
                            </button>
                        </form>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row row-cols-1 row-cols-md-2 g-3">
                        @foreach ($tareas as $tarea)
                            <div class="col">
                                <div class="card h-100">
                                    <div class="card-body" style="background-color: #A6C5E3">
                                        <div class="form-check mb-2">
                                            <input class="form-check-input tarea-checkbox" 
                                                    type="checkbox" 
                                                    name="tareas_seleccionadas[]" 
                                                    value="{{ $tarea->cotio_item }}_{{ $tarea->cotio_subitem }}"
                                                    id="tarea_{{ $tarea->cotio_item }}_{{ $tarea->cotio_subitem }}"
                                                    data-fecha-inicio="{{ $tarea->fecha_inicio_muestreo ? date('Y-m-d\TH:i', strtotime($tarea->fecha_inicio_muestreo)) : '' }}"
                                                    data-fecha-fin="{{ $tarea->fecha_fin_muestreo ? date('Y-m-d\TH:i', strtotime($tarea->fecha_fin_muestreo)) : '' }}">
                                            <label class="form-check-label" for="tarea_{{ $tarea->cotio_item }}_{{ $tarea->cotio_subitem }}">
                                                <h5 class="card-title">{{ $tarea->cotio_descripcion }}</h5>
                                            </label>
                                        </div>

                                        @if($tarea->instancia && $tarea->instancia->resultado)
                                            <p class="mb-2">
                                                Resultado: 
                                                <span class="badge bg-success">{{ $tarea->instancia->resultado }}</span>
                                            </p>
                                        @endif

                                        <p class="text-muted mb-3">
                                            <strong>Asignada a:</strong> 
                                            @if ($tarea->instancia->responsablesMuestreo->count() > 0)
                                                @foreach ($tarea->instancia->responsablesMuestreo as $responsable)
                                                    <span class="badge bg-primary">{{ $responsable->usu_descripcion }}</span>
                                                @endforeach
                                            @else
                                                <span class="badge bg-secondary">Sin asignar</span>
                                            @endif
                                        </p>

                                        @if ($tarea->instancia->vehiculo_asignado)
                                            <div class="mb-2 d-flex align-items-center justify-content-between">
                                                <p class="mb-1">Vehículo: 
                                                    <span class="badge bg-primary">{{ $tarea->instancia->vehiculo->marca ?? 'N/A' }} {{ $tarea->instancia->vehiculo->modelo ?? 'N/A' }} ({{ $tarea->instancia->vehiculo->patente ?? 'N/A' }})</span>
                                                </p>
                                                <form 
                                                    action="{{ route('tareas.desasignar-vehiculo', [
                                                        'cotizacion' => $tarea->instancia->cotio_numcoti,
                                                        'item' => $tarea->instancia->cotio_item,
                                                        'subitem' => $tarea->instancia->cotio_subitem,
                                                        'vehiculo_id' => $tarea->instancia->vehiculo_asignado
                                                    ]) }}" 
                                                    method="POST"
                                                    class="d-inline"
                                                >
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger">
                                                        <x-heroicon-o-trash style="width: 20px; height: 20px;" />
                                                    </button>
                                                </form>
                                            </div>
                                        @endif

                                        @if(isset($tarea->instancia->herramientas) && $tarea->instancia->herramientas->count())
                                            <div class="card shadow-sm mb-3">
                                                <div class="card-header bg-light">
                                                    <h6 class="card-title mb-0 p-2">Herramientas para {{ $tarea->cotio_descripcion }}</h6>
                                                </div>
                                                <div class="card-body">
                                                    <ul class="list-group">
                                                        @foreach ($tarea->instancia->herramientas as $herramienta)
                                                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                                                {{ $herramienta->equipamiento }}
                                                                @if($herramienta->marca_modelo)
                                                                    <small class="text-muted">({{ $herramienta->marca_modelo }})</small>
                                                                @endif
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                            </div>
                                        @endif

                                        <p class="text-muted mb-2 fecha-wrapper" data-fecha-fin="{{ $tarea->instancia->fecha_fin_muestreo ? $tarea->instancia->fecha_fin_muestreo : '' }}">
                                            <strong>Inicio:</strong> 
                                            <span class="{{ $tarea->instancia->fecha_inicio_muestreo ? 'bg-light text-dark px-2 py-1 rounded' : '' }}">
                                                {{ $tarea->instancia->fecha_inicio_muestreo ? date('d/m/Y H:i', strtotime($tarea->instancia->fecha_inicio_muestreo)) : 'Faltante' }}
                                            </span>
                                            &nbsp;&nbsp;|&nbsp;&nbsp;
                                            <strong>Fin:</strong> 
                                            <span class="fecha-fin {{ $tarea->instancia->fecha_fin_muestreo ? 'bg-light text-dark px-2 py-1 rounded' : '' }}">
                                                {{ $tarea->instancia->fecha_fin ? date('d/m/Y H:i', strtotime($tarea->instancia->fecha_fin)) : 'Faltante' }}
                                            </span>
                                        </p>

                                    
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @else
            <div class="alert alert-info">
                <x-heroicon-o-information-circle style="width: 20px; height: 20px;" />No hay análisis agregados al muestreo.
            </div>
        @endif
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
                                <option value="coordinado muestreo" {{ ($instanciaActual->cotio_estado ?? 'pendiente') == 'coordinado muestreo' ? 'selected' : '' }}>coordinado muestreo</option>
                                <option value="en revision muestreo" {{ ($instanciaActual->cotio_estado ?? 'pendiente') == 'en revision muestreo' ? 'selected' : '' }}>En revision Muestreo</option>
                                <option value="muestreado" {{ ($instanciaActual->cotio_estado ?? 'pendiente') == 'muestreado' ? 'selected' : '' }}>muestreado</option>
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

    <div class="modal fade" id="herramientasModal" tabindex="-1" aria-labelledby="herramientasModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="herramientasModalLabel">Seleccionar Herramientas</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-12">
                            <div class="list-group" style="max-height: 400px; overflow-y: auto;">
                                @foreach($inventario as $item)
                                    <label class="list-group-item d-flex align-items-center">
                                        <input class="form-check-input me-3" type="checkbox" value="{{ $item->id }}" 
                                               data-equipamiento="{{ $item->equipamiento }}"
                                               data-marca="{{ $item->marca_modelo }}"
                                               data-serie="{{ $item->n_serie_lote }}">
                                        <div>
                                            <div class="fw-bold">{{ $item->equipamiento }}</div>
                                            <small class="text-muted">{{ $item->marca_modelo }} - {{ $item->n_serie_lote }}</small>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="guardarSeleccionHerramientas()">Guardar Selección</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="historialModal" tabindex="-1" aria-labelledby="historialModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="historialModalLabel">Historial de Cambios</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="historialContent">
                        <p>Seleccione una variable para ver su historial.</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
</div>



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
            const response = await fetch('{{ route("tareas.actualizar-estado") }}', {
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
    // Inicializar Select2 para herramientas
    $('#herramientas').select2({
        placeholder: "Seleccione herramientas",
        width: '100%',
        dropdownParent: $('#herramientasModal')
    });

    // Configurar fechas al cargar el formulario
    function setupInitialDates() {
        const fechaInicioInput = document.getElementById('fecha_inicio_muestreo');
        const fechaFinInput = document.getElementById('fecha_fin_muestreo');
        
        // Si no hay valores establecidos, configurar defaults
        if (!fechaInicioInput.value) {
            const now = new Date();
            let defaultStartDate = new Date();
            
            // Si hoy es fin de semana, establecer para el próximo lunes
            if (now.getDay() === 0) defaultStartDate.setDate(now.getDate() + 1);
            else if (now.getDay() === 6) defaultStartDate.setDate(now.getDate() + 2);
            
            // Establecer hora a las 8:00 AM
            defaultStartDate.setHours(8, 0, 0, 0);
            fechaInicioInput.value = formatDateTimeForInput(defaultStartDate);
            
            // Establecer fecha fin (mismo día a las 6:00 PM)
            if (!fechaFinInput.value) {
                let defaultEndDate = new Date(defaultStartDate);
                defaultEndDate.setHours(18, 0, 0, 0);
                fechaFinInput.value = formatDateTimeForInput(defaultEndDate);
            }
        }
    }

    // Función para formatear fecha al formato que espera el input datetime-local
    function formatDateTimeForInput(date) {
        return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}T${String(date.getHours()).padStart(2, '0')}:${String(date.getMinutes()).padStart(2, '0')}`;
    }

    // Validar fecha inicio al cambiar
    document.getElementById('fecha_inicio_muestreo').addEventListener('change', function() {
        const startDateInput = this;
        const endDateInput = document.getElementById('fecha_fin_muestreo');
        const startDate = new Date(startDateInput.value);
        
        if (startDate.getDay() === 0 || startDate.getDay() === 6) {
            Swal.fire({
                title: 'Día no válido',
                text: 'No se pueden seleccionar sábados o domingos como fecha de inicio',
                icon: 'error'
            });
            startDateInput.value = '';
            return;
        }
        
        if (endDateInput.value) {
            const endDate = new Date(endDateInput.value);
            if (endDate <= startDate) {
                const newEndDate = new Date(startDate);
                newEndDate.setHours(18, 0, 0, 0);
                endDateInput.value = formatDateTimeForInput(newEndDate);
            }
        } else {
            const defaultEndDate = new Date(startDate);
            defaultEndDate.setHours(18, 0, 0, 0);
            endDateInput.value = formatDateTimeForInput(defaultEndDate);
        }
    });

    // Validar fecha fin al cambiar
    document.getElementById('fecha_fin_muestreo').addEventListener('change', function() {
        const endDateInput = this;
        const startDateInput = document.getElementById('fecha_inicio_muestreo');
        
        if (!startDateInput.value) {
            Swal.fire({
                title: 'Fecha requerida',
                text: 'Primero debe seleccionar una fecha de inicio',
                icon: 'warning'
            });
            endDateInput.value = '';
            return;
        }
        
        const startDate = new Date(startDateInput.value);
        const endDate = new Date(endDateInput.value);
        
        if (endDate.getDay() === 0 || endDate.getDay() === 6) {
            Swal.fire({
                title: 'Día no válido',
                text: 'No se pueden seleccionar sábados o domingos como fecha de fin',
                icon: 'error'
            });
            endDateInput.value = '';
            return;
        }
        
        if (endDate <= startDate) {
            Swal.fire({
                title: 'Fecha inválida',
                text: 'La fecha de fin debe ser posterior a la fecha de inicio',
                icon: 'error'
            });
            const defaultEndDate = new Date(startDate);
            defaultEndDate.setHours(18, 0, 0, 0);
            endDateInput.value = formatDateTimeForInput(defaultEndDate);
            return;
        }
        
        if (endDate.getDate() !== startDate.getDate() || 
            endDate.getMonth() !== startDate.getMonth() || 
            endDate.getFullYear() !== startDate.getFullYear()) {
            Swal.fire({
                title: 'Fecha inválida',
                text: 'La fecha de fin debe ser el mismo día que la fecha de inicio',
                icon: 'error'
            });
            const defaultEndDate = new Date(startDate);
            defaultEndDate.setHours(18, 0, 0, 0);
            endDateInput.value = formatDateTimeForInput(defaultEndDate);
        }
    });

    // Manejar envío del formulario
    document.getElementById('asignarForm').addEventListener('submit', async function(e) {
        e.preventDefault();

        // Validación de fechas antes de enviar
        const fechaInicio = document.getElementById('fecha_inicio_muestreo').value;
        const fechaFin = document.getElementById('fecha_fin_muestreo').value;
        
        if (!fechaInicio || !fechaFin) {
            Swal.fire({
                title: 'Fechas requeridas',
                text: 'Debe especificar fechas de inicio y fin',
                icon: 'warning'
            });
            return;
        }

        if (new Date(fechaFin) <= new Date(fechaInicio)) {
            Swal.fire({
                title: 'Fecha inválida',
                text: 'La fecha de fin debe ser posterior a la fecha de inicio',
                icon: 'error'
            });
            return;
        }

        // Obtener herramientas seleccionadas como array de IDs
        const herramientasSeleccionadas = $('#herramientas').val() || [];
        
        // Obtener tareas seleccionadas como array
        const tareasSeleccionadas = Array.from(document.querySelectorAll('.tarea-checkbox:checked'))
            .map(checkbox => checkbox.value);

        // Crear objeto FormData
        const formData = new FormData();
        formData.append('cotio_numcoti', document.querySelector('input[name="cotio_numcoti"]').value);
        formData.append('cotio_item', document.querySelector('input[name="cotio_item"]').value);
        formData.append('instance', document.querySelector('input[name="instance"]').value);
        formData.append('vehiculo_asignado', document.getElementById('vehiculo_asignado').value || '');
        formData.append('responsable_codigo', document.getElementById('responsable_codigo').value || '');
        formData.append('fecha_inicio_muestreo', fechaInicio);
        formData.append('fecha_fin_muestreo', fechaFin);

        // Agregar herramientas como array
        herramientasSeleccionadas.forEach(herramienta => {
            formData.append('herramientas[]', herramienta);
        });

        // Agregar tareas seleccionadas como array
        tareasSeleccionadas.forEach(tarea => {
            formData.append('tareas_seleccionadas[]', tarea);
        });

        try {
            const response = await fetch(e.target.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': "{{ csrf_token() }}",
                    'Accept': 'application/json'
                },
                body: formData
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
                throw new Error(data.message || "Hubo un problema al procesar la solicitud");
            }
        } catch (err) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: err.message,
                confirmButtonColor: '#3085d6',
            });
            console.error(err);
        }
    });

    // Configurar fechas iniciales al cargar
    setupInitialDates();
});


async function enviarFrecuencia() {
    const form = document.getElementById('asignarFrecuenciaForm');
    const formData = new FormData(form);
    const data = Object.fromEntries(formData.entries());
    
    data.es_frecuente = data.es_frecuente === '1';

    // Obtener las tareas seleccionadas
    const tareasSeleccionadas = Array.from(document.querySelectorAll('.tarea-checkbox:checked'))
        .map(checkbox => {
            const [item, subitem] = checkbox.value.split('_');
            return { item, subitem };
        });

    data.tareas_seleccionadas = tareasSeleccionadas;

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
    $(document).ready(function() {
        $('.select2-multiple').select2({
            theme: 'bootstrap-5',
            width: '100%'
        });
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Inicializar Select2
        $('.select2-multiple').select2({
            theme: 'bootstrap-5',
            width: '100%'
        });

        // Manejar el envío del formulario de identificación
        document.getElementById('identificacionForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            try {
                const formData = new FormData(this);
                const response = await fetch(this.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
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
                        text: data.message || 'Error al guardar los cambios',
                        confirmButtonColor: '#3085d6',
                    });
                }
            } catch (error) {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Ocurrió un error al procesar la solicitud',
                    confirmButtonColor: '#3085d6',
                });
            }
        });
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('medicionesForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            try {
                const formData = new FormData(this);
                const response = await fetch(this.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
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
                        text: data.message || 'Error al guardar las mediciones',
                        confirmButtonColor: '#3085d6',
                    });
                }
            } catch (error) {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Ocurrió un error al procesar la solicitud',
                    confirmButtonColor: '#3085d6',
                });
            }
        });
    });
</script>

<script>
    function toggleFormularios() {
        const content = document.getElementById('formularios-content');
        const chevron = document.getElementById('chevron-formularios');
        const header = content.previousElementSibling;
        
        if (content.style.display === 'none' || getComputedStyle(content).display === 'none') {
            content.style.display = 'block';
            let height = content.scrollHeight + 'px';
            content.style.height = '0';
            requestAnimationFrame(() => {
                content.style.transition = 'height 0.3s ease';
                content.style.height = height;
            });
            
            chevron.setAttribute('transform', 'rotate(180)');
            header.setAttribute('aria-expanded', 'true');
            
            content.addEventListener('transitionend', function handler() {
                content.style.height = 'auto';
                content.removeEventListener('transitionend', handler);
            });
        } else {
            content.style.height = content.scrollHeight + 'px';
            requestAnimationFrame(() => {
                content.style.transition = 'height 0.3s ease';
                content.style.height = '0';
            });
            
            chevron.setAttribute('transform', 'rotate(0)');
            header.setAttribute('aria-expanded', 'false');
            
            content.addEventListener('transitionend', function handler() {
                content.style.display = 'none';
                content.removeEventListener('transitionend', handler);
            });
        }
    }

    // Inicializar el estado del formulario
    document.addEventListener('DOMContentLoaded', function() {
        const content = document.getElementById('formularios-content');
        content.style.display = 'none';
        content.style.height = '0';
        content.style.overflow = 'hidden';
    });
</script>

<script>
    function guardarSeleccionHerramientas() {
        const checkboxes = document.querySelectorAll('#herramientasModal .form-check-input:checked');
        const select = document.getElementById('herramientas');
        const contenedor = document.getElementById('herramientas-seleccionadas');
        
        select.innerHTML = '';
        contenedor.innerHTML = '';
        
        if (checkboxes.length === 0) {
            contenedor.innerHTML = '<small class="text-muted">Ninguna herramienta seleccionada</small>';
        } else {
            const badges = [];
            checkboxes.forEach(checkbox => {
                const option = document.createElement('option');
                option.value = checkbox.value;
                option.selected = true;
                option.text = checkbox.dataset.equipamiento;
                select.appendChild(option);
                
                badges.push(`
                    <span class="badge bg-primary me-2 mb-2">
                        ${checkbox.dataset.equipamiento}
                        <button type="button" class="btn-close btn-close-white ms-2" 
                                style="font-size: 0.5rem;"
                                onclick="quitarHerramienta(${checkbox.value})"></button>
                    </span>
                `);
            });
            contenedor.innerHTML = badges.join('');
        }
        
        bootstrap.Modal.getInstance(document.getElementById('herramientasModal')).hide();
    }

    function quitarHerramienta(id) {
        const checkbox = document.querySelector(`#herramientasModal .form-check-input[value="${id}"]`);
        if (checkbox) {
            checkbox.checked = false;
        }
        guardarSeleccionHerramientas();
    }
</script>

<script>
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
            alert('Error al remover responsable', error);
        });
    }   
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
    document.addEventListener('DOMContentLoaded', function() {
        // Guardar todas las variables y observaciones
        document.querySelectorAll('.save-all-data').forEach(button => {
            button.addEventListener('click', function() {
                const instanciaId = this.dataset.instanciaId;
                const button = this;
                
                // Recopilar todas las variables (solo las que tienen valores)
                const variables = [];
                document.querySelectorAll('.variable-value').forEach(input => {
                    const valor = input.value.trim();
                    if (valor !== '') { // Solo incluir variables con valores
                        variables.push({
                            id: input.dataset.id,
                            valor: valor
                        });
                    }
                });
                
                // Obtener las observaciones
                const observaciones = document.getElementById('observaciones').value.trim();
                
                // Validar que al menos hay una variable
                if (variables.length === 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Advertencia',
                        text: 'Debes ingresar al menos un valor de variable antes de guardar.',
                        confirmButtonColor: '#3085d6',
                    });
                    return;
                }
                
                // Mostrar indicador de carga
                button.disabled = true;
                button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';
                
                // Configurar los headers
                const headers = {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                };
                
                // Configurar el cuerpo de la petición
                const body = JSON.stringify({
                    instancia_id: instanciaId,
                    variables: variables,
                    observaciones: observaciones
                });
                
                console.log('Enviando datos:', body); // Para debugging
                
                // Hacer la petición con fetch
                fetch('{{ route("muestras.updateAllData") }}', {
                    method: 'PUT',
                    headers: headers,
                    body: body,
                    credentials: 'same-origin'
                })
                .then(response => {
                    if (!response.ok) {
                        return response.json().then(errorData => {
                            // Si hay errores de validación específicos, mostrarlos
                            if (errorData.errors) {
                                const errorMessages = Object.values(errorData.errors).flat().join('\n');
                                throw new Error('Errores de validación:\n' + errorMessages);
                            }
                            throw new Error(errorData.message || 'Error en la respuesta del servidor');
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    Swal.fire({
                        icon: 'success',
                        title: 'Éxito',
                        text: data.message || 'Variables y observaciones actualizadas correctamente',
                        confirmButtonColor: '#3085d6',
                    });
                    button.innerHTML = '<i class="fas fa-check"></i> Guardado';
                    setTimeout(() => {
                        button.innerHTML = '<i class="fas fa-save"></i> Guardar Variables y Observaciones';
                        button.disabled = false;
                    }, 2000);
                })
                .catch(error => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Error al actualizar los datos:\n' + error.message,
                        confirmButtonColor: '#3085d6',
                    });
                    button.innerHTML = '<i class="fas fa-save"></i> Guardar Variables y Observaciones';
                    button.disabled = false;
                    console.error('Error:', error);
                });
            });
        });
        
        // Opcional: permitir guardar con Enter en cualquier campo
        document.querySelectorAll('.variable-value, #observaciones').forEach(input => {
            input.addEventListener('keypress', function(e) {
                if(e.which === 13) { // Tecla Enter
                    e.preventDefault();
                    document.querySelector('.save-all-data').click();
                }
            });
        });
        
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
            const url = '{{ route("muestras.remover-responsable") }}';
            
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
                    Swal.fire({
                        icon: 'success',
                        title: 'Éxito',
                        text: data.message,
                        confirmButtonColor: '#3085d6',
                    });
                    // Recargar la página para mostrar los cambios
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message || 'Error al eliminar el responsable',
                        confirmButtonColor: '#3085d6',
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error al eliminar el responsable: ' + error.message,
                    confirmButtonColor: '#3085d6',
                });
            });
        };
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const modal = new bootstrap.Modal(document.getElementById('historialModal'));
        const historial = @json($historialCambios);

        document.querySelectorAll('.btn-historial').forEach(button => {
            button.addEventListener('click', function() {
                const variableId = this.dataset.variableId;
                const cambios = historial[variableId] || [];

                let content = '';
                if (cambios.length === 0) {
                    content = '<p>No hay historial de cambios para esta variable.</p>';
                } else {
                    content = '<table class="table table-bordered table-striped">' +
                              '<thead><tr>' +
                              '<th>Fecha</th>' +
                              '<th>Usuario</th>' +
                              '<th>Acción</th>' +
                              '<th>Campo</th>' +
                              '<th>Valor Anterior</th>' +
                              '<th>Valor Nuevo</th>' +
                              '</tr></thead><tbody>';

                    cambios.forEach(cambio => {
                        content += `<tr>
                            <td>${new Date(cambio.fecha_cambio).toLocaleString()}</td>
                            <td>${cambio.usuario ? cambio.usuario.usu_descripcion : 'Desconocido'}</td>
                            <td>${cambio.accion.charAt(0).toUpperCase() + cambio.accion.slice(1)}</td>
                            <td>${cambio.campo_modificado}</td>
                            <td>${cambio.valor_anterior || 'N/A'}</td>
                            <td>${cambio.valor_nuevo || 'N/A'}</td>
                        </tr>`;
                    });

                    content += '</tbody></table>';
                }

                document.getElementById('historialContent').innerHTML = content;
                document.getElementById('historialModalLabel').textContent = `Historial de Cambios - Variable ID: ${variableId}`;
                modal.show();
            });
        });
    });
</script>

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

    .card-header {
        transition: background-color 0.3s ease;
    }
    
    .card-header:hover {
        background-color: #f8f9fa !important;
    }
    
    .card-header i, .card-header svg {
        transition: transform 0.3s ease;
    }
    
    .card-header[aria-expanded="true"] i,
    .card-header[aria-expanded="true"] svg {
        transform: rotate(180deg);
    }
    
    .select2-container {
        width: 100% !important;
        display: none !important;
    }
    
    .select2-container--bootstrap-5 .select2-selection {
        min-height: 38px;
    }
    
    .form-control:focus, .form-select:focus {
        border-color: #86b7fe;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    }

    .nav-tabs .nav-link {
        color: #6c757d;
        border: none;
        padding: 0.75rem 1.25rem;
        transition: all 0.3s ease;
    }

    .nav-tabs .nav-link:hover {
        color: #0d6efd;
        background-color: rgba(13, 110, 253, 0.1);
    }

    .nav-tabs .nav-link.active {
        color: #0d6efd;
        background-color: transparent;
        border-bottom: 2px solid #0d6efd;
    }

    .card-header {
        padding: 0;
        background-color: transparent !important;
    }

    .tab-content {
        padding: 1.5rem 0;
    }

    .list-group-item {
        cursor: pointer;
        transition: background-color 0.2s;
    }
    
    .list-group-item:hover {
        background-color: #f8f9fa;
    }
    
    .form-check-input:checked + div {
        color: #0d6efd;
    }
    
    .badge {
        font-size: 0.9em;
        padding: 0.5em 0.8em;
    }
    
    .btn-close-white {
        opacity: 0.8;
    }
    
    .btn-close-white:hover {
        opacity: 1;
    }
</style>

@endsection
