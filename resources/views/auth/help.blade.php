@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card border-0 shadow-lg">
                <div class="card-header bg-gradient-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="mb-0"><i class="fas fa-life-ring me-2"></i> Centro de Ayuda</h3>
                        <span class="badge bg-white text-primary rounded-pill fs-6">Usuario: {{ $user->usu_codigo }}</span>
                    </div>
                </div>
                
                <div class="card-body p-4">
                    <div class="row g-4">
                        <!-- Banner de Bienvenida -->
                        <div class="col-12">
                            <div class="alert alert-primary bg-primary bg-opacity-10 border-0">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <i class="fas fa-info-circle fs-2 text-primary"></i>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h5 class="alert-heading">¡Bienvenido al Centro de Ayuda!</h5>
                                        <p class="mb-0">Aquí encontrarás toda la información y recursos necesarios para sacar el máximo provecho a nuestra plataforma.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Preguntas Frecuentes -->
                        <div class="col-lg-6">
                            <div class="card h-100 border-0 shadow-sm">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0"><i class="fas fa-question-circle me-2 text-primary"></i> Preguntas Frecuentes</h5>
                                </div>
                                <div class="card-body">
                                    <div class="accordion accordion-flush" id="faqAccordion">
                                        <div class="accordion-item border-0 mb-2 shadow-sm rounded">
                                            <h2 class="accordion-header" id="headingOne">
                                                <button class="accordion-button collapsed rounded" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne">
                                                    <i class="fas fa-key me-2 text-primary"></i> ¿Cómo cambio mi contraseña?
                                                </button>
                                            </h2>
                                            <div id="collapseOne" class="accordion-collapse collapse" aria-labelledby="headingOne" data-bs-parent="#faqAccordion">
                                                <div class="accordion-body">
                                                    <p>Para cambiar tu contraseña:</p>
                                                    <ol>
                                                        <li>Ve a tu perfil de usuario</li>
                                                        <li>Selecciona "Seguridad y Contraseña"</li>
                                                        <li>Ingresa tu contraseña actual</li>
                                                        <li>Crea y confirma tu nueva contraseña</li>
                                                        <li>Guarda los cambios</li>
                                                    </ol>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="accordion-item border-0 mb-2 shadow-sm rounded">
                                            <h2 class="accordion-header" id="headingTwo">
                                                <button class="accordion-button collapsed rounded" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo">
                                                    <i class="fas fa-user-lock me-2 text-primary"></i> ¿Qué es la autenticación 2FA?
                                                </button>
                                            </h2>
                                            <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#faqAccordion">
                                                <div class="accordion-body">
                                                    <p>La autenticación de dos factores (2FA) añade una capa adicional de seguridad a tu cuenta. Requiere:</p>
                                                    <ul>
                                                        <li>Algo que sabes (tu contraseña)</li>
                                                        <li>Algo que tienes (un código temporal)</li>
                                                    </ul>
                                                    <p>Puedes activarla en la sección de Seguridad de tu perfil.</p>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="accordion-item border-0 mb-2 shadow-sm rounded">
                                            <h2 class="accordion-header" id="headingThree">
                                                <button class="accordion-button collapsed rounded" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree">
                                                    <i class="fas fa-file-export me-2 text-primary"></i> ¿Cómo exporto mis datos?
                                                </button>
                                            </h2>
                                            <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#faqAccordion">
                                                <div class="accordion-body">
                                                    <p>Para exportar tus datos:</p>
                                                    <ol>
                                                        <li>Ve a Configuración de cuenta</li>
                                                        <li>Selecciona "Exportar datos"</li>
                                                        <li>Elige el formato (CSV, Excel o PDF)</li>
                                                        <li>Haz clic en "Generar exportación"</li>
                                                        <li>Descarga el archivo cuando esté listo</li>
                                                    </ol>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Contactar al Soporte -->
                        <div class="col-lg-6">
                            <div class="card h-100 border-0 shadow-sm">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0"><i class="fas fa-headset me-2 text-primary"></i> Soporte Directo</h5>
                                </div>
                                <div class="card-body">
                                    <form class="needs-validation" novalidate>
                                        <div class="mb-3">
                                            <label class="form-label">Asunto</label>
                                            <select class="form-select" required>
                                                <option value="" selected disabled>Seleccione un tema</option>
                                                <option>Problema técnico</option>
                                                <option>Consulta sobre funcionalidades</option>
                                                <option>Solicitud de característica</option>
                                                <option>Reporte de error</option>
                                                <option>Otro</option>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Mensaje</label>
                                            <textarea class="form-control" rows="5" placeholder="Describa su consulta o problema en detalle..." required></textarea>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Adjuntos (opcional)</label>
                                            <div class="file-upload-wrapper">
                                                <input type="file" class="form-control" id="supportAttachments" multiple>
                                                <div class="file-upload-preview mt-2"></div>
                                            </div>
                                        </div>
                                        <button type="submit" class="btn btn-primary w-100 py-2">
                                            <i class="fas fa-paper-plane me-2"></i> Enviar Mensaje
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Recursos Adicionales -->
                        <div class="col-12">
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0"><i class="fas fa-box-open me-2 text-primary"></i> Recursos Adicionales</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row g-4">
                                        <div class="col-md-4">
                                            <div class="card h-100 border-0 shadow-sm hover-shadow transition-all">
                                                <div class="card-body text-center p-4">
                                                    <div class="bg-primary bg-opacity-10 p-3 rounded-circle d-inline-block mb-3">
                                                        <i class="fas fa-file-pdf text-primary fs-3"></i>
                                                    </div>
                                                    <h5 class="card-title">Manual de Usuario</h5>
                                                    <p class="card-text small text-muted">Guía completa con todas las funcionalidades del sistema</p>
                                                    <a href="#" class="btn btn-sm btn-outline-primary stretched-link">
                                                        <i class="fas fa-download me-1"></i> Descargar PDF
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="card h-100 border-0 shadow-sm hover-shadow transition-all">
                                                <div class="card-body text-center p-4">
                                                    <div class="bg-primary bg-opacity-10 p-3 rounded-circle d-inline-block mb-3">
                                                        <i class="fas fa-video text-primary fs-3"></i>
                                                    </div>
                                                    <h5 class="card-title">Tutoriales en Video</h5>
                                                    <p class="card-text small text-muted">Aprende con nuestros videos paso a paso</p>
                                                    <a href="#" class="btn btn-sm btn-outline-primary stretched-link">
                                                        <i class="fas fa-play me-1"></i> Ver Tutoriales
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="card h-100 border-0 shadow-sm hover-shadow transition-all">
                                                <div class="card-body text-center p-4">
                                                    <div class="bg-primary bg-opacity-10 p-3 rounded-circle d-inline-block mb-3">
                                                        <i class="fas fa-comments text-primary fs-3"></i>
                                                    </div>
                                                    <h5 class="card-title">Comunidad</h5>
                                                    <p class="card-text small text-muted">Únete a nuestra comunidad de usuarios</p>
                                                    <a href="#" class="btn btn-sm btn-outline-primary stretched-link">
                                                        <i class="fas fa-users me-1"></i> Acceder
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .accordion-button:not(.collapsed) {
        background-color: rgba(13, 110, 253, 0.1);
        color: #0d6efd;
    }
    .accordion-button:focus {
        box-shadow: none;
        border-color: rgba(0,0,0,.125);
    }
    .file-upload-wrapper {
        position: relative;
    }
    .file-upload-preview {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }
    .file-upload-preview .file-preview-item {
        background: #f8f9fa;
        border-radius: 5px;
        padding: 5px 10px;
        font-size: 0.8rem;
        display: flex;
        align-items: center;
    }
    .file-upload-preview .file-preview-item .remove-file {
        margin-left: 5px;
        cursor: pointer;
        color: #dc3545;
    }
    .hover-shadow:hover {
        box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.1) !important;
        transform: translateY(-2px);
    }
    .transition-all {
        transition: all 0.3s ease;
    }
