@extends('layouts.app')

@section('content')
<div class="container py-4">
    <!-- Encabezado -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0">
            Detalle de Muestra
            @if($instanceNumber)
                <span class="fs-5 text-muted">(Muestra {{ $instancia->id ? '#' . str_pad($instancia->id, 8, '0', STR_PAD_LEFT) : null }})</span>
            @endif
        </h1>
        <a href="{{ url()->previous() }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Volver
        </a>
    </div>

    <!-- Mensaje de éxito -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Detalles de la muestra -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                {{ $instancia->cotio_descripcion }}
                @php
                    $estado = strtolower($instancia->cotio_estado);
                    $badgeClass = match ($estado) {
                        'pendiente', 'coordinado muestreo', 'coordinado analisis' => 'warning',
                        'en proceso', 'en revision muestreo', 'en revision analisis' => 'info',
                        'finalizado', 'muestreado', 'analizado' => 'success',
                        'suspension' => 'danger',
                        default => 'secondary',
                    };
                @endphp
            <span class="badge bg-{{ $badgeClass }} ms-2">
                {{ ucfirst($instancia->cotio_estado) }}
            </span>
            </h5>
            @if(Auth::user()->rol != 'laboratorio')
                <div class="btn-group botones-muestra" role="group">
                    @if($instancia->cotio_estado != 'suspension')
                        <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#suspenderModal">
                            <i class="fas fa-pause me-1"></i> Suspender
                        </button>
                    @else
                        <button type="button" class="btn btn-sm btn-secondary" disabled>
                            <i class="fas fa-pause me-1"></i> Ya suspendida
                        </button>
                    @endif
                    @if($instancia->cotio_estado == 'coordinado muestreo' || $instancia->cotio_estado == 'en revision muestreo')
                        <button class="btn btn-sm btn-outline-light" data-bs-toggle="modal" data-bs-target="#editMuestraModal">
                            Añadir identificación
                        </button>
                    @endif
                </div>
            @endif
        </div>

        <div class="card-body">
            <div class="row gy-3">
                <div class="col-md-4">
                    <p><strong>Cotización:</strong> {{ $instancia->cotio_numcoti }}</p>
                    <p><strong>Identificación:</strong> {{ $instancia->cotio_identificacion ?? 'N/A' }}</p>
                </div>
                <div class="col-md-4">
                    <p><strong>Fecha Inicio:</strong> 
                        {{ $instancia->fecha_inicio_muestreo ? \Carbon\Carbon::parse($instancia->fecha_inicio_muestreo)->format('d/m/Y') : 'N/A' }}
                    </p>
                    <p><strong>Fecha Fin:</strong> 
                        {{ $instancia->fecha_fin_muestreo ? \Carbon\Carbon::parse($instancia->fecha_fin_muestreo)->format('d/m/Y') : 'N/A' }}
                    </p>
                </div>
                @if(Auth::user()->rol != 'laboratorio')
                    <div class="col-md-4">
                        <p><strong>Vehículo:</strong> 
                            @if($instancia->vehiculo)
                                {{ $instancia->vehiculo->marca }} {{ $instancia->vehiculo->modelo }} ({{ $instancia->vehiculo->patente }})
                            @else
                                N/A
                            @endif
                        </p>
                    </div>
                @endif
                <div class="col-md-4">
                    @if($instancia->image)
                        <img src="{{ Storage::url('images/' . $instancia->image) }}" alt="Imagen de la muestra" class="img-fluid w-50 rounded">
                    @else
                        <p class="text-muted">No hay imagen disponible</p>
                    @endif
                </div>
            </div>

            <!-- Herramientas asignadas -->
            @if($instancia->herramientas->count() > 0)
                <div class="mt-4 pt-3 border-top">
                    <h6>Herramientas Asignadas</h6>
                    <div class="row gy-2">
                        @foreach($instancia->herramientas as $herramienta)
                            <div class="col-md-4">
                                <div class="card border h-100">
                                    <div class="card-body p-2">
                                        <h6 class="mb-1">{{ $herramienta->equipamiento }}</h6>
                                        <p class="small mb-0 text-muted">{{ $herramienta->marca_modelo }}</p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if($instancia->latitud && $instancia->longitud)
                <div class="mt-3">
                    <a href="https://www.google.com/maps?q={{ $instancia->latitud }},{{ $instancia->longitud }}&z=15&t=m" 
                        target="_blank"
                        class="btn btn-sm btn-outline-primary">
                         <x-heroicon-o-map style="width: 14px; height: 14px;" class="me-1"/>
                         Ver en Google Maps
                     </a>
                </div>
            @endif
        </div>
    </div>




    <div class="card shadow-sm my-5">
        <div class="card-header bg-secondary text-white">
            <h5 style="cursor: pointer;" data-bs-toggle="collapse" data-bs-target="#medicionesCollapse" aria-expanded="false" aria-controls="medicionesCollapse">
                Mediciones campo
            </h5>
        </div>
      
        <div id="medicionesCollapse" class="collapse">
            <form class="card-body" action="{{ route('tareas.updateMediciones', $instancia->id) }}" 
                  method="POST">
                @csrf
                @method('PUT')
                <div class="mt-3">
                    @if($instancia->valoresVariables->isEmpty())
                        <div class="alert alert-info">
                            No hay variables asignadas para este análisis.
                        </div>
                    @else
                        @foreach($instancia->valoresVariables as $valorVariable)
                            <div class="mb-3">
                                <label for="variable_{{ $valorVariable->id }}" class="form-label">
                                    {{ $valorVariable->variable }}
                                </label>
                                <input type="text" 
                                       class="form-control" 
                                       name="valores[{{ $valorVariable->id }}][valor]" 
                                       id="variable_{{ $valorVariable->id }}" 
                                       value="{{ $valorVariable->valor ?? '' }}" 
                                       placeholder="Ingrese el valor para {{ $valorVariable->variable }}"
                                       @if($instancia->cotio_estado == 'muestreado') readonly @endif>
                                <input type="hidden" 
                                       name="valores[{{ $valorVariable->id }}][variable_id]" 
                                       value="{{ $valorVariable->id }}">
                            </div>
                        @endforeach

                        <div style="background-color: #DECB72; padding: 10px; border-radius: 5px; margin-top: 50px; margin-bottom: 30px;">
                            <label for="observaciones_medicion_coord_muestreo" class="form-label">
                                Observaciones del coordinador:
                            </label>
                            <textarea class="form-control" id="observaciones_muestreador" rows="3" readonly
                                style="background-color: #fff8e1; border-left: 4px solid #ffc107; padding-left: 12px;">
                                {{ trim($instancia->observaciones_medicion_coord_muestreo) ?? '' }}
                            </textarea>
                        </div>

                        <div class="mb-3">
                            <label for="observaciones_medicion_muestreador" class="form-label">
                                Observaciones del muestreador:
                            </label>
                            <textarea class="form-control" 
                                name="observaciones_medicion_muestreador" 
                                id="observaciones_medicion_muestreador" 
                                rows="3"
                                placeholder="Ingrese las observaciones del muestreador"
                                @if($instancia->cotio_estado == 'muestreado') readonly @endif
                            >{{ trim($instancia->observaciones_medicion_muestreador ?? '') }}</textarea>
                        </div>
        
                        @if($instancia->cotio_estado != 'muestreado')
                            <div class="d-flex justify-content-end mt-3">
                                <button type="submit" class="btn btn-primary">
                                    Guardar
                                </button>
                            </div>
                        @endif
                    @endif
                </div>
            </form>  
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
                                                $estado = strtolower($item->cotio_estado ?? 'pendiente');
                                            @endphp
                                            <span class="badge bg-{{ 
                                                $estado === 'finalizado' ? 'success' : 
                                                ($estado === 'en revision muestreo' ? 'info' : 'warning') 
                                            }} me-2">
                                                {{ ucfirst($estado) }}
                                            </span>
                                            <span class="text-muted small">Resultado: {{ $item->resultado ?? 'N/A' }}</span>
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

    <div class="modal fade" id="editMuestraModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Editar Muestra</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <form method="POST" action="{{ route('asignar.identificacion-muestra') }}" id="muestraForm" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="cotio_numcoti" value="{{ $instancia->cotio_numcoti }}">
                        <input type="hidden" name="cotio_item" value="{{ $instancia->cotio_item }}">
                        <input type="hidden" name="instance_number" value="{{ $instanceNumber }}">
                        
                        <div class="mb-3">
                            <label for="cotio_identificacion" class="form-label">Identificación</label>
                            <input type="text" class="form-control" id="cotio_identificacion" 
                                   name="cotio_identificacion" value="{{ $instancia->cotio_identificacion ?? '' }}"
                                   placeholder="Ingrese la identificación de la muestra">
                        </div>
                    
                        <div class="mb-3">
                            <label class="form-label">Imagen de la Muestra</label>
                            <div class="d-flex gap-2 mb-2">
                                <button type="button" class="btn btn-primary" id="captureBtn">
                                    <i class="fas fa-camera me-1"></i> Tomar Foto
                                </button>
                                <button type="button" class="btn btn-secondary" id="selectBtn">
                                    <i class="fas fa-image me-1"></i> Seleccionar de Galería
                                </button>
                            </div>
                            <input type="hidden" name="image_base64" id="image_base64">
                            <input type="file" class="d-none" id="imageInput" accept="image/*" capture="environment">
                            <input type="file" class="d-none" id="galleryInput" accept="image/*">
                            
                            <div id="imagePreview" class="mt-2">
                                @if($instancia->image)
                                    <img src="{{ asset('storage/images/'.$instancia->image) }}" width="100" class="img-thumbnail">
                                    <div class="form-check mt-2">
                                        <input class="form-check-input" type="checkbox" id="remove_image" name="remove_image" value="1">
                                        <label class="form-check-label" for="remove_image">Eliminar imagen actual</label>
                                    </div>
                                @endif
                            </div>
                        </div>
                    
                        <div class="mb-3">
                            <label class="form-label">Georeferencia</label>
                            <div id="map" style="height: 300px; width: 100%;"></div>
                            <input type="hidden" id="latitud" name="latitud" value="{{ $instancia->latitud ?? '' }}">
                            <input type="hidden" id="longitud" name="longitud" value="{{ $instancia->longitud ?? '' }}">
                            <div class="input-group mt-2">
                                <span class="input-group-text">Latitud</span>
                                <input type="number" step="any" class="form-control" id="latitude-display" value="{{ $instancia->latitud ?? '' }}">
                                <span class="input-group-text">Longitud</span>
                                <input type="number" step="any" class="form-control" id="longitude-display" value="{{ $instancia->longitud ?? '' }}">
                            </div>
                            <small class="text-muted">Haz clic en el mapa para seleccionar la ubicación o edita los valores de latitud y longitud manualmente.</small>
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

    <!-- Modal para suspender muestra -->
    <div class="modal fade" id="suspenderModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Suspender Muestra</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>¿Estás seguro de suspender esta muestra?</p>
                    <form id="suspenderForm" method="POST" action="{{ route('asignar.suspension-muestra', [
                        'cotio_numcoti' => $instancia->cotio_numcoti,
                        'cotio_item' => $instancia->cotio_item,
                        'instance_number' => $instanceNumber
                    ]) }}">
                        @csrf
                        
                        <div class="mb-3">
                            <label for="observacion" class="form-label">Observación de suspensión</label>
                            <input type="text" class="form-control" id="observacion" 
                                   name="cotio_observaciones_suspension" 
                                   placeholder="Ingrese la razón de la suspensión" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger" form="suspenderForm">Suspender</button>
                </div>
            </div>
        </div>
    </div>

   <!-- Modales para editar análisis -->
   @foreach($analisis as $item)
    <div class="modal fade" id="editAnalisisModal{{ $item->cotio_subitem }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Editar Análisis #{{ $item->cotio_subitem }}: {{ $item->cotio_descripcion }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" action="{{ route('tareas.updateResultado', [
                        'cotio_numcoti' => $instancia->cotio_numcoti,
                        'cotio_item' => $instancia->cotio_item,
                        'cotio_subitem' => $item->cotio_subitem,
                        'instance' => $instanceNumber
                    ]) }}" id="editAnalisisForm{{ $item->cotio_subitem }}">
                        @csrf
                        @method('PUT')

                        <!-- Tabs Navigation -->
                        <ul class="nav nav-tabs" id="analisisTabs{{ $item->cotio_subitem }}" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="resultado-tab{{ $item->cotio_subitem }}" data-bs-toggle="tab" 
                                        data-bs-target="#resultado{{ $item->cotio_subitem }}" type="button" role="tab" 
                                        aria-controls="resultado{{ $item->cotio_subitem }}" aria-selected="true">
                                    Resultado
                                </button>
                            </li>
                        </ul>

                        <div class="tab-content" id="analisisTabsContent{{ $item->cotio_subitem }}">
                            <!-- Resultado Tab -->
                            <div class="tab-pane fade show active" id="resultado{{ $item->cotio_subitem }}" role="tabpanel" 
                                    aria-labelledby="resultado-tab{{ $item->cotio_subitem }}">
                                <div class="mb-3 mt-3">
                                    <textarea class="form-control" name="resultado" id="resultado{{ $item->cotio_subitem }}" rows="4"
                                                placeholder="Ingrese los resultados del análisis">{{ $item->resultado ?? '' }}</textarea>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end mt-3">
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

    @media (max-width: 480px) {
        .botones-muestra {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 10px;	
        }

        .botones-muestra button {
            width: 100%;
        }
    }   
