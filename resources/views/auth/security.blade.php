@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card border-0 shadow-lg">
                <div class="card-header bg-gradient-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="mb-0"><i class="fas fa-shield-alt me-2"></i> Seguridad y Contraseña</h3>
                        <span class="badge bg-white text-primary rounded-pill fs-6">Usuario: {{ $user->usu_codigo }}</span>
                    </div>
                </div>
                
                <div class="card-body p-4">
                    <div class="stepper-wrapper mb-5">
                        <div class="stepper-item completed">
                            <div class="step-counter bg-primary text-white">1</div>
                            <div class="step-name">Autenticación</div>
                        </div>
                        <div class="stepper-item active">
                            <div class="step-counter bg-primary text-white">2</div>
                            <div class="step-name">Seguridad</div>
                        </div>
                        <div class="stepper-item">
                            <div class="step-counter">3</div>
                            <div class="step-name">Privacidad</div>
                        </div>
                    </div>

                    <div class="row g-4">
                        <!-- Cambio de Contraseña -->
                        <div class="col-md-6">
                            <div class="card h-100 border-0 shadow-sm">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0"><i class="fas fa-key me-2 text-primary"></i> Cambiar Contraseña</h5>
                                </div>
                                <div class="card-body">
                                    <form class="needs-validation" novalidate>
                                        <div class="mb-3">
                                            <label class="form-label">Contraseña Actual</label>
                                            <div class="input-group">
                                                <input type="password" class="form-control" placeholder="Ingrese su contraseña actual" required>
                                                <button class="btn btn-outline-secondary toggle-password" type="button">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Nueva Contraseña</label>
                                            <div class="input-group">
                                                <input type="password" class="form-control" placeholder="Cree una nueva contraseña" required>
                                                <button class="btn btn-outline-secondary toggle-password" type="button">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </div>
                                            <div class="password-strength mt-2">
                                                <div class="progress" style="height: 5px;">
                                                    <div class="progress-bar bg-danger" role="progressbar" style="width: 25%"></div>
                                                </div>
                                                <small class="text-muted">Seguridad: Débil</small>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Confirmar Contraseña</label>
                                            <input type="password" class="form-control" placeholder="Repita la nueva contraseña" required>
                                        </div>
                                        <button type="submit" class="btn btn-primary w-100 py-2">
                                            <i class="fas fa-save me-2"></i> Actualizar Contraseña
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Autenticación de Dos Factores -->
                        <div class="col-md-6">
                            <div class="card h-100 border-0 shadow-sm">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0"><i class="fas fa-mobile-alt me-2 text-primary"></i> Autenticación 2FA</h5>
                                </div>
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-4">
                                        <div class="flex-shrink-0">
                                            <div class="bg-primary bg-opacity-10 p-3 rounded-circle">
                                                <i class="fas fa-lock text-primary fs-4"></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h6 class="mb-1">Protección adicional</h6>
                                            <p class="small text-muted mb-0">Añade una capa extra de seguridad a tu cuenta</p>
                                        </div>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="enable2FA" style="width: 3em; height: 1.5em;">
                                        </div>
                                    </div>

                                    <div id="2faSetup" class="bg-light p-4 rounded text-center" style="display: none;">
                                        <h6 class="mb-3">Configuración 2FA</h6>
                                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=otpauth://totp/Example:user@example.com?secret=JBSWY3DPEHPK3PXP&issuer=Example" 
                                             class="img-fluid mb-3 border p-2 bg-white" alt="QR Code">
                                        <p class="small text-muted mb-3">Escanee este código con Google Authenticator o similar</p>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Código de Verificación</label>
                                            <input type="text" class="form-control text-center" placeholder="000000" maxlength="6">
                                        </div>
                                        <button class="btn btn-sm btn-outline-primary w-100">Verificar Código</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Sesiones Activas -->
                        <div class="col-12">
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0"><i class="fas fa-laptop me-2 text-primary"></i> Sesiones Activas</h5>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th class="ps-4">Dispositivo</th>
                                                    <th>Ubicación</th>
                                                    <th>IP</th>
                                                    <th>Última Actividad</th>
                                                    <th class="pe-4">Acciones</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td class="ps-4">
                                                        <div class="d-flex align-items-center">
                                                            <i class="fab fa-windows text-primary me-2 fs-5"></i>
                                                            <div>
                                                                <p class="mb-0 fw-bold">Chrome - Windows</p>
                                                                <small class="text-muted">Dispositivo principal</small>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>Lima, Perú</td>
                                                    <td>192.168.1.1</td>
                                                    <td>
                                                        <span class="badge bg-success bg-opacity-10 text-success">Activo ahora</span>
                                                        <small class="d-block text-muted">Hace 2 minutos</small>
                                                    </td>
                                                    <td class="pe-4">
                                                        <button class="btn btn-sm btn-outline-danger">
                                                            <i class="fas fa-sign-out-alt"></i> Cerrar
                                                        </button>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="ps-4">
                                                        <div class="d-flex align-items-center">
                                                            <i class="fab fa-apple text-muted me-2 fs-5"></i>
                                                            <div>
                                                                <p class="mb-0 fw-bold">Safari - iPhone</p>
                                                                <small class="text-muted">Dispositivo móvil</small>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>Lima, Perú</td>
                                                    <td>192.168.1.2</td>
                                                    <td>
                                                        <span class="badge bg-warning bg-opacity-10 text-warning">Inactivo</span>
                                                        <small class="d-block text-muted">Hace 3 horas</small>
                                                    </td>
                                                    <td class="pe-4">
                                                        <button class="btn btn-sm btn-outline-danger">
                                                            <i class="fas fa-sign-out-alt"></i> Cerrar
                                                        </button>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
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
    .stepper-wrapper {
        display: flex;
        justify-content: space-between;
        margin-bottom: 20px;
    }
    .stepper-item {
        position: relative;
        display: flex;
        flex-direction: column;
        align-items: center;
        flex: 1;
    }
    .stepper-item::before {
        position: absolute;
        content: "";
        border-bottom: 2px solid #dee2e6;
        width: 100%;
        top: 20px;
        left: -50%;
        z-index: 2;
    }
    .stepper-item::after {
        position: absolute;
        content: "";
        border-bottom: 2px solid #dee2e6;
        width: 100%;
        top: 20px;
        left: 50%;
        z-index: 2;
    }
    .stepper-item .step-counter {
        position: relative;
        z-index: 5;
        display: flex;
        justify-content: center;
        align-items: center;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: #dee2e6;
        color: #495057;
        margin-bottom: 6px;
    }
    .stepper-item.completed .step-counter {
        background-color: #0d6efd;
        color: white;
    }
    .stepper-item.active .step-counter {
        background-color: #0d6efd;
        color: white;
    }
    .stepper-item.completed::after,
    .stepper-item.active::after {
        border-bottom: 2px solid #0d6efd;
    }
    .stepper-item:first-child::before {
        content: none;
    }
    .stepper-item:last-child::after {
        content: none;
    }
    .step-name {
        color: #6c757d;
        font-size: 0.875rem;
    }
    .completed .step-name,
    .active .step-name {
        color: #0d6efd;
        font-weight: 500;
    }