</style>

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // File upload preview
        const fileInput = document.getElementById('supportAttachments');
        const previewDiv = document.querySelector('.file-upload-preview');
        
        fileInput.addEventListener('change', function() {
            previewDiv.innerHTML = '';
            if (this.files.length > 0) {
                Array.from(this.files).forEach(file => {
                    const fileItem = document.createElement('div');
                    fileItem.className = 'file-preview-item';
                    fileItem.innerHTML = `
                        <span>${file.name}</span>
                        <span class="remove-file" data-file="${file.name}">
                            <i class="fas fa-times"></i>
                        </span>
                    `;
                    previewDiv.appendChild(fileItem);
                });
            }
        });

        // Remove file from preview
        previewDiv.addEventListener('click', function(e) {
            if (e.target.closest('.remove-file')) {
                const fileName = e.target.closest('.remove-file').getAttribute('data-file');
                e.target.closest('.file-preview-item').remove();
                
                // Remove from file input (would need more complex handling in real app)
                const dt = new DataTransfer();
                Array.from(fileInput.files).forEach(file => {
                    if (file.name !== fileName) dt.items.add(file);
                });
                fileInput.files = dt.files;
            }
        });

        // Form validation
        const forms = document.querySelectorAll('.needs-validation');
        Array.from(forms).forEach(form => {
            form.addEventListener('submit', function(event) {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });
    });
</script>
@endsection
@endsection