</style>

<script>
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
        const captureBtn = document.getElementById('captureBtn');
        const selectBtn = document.getElementById('selectBtn');
        const imageInput = document.getElementById('imageInput');
        const galleryInput = document.getElementById('galleryInput');
        const imageBase64 = document.getElementById('image_base64');
        const imagePreview = document.getElementById('imagePreview');

        // Función para procesar la imagen
        function processImage(file) {
            return new Promise((resolve, reject) => {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const img = new Image();
                    img.onload = function() {
                        // Crear un canvas para redimensionar
                        const canvas = document.createElement('canvas');
                        const MAX_WIDTH = 800;
                        const MAX_HEIGHT = 800;
                        let width = img.width;
                        let height = img.height;

                        if (width > height) {
                            if (width > MAX_WIDTH) {
                                height *= MAX_WIDTH / width;
                                width = MAX_WIDTH;
                            }
                        } else {
                            if (height > MAX_HEIGHT) {
                                width *= MAX_HEIGHT / height;
                                height = MAX_HEIGHT;
                            }
                        }

                        canvas.width = width;
                        canvas.height = height;
                        const ctx = canvas.getContext('2d');
                        ctx.drawImage(img, 0, 0, width, height);

                        // Convertir a base64 con calidad reducida
                        const base64 = canvas.toDataURL('image/jpeg', 0.7);
                        resolve(base64);
                    };
                    img.onerror = reject;
                    img.src = e.target.result;
                };
                reader.onerror = reject;
                reader.readAsDataURL(file);
            });
        }

        // Función para mostrar la vista previa
        function showPreview(base64) {
            imagePreview.innerHTML = `
                <img src="${base64}" class="img-thumbnail" style="max-height: 200px;">
            `;
        }

        // Manejar captura de foto
        captureBtn.addEventListener('click', () => {
            imageInput.click();
        });

        // Manejar selección de galería
        selectBtn.addEventListener('click', () => {
            galleryInput.click();
        });

        // Procesar imagen de la cámara
        imageInput.addEventListener('change', async (e) => {
            const file = e.target.files[0];
            if (file) {
                try {
                    const base64 = await processImage(file);
                    imageBase64.value = base64;
                    showPreview(base64);
                } catch (error) {
                    console.error('Error al procesar la imagen:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'No se pudo procesar la imagen'
                    });
                }
            }
        });

        // Procesar imagen de la galería
        galleryInput.addEventListener('change', async (e) => {
            const file = e.target.files[0];
            if (file) {
                try {
                    const base64 = await processImage(file);
                    imageBase64.value = base64;
                    showPreview(base64);
                } catch (error) {
                    console.error('Error al procesar la imagen:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'No se pudo procesar la imagen'
                    });
                }
            }
        });
    });