</style>

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Toggle password visibility
        document.querySelectorAll('.toggle-password').forEach(button => {
            button.addEventListener('click', function() {
                const input = this.previousElementSibling;
                const icon = this.querySelector('i');
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.replace('fa-eye', 'fa-eye-slash');
                } else {
                    input.type = 'password';
                    icon.classList.replace('fa-eye-slash', 'fa-eye');
                }
            });
        });

        // 2FA toggle
        document.getElementById('enable2FA').addEventListener('change', function() {
            const setupDiv = document.getElementById('2faSetup');
            if (this.checked) {
                setupDiv.style.display = 'block';
            } else {
                setupDiv.style.display = 'none';
            }
        });

        // Password strength indicator
        const passwordInput = document.querySelector('input[type="password"][placeholder*="nueva contraseña"]');
        passwordInput.addEventListener('input', function() {
            const strengthDiv = this.closest('.mb-3').querySelector('.password-strength');
            const progressBar = strengthDiv.querySelector('.progress-bar');
            const strengthText = strengthDiv.querySelector('small');
            
            if (this.value.length === 0) {
                progressBar.style.width = '0%';
                progressBar.className = 'progress-bar';
                strengthText.textContent = '';
                strengthText.className = 'text-muted';
                return;
            }
            
            if (this.value.length < 6) {
                progressBar.style.width = '25%';
                progressBar.className = 'progress-bar bg-danger';
                strengthText.textContent = 'Débil';
                strengthText.className = 'text-danger';
            } else if (this.value.length < 10) {
                progressBar.style.width = '50%';
                progressBar.className = 'progress-bar bg-warning';
                strengthText.textContent = 'Moderada';
                strengthText.className = 'text-warning';
            } else {
                progressBar.style.width = '100%';
                progressBar.className = 'progress-bar bg-success';
                strengthText.textContent = 'Fuerte';
                strengthText.className = 'text-success';
            }
        });
    });
</script>
@endsection
@endsection