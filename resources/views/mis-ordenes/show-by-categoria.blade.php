@extends('layouts.app')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

{{-- @dd($instancia); --}}

@section('content')
<div class="container py-4">
    <!-- Encabezado -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0">
            Detalle de Muestra
            @if($instanceNumber)
                <span class="fs-5 text-muted">(Muestra #{{ $instanceNumber }})</span>
            @endif
        </h1>
        <a href="{{ url()->previous() }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                {{ $instancia->cotio_descripcion ?? 'N/A' }}
                @php
                    $estadoMuestra = strtolower($instancia->cotio_estado_analisis ?? 'pendiente');
                    $badgeClassMuestra = match ($estadoMuestra) {
                        'coordinado muestreo' => 'warning',
                        'pendiente' => 'warning',
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
                <span class="badge bg-{{ $badgeClassMuestra }} ms-2">
                    {{ ucfirst($instancia->cotio_estado_analisis ?? 'pendiente') }}
                </span>
            </h5>

            @if(Auth::user()->rol != 'laboratorio')

                <div>
                    <div class="btn-group" role="group">
                        @if($instancia->cotio_estado_analisis != 'suspension')
                        <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#suspenderModal">
                            <i class="fas fa-pause me-1"></i> Suspender
                        </button>
                        @else
                        <button type="button" class="btn btn-sm btn-secondary" disabled>
                            <i class="fas fa-pause me-1"></i> Ya suspendida
                        </button>
                        @endif
                    </div>
    
                    <button class="btn btn-sm btn-outline-light" data-bs-toggle="modal" data-bs-target="#editMuestraModal">
                        Editar Muestra
                    </button>
                </div>
            @endif

        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <p><strong>Cotización:</strong> {{ $instancia->cotio_numcoti ?? 'N/A' }}</p>
                </div>
                <div class="col-md-4">
                    <p><strong>Fecha Inicio:</strong> 
                        {{ $instancia->fecha_inicio_ot ? \Carbon\Carbon::parse($instancia->fecha_inicio_ot)->format('d/m/Y') : 'N/A' }}
                    </p>
                    <p><strong>Fecha Fin:</strong> 
                        {{ $instancia->fecha_fin_ot ? \Carbon\Carbon::parse($instancia->fecha_fin_ot)->format('d/m/Y') : 'N/A' }}
                    </p>
                </div>

                <div class="col-md-4">
                    <p><strong>Identificación:</strong> {{ $instancia->cotio_identificacion ?? 'N/A' }}</p>
                </div>


                <div class="col-md-4">
                    @if($instancia->image)
                        <img src="{{ Storage::url('images/' . $instancia->image) }}" alt="Imagen de la muestra" class="img-fluid w-50 rounded">
                    @else
                        <p>No hay imagen disponible</p>
                    @endif
                </div>
            </div>

            @if($instancia->herramientasLab->count() > 0)
            <div class="mt-3 pt-3 border-top">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6>Herramientas asignadas</h6>
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#editHerramientasModal">
                        <i class="fas fa-edit"></i> Editar Herramientas
                    </button>
                </div>
                <div class="row">
                    @foreach($instancia->herramientasLab as $herramienta)
                        <div class="col-md-4 mb-2">
                            <div class="card border">
                                <div class="card-body p-2">
                                    <h6 class="mb-1">{{ $herramienta->equipamiento }}</h6>
                                    <p class="small mb-1 text-muted">{{ $herramienta->marca_modelo }}</p>
                                    {{-- <p class="small mb-1"><strong>Cantidad:</strong> {{ $herramienta->pivot->cantidad }}</p> --}}
                                    @if($herramienta->pivot->observaciones)
                                        <p class="small mb-1"><strong>Observaciones:</strong> {{ $herramienta->pivot->observaciones }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @else
            <div class="alert alert-info mt-3">
                No hay herramientas asignadas. 
                <button type="button" class="btn btn-sm btn-primary ms-2" data-bs-toggle="modal" data-bs-target="#editHerramientasModal">
                    <i class="fas fa-plus"></i> Agregar Herramientas
                </button>
            </div>
        @endif
        
        <!-- Modal para editar herramientas -->
        <div class="modal fade" id="editHerramientasModal" tabindex="-1" aria-labelledby="editHerramientasModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <form action="{{ route('instancias.update-herramientas', $instancia->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="modal-header">
                            <h5 class="modal-title" id="editHerramientasModalLabel">Editar Herramientas Asignadas</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Herramientas Disponibles</label>
                                <select class="form-select select2-herramientas" name="herramientas[]" multiple="multiple" style="width: 100%;">
                                    @foreach($allHerramientas as $herramienta)
                                        <option value="{{ $herramienta->id }}" 
                                            {{ $instancia->herramientasLab->contains($herramienta->id) ? 'selected' : '' }}
                                            data-cantidad="{{ $instancia->herramientasLab->contains($herramienta->id) ? $instancia->herramientasLab->find($herramienta->id)->pivot->cantidad : 1 }}"
                                            data-observaciones="{{ $instancia->herramientasLab->contains($herramienta->id) ? $instancia->herramientasLab->find($herramienta->id)->pivot->observaciones : '' }}">
                                            {{ $herramienta->equipamiento }} ({{ $herramienta->marca_modelo }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
        
                            <div id="herramientas-detalles" class="mt-3">
                                <!-- Aquí se mostrarán los detalles de cada herramienta seleccionada -->
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        </div>
    </div>



    <div class="card shadow-sm my-5">
        <div class="card-header bg-secondary text-white">
            <h5 style="cursor: pointer;" data-bs-toggle="collapse" data-bs-target="#medicionesCollapse" aria-expanded="false" aria-controls="medicionesCollapse">
                Mediciones de Campo
            </h5>
        </div>
      
        <div id="medicionesCollapse" class="collapse">
            <div class="card-body">
                <div class="mt-3">
                    @if($instancia->valoresVariables->isEmpty())
                        <div class="alert alert-info">
                            No se registraron mediciones para esta muestra.
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Variable</th>
                                        <th>Valor</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($instancia->valoresVariables as $valorVariable)
                                        <tr>
                                            <td>{{ $valorVariable->variable }}</td>
                                            <td>{{ $valorVariable->valor }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>  
        </div>
    </div>



    <div class="card shadow-sm">
        <div class="card-header bg-secondary text-white">
            <h5 class="mb-0">Análisis Asociados</h5>
        </div>
        
        @if($analisis->isEmpty())
            <div class="card-body text-center py-5">
                <div class="empty-state">
                    <h3 class="text-muted">No hay análisis registrados</h3>
                    <p class="text-muted">Esta muestra no tiene análisis asociados.</p>
                </div>
            </div>
        @else
            <div class="card-body p-0">
                <div class="accordion" id="analisisAccordion">
                    @foreach($analisis as $item)
                        <div class="accordion-item border-0 mb-2">
                            <h2 class="accordion-header" id="heading{{ $item->cotio_subitem }}">
                                <button class="accordion-button collapsed bg-light" type="button" data-bs-toggle="collapse" 
                                    data-bs-target="#collapse{{ $item->cotio_subitem }}" aria-expanded="false" 
                                    aria-controls="collapse{{ $item->cotio_subitem }}">
                                    <div class="d-flex justify-content-between w-100 pe-3">
                                        <div>
                                            <span class="badge bg-primary me-2">#{{ $item->cotio_subitem }}</span>
                                            {{ $item->cotio_descripcion }}
                                        </div>
                                        <div>
                                            @php
                                                $estado = strtolower($item->cotio_estado_analisis ?? 'pendiente');
                                                $badgeClassAnalisis = match ($estado) {
                                                    'coordinado muestreo' => 'warning',
                                                    'pendiente' => 'warning',
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
                                            <span class="badge bg-{{ $badgeClassAnalisis }} me-2">
                                                {{ ucfirst($estado) }}
                                            </span>
                                            <span class="text-muted small">Resultado Final: {{ $item->resultado_final ?? 'N/A' }} </span>
                                        </div>
                                    </div>
                                </button>
                            </h2>
                            <div id="collapse{{ $item->cotio_subitem }}" class="accordion-collapse collapse" 
                                aria-labelledby="heading{{ $item->cotio_subitem }}" data-bs-parent="#analisisAccordion">
                                <div class="accordion-body pt-3">
                             
                                    <div class="d-flex justify-content-end mb-3">
                                        <button class="btn btn-sm btn-outline-primary me-2" data-bs-toggle="modal" 
                                            data-bs-target="#editAnalisisModal{{ $item->cotio_subitem }}">
                                            Agregar Resultado
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

   

   <!-- Modales para editar análisis -->
    @foreach($analisis as $item)
    <div class="modal fade" id="editAnalisisModal{{ $item->cotio_subitem }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Editar Análisis #{{ $item->cotio_descripcion }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>



                <div class="modal-body">
                    <form method="POST" action="{{ route('tareas.updateResultado', [
                        'cotio_numcoti' => $instancia->cotio_numcoti,
                        'cotio_item' => $instancia->cotio_item,
                        'cotio_subitem' => $item->cotio_subitem,
                        'instance' => $instanceNumber
                    ]) }}">
                        @csrf
                        @method('PUT')
                        

                        <div class="d-flex justify-content-between flex-md-row flex-column">
                            <div class="mb-3">
                                <div>
                                    <label for="resultado" class="form-label">Resultado</label>
                                    <input type="text" class="form-control" name="resultado" rows="4" value="{{ $item->resultado }}"
                                        placeholder="Ingrese los resultados del análisis"></input>
                                </div>

                                <div>
                                    <input type="text" class="form-control" name="observacion_resultado" rows="4" value="{{ $item->observacion_resultado }}"
                                        placeholder="Observación"></input>
                                </div>
                            </div>

                            <div class="mb-3">
                                <div>
                                    <label for="resultado_2" class="form-label">Resultado 2</label>
                                    <input type="text" class="form-control" name="resultado_2" rows="4" value="{{ $item->resultado_2 }}"
                                        placeholder="Ingrese los resultados del análisis"></input>
                                </div>

                                <div>
                                    <input type="text" class="form-control" name="observacion_resultado_2" rows="4" value="{{ $item->observacion_resultado_2 }}"
                                        placeholder="Observación"></input>
                                </div>
                            </div>


                            <div class="mb-3">
                                <div>
                                    <label for="resultado_3" class="form-label">Resultado 3</label>
                                    <input type="text" class="form-control" name="resultado_3" rows="4" value="{{ $item->resultado_3 }}"
                                        placeholder="Ingrese los resultados del análisis"></input>
                                </div>

                                <div>
                                    <input type="text" class="form-control" name="observacion_resultado_3" rows="4" value="{{ $item->observacion_resultado_3 }}"
                                        placeholder="Observación"></input>
                                </div>
                            </div>
                        </div>
          
                        <div class="mb-3">
                            <label for="resultado_final" class="form-label">Resultado Final</label>
                            <textarea class="form-control" name="resultado_final" rows="4" value="{{ $item->resultado_final }}"
                                placeholder="Ingrese los resultados del análisis">{{ $item->resultado_final ?? '' }}</textarea>
                        </div>
                        
                        <div class="mb-3">
                            <textarea class="form-control" name="observacion_resultado_final" rows="4"
                                placeholder="Observación">{{ $item->observacion_resultado_final ?? '' }}</textarea>
                        </div>

                        <div class="mb-3">
                            <x-heroicon-o-calendar style="width: 1rem; height: 1rem;" />
                            <small class="form-text text-muted">El sistema asignará la fecha actual automáticamente</small>
                        </div>
                        
                        <div class="d-flex justify-content-end">
                            <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Guardar Cambios
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

<style>
    .empty-state {
        max-width: 500px;
        margin: 0 auto;
        padding: 2rem;
    }
    .empty-state i {
        opacity: 0.6;
    }

    .select2-container--default .select2-selection--multiple {
        border: 1px solid #ced4da;
        padding: 0.375rem 0.75rem;
    }
</style>

<script>
// Manejo de guardado de resultados editables
document.addEventListener('DOMContentLoaded', function() {
    console.log('Script principal cargado');
    
    // Función para calcular promedio
    function calcularPromedio(form) {
        console.log('Calculando promedio para formulario:', form);
        
        const resultado1 = parseFloat(form.querySelector('[name="resultado"]').value) || 0;
        const resultado2 = parseFloat(form.querySelector('[name="resultado_2"]').value) || 0;
        const resultado3 = parseFloat(form.querySelector('[name="resultado_3"]').value) || 0;
        
        const valores = [resultado1, resultado2, resultado3].filter(v => v > 0);
        
        if (valores.length > 0) {
            const promedio = valores.reduce((a, b) => a + b, 0) / valores.length;
            form.querySelector('[name="resultado_final"]').value = promedio.toFixed(2);
            console.log('Promedio calculado:', promedio.toFixed(2));
        }
    }
    
    // Delegación de eventos para formularios dinámicos
    document.addEventListener('input', function(e) {
        const input = e.target;
        if (input.matches('[name="resultado"], [name="resultado_2"], [name="resultado_3"]')) {
            const form = input.closest('form');
            if (form) calcularPromedio(form);
        }
    });
    
    // Inicializar formularios existentes
    document.querySelectorAll('form').forEach(form => {
        if (form.action.includes('updateResultado')) {
            form.querySelectorAll('[name="resultado"], [name="resultado_2"], [name="resultado_3"]')
                .forEach(input => {
                    input.addEventListener('input', () => calcularPromedio(form));
                });
            calcularPromedio(form); // Calcular inicialmente
        }
    });
    
    // Manejar modales dinámicos
    document.addEventListener('shown.bs.modal', function(e) {
        const modal = e.target;
        const forms = modal.querySelectorAll('form');
        forms.forEach(form => {
            if (form.action.includes('updateResultado')) {
                form.querySelectorAll('[name="resultado"], [name="resultado_2"], [name="resultado_3"]')
                    .forEach(input => {
                        input.addEventListener('input', () => calcularPromedio(form));
                    });
                calcularPromedio(form);
            }
        });
    });
    
    // Función para inicializar los eventos de un formulario
    function inicializarFormulario(form) {
        console.log('Inicializando formulario:', form);
        
        // Buscar todos los inputs de resultado en el formulario
        const inputs = form.querySelectorAll('input[name="resultado"], input[name="resultado_2"], input[name="resultado_3"]');
        
        console.log('Inputs de resultado encontrados:', inputs.length);
        
        // Función para validar entrada numérica
        function validarNumerico(input) {
            if (input.value && isNaN(input.value)) {
                input.value = input.value.replace(/[^0-9.]/g, '');
                if (isNaN(input.value)) {
                    input.value = '';
                }
            }
        }
        
        // Agregar event listeners a cada input
        inputs.forEach(input => {
            console.log('Agregando event listeners a:', input.name, 'valor actual:', input.value);
            
            // Función que se ejecuta cuando cambia el input
            function onInputChange() {
                console.log('Input cambiado:', input.name, 'nuevo valor:', input.value);
                validarNumerico(input);
                calcularPromedio(form);
            }
            
            // Remover listeners existentes
            input.removeEventListener('input', onInputChange);
            input.removeEventListener('blur', onInputChange);
            input.removeEventListener('change', onInputChange);
            
            // Agregar nuevos listeners
            input.addEventListener('input', onInputChange);
            input.addEventListener('blur', onInputChange);
            input.addEventListener('change', onInputChange);
            
            // Agregar un listener de prueba para verificar que funciona
            input.addEventListener('keyup', function() {
                console.log('Keyup detectado en:', input.name, 'valor:', input.value);
            });
        });
        
        // Calcular promedio inicial si hay valores
        console.log('Calculando promedio inicial...');
        calcularPromedio(form);
        
        // Manejar el envío del formulario
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            console.log('Formulario enviado');
            
            // Recalcular por si acaso hay cambios no detectados
            calcularPromedio(form);
            
            // Obtener datos del formulario desde la URL del action
            const actionUrl = this.action;
            const urlParts = actionUrl.split('/');
            const cotioNumcoti = urlParts[urlParts.length - 5];
            const cotioItem = urlParts[urlParts.length - 4];
            const cotioSubitem = urlParts[urlParts.length - 3];
            const instance = urlParts[urlParts.length - 2];
            
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
            const allInputs = this.querySelectorAll('input, textarea');
            allInputs.forEach(input => {
                if (input.name && input.value !== undefined) {
                    formData.append(input.name, input.value);
                    console.log('Agregando campo:', input.name, 'valor:', input.value);
                }
            });
            
            const submitBtn = this.querySelector('button[type="submit"]');
            
            // Mostrar loader
            if (submitBtn) {
                const originalBtnText = submitBtn.innerHTML;
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
                
                // Cerrar el modal después de guardar exitosamente
                const modal = form.closest('.modal');
                if (modal) {
                    const modalInstance = bootstrap.Modal.getInstance(modal);
                    if (modalInstance) {
                        modalInstance.hide();
                    }
                }
                
                // Recargar la página después de un breve delay
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
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
    
    // Función para inicializar formularios cuando se abren los modales
    function inicializarFormulariosEnModal(modal) {
        console.log('Modal abierto:', modal.id);
        const forms = modal.querySelectorAll('form');
        console.log('Formularios encontrados en modal:', forms.length);
        
        forms.forEach(form => {
            if (form.action && form.action.includes('updateResultado')) {
                console.log('Inicializando formulario en modal:', form.action);
                inicializarFormulario(form);
            }
        });
    }
    
    // Inicializar formularios cuando se abren los modales
    document.querySelectorAll('.modal').forEach(modal => {
        console.log('Agregando event listener a modal:', modal.id);
        modal.addEventListener('shown.bs.modal', function() {
            console.log('Modal shown event disparado para:', this.id);
            inicializarFormulariosEnModal(this);
        });
    });
    
    // Inicializar todos los formularios de resultados en los modales (por si ya están cargados)
    console.log('Buscando formularios de resultados existentes...');
    document.querySelectorAll('.modal form').forEach(form => {
        console.log('Formulario encontrado:', form.action);
        if (form.action && form.action.includes('updateResultado')) {
            console.log('Inicializando formulario de resultados existente:', form.action);
            inicializarFormulario(form);
        }
    });
    
    // Función para mostrar alertas
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
});

async function enviarResultado(event, cotio_numcoti, cotio_item, cotio_subitem) {
    event.preventDefault();
    
    const form = event.target;
    const formData = new FormData(form);
    const data = Object.fromEntries(formData);
    
    try {
        const response = await fetch(`/tareas/${cotio_numcoti}/${cotio_item}/${cotio_subitem}/resultado`, {
            method: 'PUT',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        });

        const responseData = await response.json();

        if (response.ok) {
            Swal.fire({
                icon: 'success',
                title: '¡Éxito!',
                text: responseData.message,
                confirmButtonColor: '#3085d6'
            }).then(() => {
                window.location.reload();
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: responseData.message || 'Hubo un error al procesar la solicitud',
                confirmButtonColor: '#3085d6'
            });
        }
    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Error al procesar la solicitud',
            confirmButtonColor: '#3085d6'
        });
    }
}
</script>


<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('suspensionForm');
        const textarea = document.getElementById('cotio_observaciones_suspension');
        
        form.addEventListener('submit', function(event) {
            if (!textarea.value.trim()) {
                event.preventDefault();
                event.stopPropagation();
                textarea.classList.add('is-invalid');
            } else {
                textarea.classList.remove('is-invalid');
            }
            
            form.classList.add('was-validated');
        });
        
        document.getElementById('suspenderModal').addEventListener('hidden.bs.modal', function() {
            form.classList.remove('was-validated');
            textarea.classList.remove('is-invalid');
        });
    });


    document.addEventListener('DOMContentLoaded', function() {
        const imageInput = document.getElementById('image');
        if (imageInput) {
            imageInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(event) {
                        const previewContainer = document.createElement('div');
                        previewContainer.className = 'mt-2';
                        previewContainer.innerHTML = `
                            <img src="${event.target.result}" alt="Vista previa" class="img-thumbnail" style="max-height: 150px;">
                        `;
                        
                        const existingPreview = imageInput.nextElementSibling.querySelector('.mt-2');
                        if (existingPreview) {
                            existingPreview.replaceWith(previewContainer);
                        } else {
                            imageInput.insertAdjacentElement('afterend', previewContainer);
                        }
                    };
                    reader.readAsDataURL(file);
                }
            });
        }


    const form = document.querySelector('#editMuestraModal form');
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Mostrar en consola para depuración
                console.log('Formulario enviado');
                console.log(new FormData(form));
                
                // Enviar el formulario manualmente
                fetch(form.action, {
                    method: 'POST',
                    body: new FormData(form),
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': form.querySelector('input[name="_token"]').value
                    }
                })
                .then(response => {
                    console.log('Respuesta recibida', response);
                    if (response.redirected) {
                        window.location.href = response.url;
                    } else {
                        return response.json();
                    }
                })
                .then(data => {
                    if (data) {
                        console.log('Datos recibidos', data);
                        // Recargar la página si es exitoso
                        window.location.reload();
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
            });
        }
    });