</script>



<script>
    const form = document.querySelector('#editMuestraModal form');
        if (form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Mostrar loader mientras se procesa
                Swal.fire({
                    title: 'Procesando...',
                    html: 'Guardando los datos de la muestra',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
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
                    if (response.redirected) {
                        return { redirected: true, url: response.url };
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.redirected) {
                        window.location.href = data.url;
                        return;
                    }
                    
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: '¡Éxito!',
                            text: data.message || 'Datos guardados correctamente',
                            confirmButtonText: 'Aceptar'
                        }).then(() => {
                            // Recargar la página o cerrar el modal
                            window.location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: data.message || 'Ocurrió un error al guardar',
                            confirmButtonText: 'Entendido'
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Error en la conexión con el servidor',
                        confirmButtonText: 'Entendido'
                    });
                });
            });
    }

document.addEventListener('DOMContentLoaded', function() {
    let initialLat = -33.45694;
    let initialLng = -70.64827;
    let zoomLevel = 13;
    
    // Si hay coordenadas existentes, usarlas
    @if($instancia->latitud && $instancia->longitud)
        initialLat = parseFloat({{ $instancia->latitud }});
        initialLng = parseFloat({{ $instancia->longitud }});
        zoomLevel = 15;
    @endif
    
    // Inicializar el mapa
    const map = new google.maps.Map(document.getElementById('map'), {
        center: { lat: initialLat, lng: initialLng },
        zoom: zoomLevel,
        streetViewControl: false,
        mapTypeControlOptions: {
            mapTypeIds: ['roadmap', 'satellite']
        }
    });
    
    let marker;
    
    // Función para actualizar los campos de entrada
    function updateInputs(latLng) {
        const lat = typeof latLng.lat === 'function' ? latLng.lat() : latLng.lat;
        const lng = typeof latLng.lng === 'function' ? latLng.lng() : latLng.lng;
        
        document.getElementById('latitud').value = lat;
        document.getElementById('longitud').value = lng;
        document.getElementById('latitude-display').value = lat.toFixed(6);
        document.getElementById('longitude-display').value = lng.toFixed(6);
    }
    
    // Función para actualizar la posición del marcador
    function updateMarkerPosition(lat, lng) {
        const newPos = { lat: parseFloat(lat), lng: parseFloat(lng) };
        if (isNaN(newPos.lat) || isNaN(newPos.lng)) {
            Swal.fire({
                icon: 'error',
                title: 'Coordenadas inválidas',
                text: 'Por favor, ingresa valores válidos para latitud y longitud.'
            });
            return false;
        }
        if (marker) {
            marker.setPosition(newPos);
        } else {
            marker = new google.maps.Marker({
                position: newPos,
                map: map,
                draggable: true,
                icon: {
                    url: "https://maps.google.com/mapfiles/ms/icons/red-dot.png",
                    scaledSize: new google.maps.Size(32, 32)
                }
            });
            // Manejador de arrastre del marcador
            google.maps.event.addListener(marker, 'dragend', function() {
                updateInputs(marker.getPosition());
            });
        }
        map.setCenter(newPos);
        return true;
    }
    
    // Si hay coordenadas iniciales, colocar marcador
    @if($instancia->latitud && $instancia->longitud)
        marker = new google.maps.Marker({
            position: { lat: initialLat, lng: initialLng },
            map: map,
            draggable: true,
            icon: {
                url: "https://maps.google.com/mapfiles/ms/icons/red-dot.png",
                scaledSize: new google.maps.Size(32, 32)
            }
        });
        updateInputs({ lat: initialLat, lng: initialLng });
        
        // Manejador de arrastre del marcador
        google.maps.event.addListener(marker, 'dragend', function() {
            updateInputs(marker.getPosition());
        });
    @endif
    
    // Manejador de clics en el mapa
    map.addListener('click', function(e) {
        if (marker) {
            marker.setPosition(e.latLng);
        } else {
            marker = new google.maps.Marker({
                position: e.latLng,
                map: map,
                draggable: true,
                icon: {
                    url: "https://maps.google.com/mapfiles/ms/icons/red-dot.png",
                    scaledSize: new google.maps.Size(32, 32)
                }
            });
            
            // Manejador de arrastre del marcador
            google.maps.event.addListener(marker, 'dragend', function() {
                updateInputs(marker.getPosition());
            });
        }
        updateInputs(e.latLng);
    });
    
    // Manejador de cambios en los inputs de latitud y longitud
    document.getElementById('latitude-display').addEventListener('change', function() {
        const lat = this.value;
        const lng = document.getElementById('longitude-display').value;
        if (updateMarkerPosition(lat, lng)) {
            updateInputs({ lat: parseFloat(lat), lng: parseFloat(lng) });
        }
    });
    
    document.getElementById('longitude-display').addEventListener('change', function() {
        const lat = document.getElementById('latitude-display').value;
        const lng = this.value;
        if (updateMarkerPosition(lat, lng)) {
            updateInputs({ lat: parseFloat(lat), lng: parseFloat(lng) });
        }
    });
    
    // Geolocalización con SweetAlert
    if (navigator.geolocation) {
        const locateBtn = document.createElement('button');
        locateBtn.textContent = 'Usar mi ubicación actual';
        locateBtn.className = 'btn btn-sm btn-info mb-2';
        locateBtn.type = 'button';
        locateBtn.onclick = function() {
            Swal.fire({
                title: 'Obteniendo ubicación...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    Swal.close();
                    const pos = {
                        lat: position.coords.latitude,
                        lng: position.coords.longitude
                    };
                    if (marker) {
                        marker.setPosition(pos);
                    } else {
                        marker = new google.maps.Marker({
                            position: pos,
                            map: map,
                            draggable: true,
                            icon: {
                                url: "https://maps.google.com/mapfiles/ms/icons/red-dot.png",
                                scaledSize: new google.maps.Size(32, 32)
                            }
                        });
                        
                        // Manejador de arrastre del marcador
                        google.maps.event.addListener(marker, 'dragend', function() {
                            updateInputs(marker.getPosition());
                        });
                    }
                    map.setCenter(pos);
                    map.setZoom(15);
                    updateInputs(pos);
                }, 
                function(error) {
                    Swal.close();
                    Swal.fire({
                        icon: 'error',
                        title: 'Error de geolocalización',
                        text: getGeoLocationError(error),
                        confirmButtonText: 'Entendido'
                    });
                }
            );
        };
        document.querySelector('#map').parentNode.insertBefore(locateBtn, document.querySelector('#map'));
    }
    
    // Función para mensajes de error de geolocalización
    function getGeoLocationError(error) {
        switch(error.code) {
            case error.PERMISSION_DENIED:
                return "Se denegó el permiso para obtener la ubicación.";
            case error.POSITION_UNAVAILABLE:
                return "La información de ubicación no está disponible.";
            case error.TIMEOUT:
                return "La solicitud de ubicación tardó demasiado tiempo.";
            case error.UNKNOWN_ERROR:
                return "Ocurrió un error desconocido.";
            default:
                return "Error al obtener la ubicación.";
        }
    }

    // Manejar el envío del formulario
    document.getElementById('muestraForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        const file = fileInput.files[0];

        if (!file) {
            Swal.fire({
                icon: 'error',
                title: 'No se seleccionó una imagen',
                text: 'Por favor seleccioná o tomá una foto para subir.',
            });
            return;
        }

        // Mostrar loader
        Swal.fire({
            title: 'Guardando cambios...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        fetch(this.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(async response => {
            const isJson = response.headers.get("content-type")?.includes("application/json");
            const data = isJson ? await response.json() : { success: false, message: 'Respuesta inesperada del servidor', debug: await response.text() };

            Swal.close();

            if (data.success) {
                // Redirección o éxito silencioso
                // Swal.fire({ icon: 'success', title: 'Éxito', text: data.message });
                // window.location.href = data.redirect;
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error al guardar',
                    html: `
                        <p><strong>Mensaje:</strong> ${data.message || 'Ocurrió un error.'}</p>
                        <pre style="white-space:pre-wrap;text-align:left;font-size:12px;background:#f5f5f5;padding:10px;border-radius:5px;">
        ${data.debug ? data.debug : JSON.stringify(data, null, 2)}
                        </pre>
                    `,
                    confirmButtonText: 'OK'
                });
            }
        })
        .catch(error => {
            Swal.close();
            Swal.fire({
                icon: 'error',
                title: 'Error de red',
                html: `
                    <p>No se pudo completar la solicitud.</p>
                    <pre style="white-space:pre-wrap;text-align:left;font-size:12px;background:#f5f5f5;padding:10px;border-radius:5px;">
        ${JSON.stringify(error, Object.getOwnPropertyNames(error), 2)}
                    </pre>
                `,
                confirmButtonText: 'OK'
            });
        });
    });
});

document.addEventListener('DOMContentLoaded', function() {
    const textarea = document.getElementById('observaciones_muestreador');
    const textarea2 = document.getElementById('observaciones_medicion_muestreador');
    if (textarea) {
        textarea.value = textarea.value.trim();
    }
    if (textarea2) {
        textarea2.value = textarea2.value.trim();
    }
});

</script>


@endsection