</script>



<script>
    $(document).ready(function() {
        // Inicializar select2
        $('.select2-herramientas').select2({
            placeholder: "Seleccione herramientas",
            allowClear: true
        });
    
        // Mostrar detalles de herramientas seleccionadas
        $('.select2-herramientas').on('change', function() {
            const selectedIds = $(this).val() || [];
            const detallesContainer = $('#herramientas-detalles');
            detallesContainer.empty();
    
            selectedIds.forEach(id => {
                const option = $(this).find(`option[value="${id}"]`);
                const cantidad = option.data('cantidad') || 1;
                const observaciones = option.data('observaciones') || '';
    
                detallesContainer.append(`
                    <div class="card mb-2 herramienta-detail" data-id="${id}">
                        <div class="card-body">
                            <h6>${option.text()}</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <label class="form-label">Cantidad</label>
                                    <input type="number" name="cantidades[${id}]" class="form-control" value="${cantidad}" min="1">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Observaciones</label>
                                    <input type="text" name="observaciones[${id}]" class="form-control" value="${observaciones}">
                                </div>
                            </div>
                        </div>
                    </div>
                `);
            });
        });
    
        // Disparar evento change al abrir el modal para mostrar las herramientas ya seleccionadas
        $('#editHerramientasModal').on('shown.bs.modal', function() {
            $('.select2-herramientas').trigger('change');
        });
    });
</script>

@endsection












