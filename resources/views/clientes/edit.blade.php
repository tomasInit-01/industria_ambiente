@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="h4 mb-0">Editar Cliente: {{ $cliente->cli_razonsocial }}</h2>
                <div>
                    <a href="{{ route('clientes.index') }}" class="btn btn-secondary me-2">
                        <x-heroicon-o-arrow-left style="width: 16px; height: 16px;" class="me-1" />
                        Volver
                    </a>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-body p-0">
                    <form method="POST" action="{{ route('clientes.update', $cliente->cli_codigo) }}" id="clienteForm">
        @csrf
        @method('PUT')
                        
                        <!-- Header con código y estado -->
                        <div class="border-bottom px-4 py-3 bg-light">
                            <div class="row align-items-center">
                                <div class="col-md-2">
                                    <label for="codigo" class="form-label fw-semibold mb-1">Código:</label>
                                    <input type="text" class="form-control form-control-sm" id="codigo" name="codigo" 
                                           value="{{ $cliente->cli_codigo }}" readonly>
                                </div>
                                <div class="col-md-6"></div>
                                <div class="col-md-4 text-end">
                                    <div class="d-flex align-items-center justify-content-end">
                                        <label class="form-label fw-semibold mb-0 me-3">Estado:</label>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="activo" id="activo_si" value="1" 
                                                   {{ ($cliente->cli_estado ?? 1) == 1 ? 'checked' : '' }}>
                                            <label class="form-check-label" for="activo_si">Sí</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="activo" id="activo_no" value="0"
                                                   {{ ($cliente->cli_estado ?? 1) == 0 ? 'checked' : '' }}>
                                            <label class="form-check-label" for="activo_no">No</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Navegación de solapas -->
                        <ul class="nav nav-tabs nav-tabs-custom" id="clienteTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="general-tab" data-bs-toggle="tab" 
                                        data-bs-target="#general" type="button" role="tab">
                                    General
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="facturacion-tab" data-bs-toggle="tab" 
                                        data-bs-target="#facturacion" type="button" role="tab">
                                    Facturación
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link disabled" type="button">Contactos</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link disabled" type="button">Cobranza</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link disabled" type="button">Observaciones</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link disabled" type="button">Actividades</button>
                            </li>
                        </ul>

                        <!-- Contenido de las solapas -->
                        <div class="tab-content" id="clienteTabsContent">
                            <!-- Solapa General -->
                            <div class="tab-pane fade show active" id="general" role="tabpanel">
                                <div class="p-4">
                                    <div class="row">
                                        <!-- Columna izquierda -->
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="razon_social" class="form-label">Razón Social</label>
                                                <input type="text" class="form-control @error('cli_razonsocial') is-invalid @enderror" 
                                                       id="razon_social" name="cli_razonsocial" 
                                                       value="{{ old('cli_razonsocial', $cliente->cli_razonsocial) }}" required>
                                                @error('cli_razonsocial')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="mb-3">
                                                <label for="fantasia" class="form-label">Fantasía</label>
                                                <input type="text" class="form-control @error('cli_fantasia') is-invalid @enderror" 
                                                       id="fantasia" name="cli_fantasia" 
                                                       value="{{ old('cli_fantasia', $cliente->cli_fantasia) }}">
                                                @error('cli_fantasia')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="mb-3">
                                                <label for="direccion" class="form-label">Dirección</label>
                                                <input type="text" class="form-control @error('cli_direccion') is-invalid @enderror" 
                                                       id="direccion" name="cli_direccion" 
                                                       value="{{ old('cli_direccion', $cliente->cli_direccion) }}">
                                                @error('cli_direccion')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="mb-3">
                                                <label for="localidad" class="form-label">Localidad</label>
                                                <input type="text" class="form-control @error('cli_localidad') is-invalid @enderror" 
                                                       id="localidad" name="cli_localidad" 
                                                       value="{{ old('cli_localidad', $cliente->cli_localidad) }}">
                                                @error('cli_localidad')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="mb-3">
                                                <label for="codigo_postal" class="form-label">Código Postal</label>
                                                <input type="text" class="form-control @error('cli_codigopostal') is-invalid @enderror" 
                                                       id="codigo_postal" name="cli_codigopostal" 
                                                       value="{{ old('cli_codigopostal', $cliente->cli_codigopostal) }}">
                                                @error('cli_codigopostal')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="mb-3">
                                                <label for="email" class="form-label">Email</label>
                                                <input type="email" class="form-control @error('cli_email') is-invalid @enderror" 
                                                       id="email" name="cli_email" 
                                                       value="{{ old('cli_email', $cliente->cli_email) }}">
                                                @error('cli_email')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="mb-3">
                                                <label for="telefono" class="form-label">Teléfono</label>
                                                <input type="text" class="form-control @error('cli_telefono') is-invalid @enderror" 
                                                       id="telefono" name="cli_telefono" 
                                                       value="{{ old('cli_telefono', $cliente->cli_telefono) }}">
                                                @error('cli_telefono')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <!-- Columna derecha -->
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="cuit" class="form-label">CUIT</label>
                                                <input type="text" class="form-control @error('cli_cuit') is-invalid @enderror" 
                                                       id="cuit" name="cli_cuit" 
                                                       value="{{ old('cli_cuit', $cliente->cli_cuit) }}">
                                                @error('cli_cuit')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="mb-3">
                                                <label for="provincia" class="form-label">Provincia</label>
                                                <input type="text" class="form-control" id="provincia" name="cli_codigoprv" 
                                                       value="{{ old('cli_codigoprv', $cliente->cli_codigoprv) }}">
                                            </div>

                                            <div class="mb-3">
                                                <label for="pais" class="form-label">País</label>
                                                <input type="text" class="form-control" id="pais" name="cli_codigopais" 
                                                       value="{{ old('cli_codigopais', $cliente->cli_codigopais ?: 'ARG') }}">
                                            </div>

                                            <div class="mb-3">
                                                <label for="fecha_alta" class="form-label">Fecha alta</label>
                                                <input type="date" class="form-control" id="fecha_alta" name="cli_fechaalta" 
                                                       value="{{ old('cli_fechaalta', $cliente->cli_fechaalta ? date('Y-m-d', strtotime($cliente->cli_fechaalta)) : '') }}">
                                            </div>

                                            <div class="mb-3">
                                                <label for="zona_comercial" class="form-label">Zona Comercial</label>
                                                <input type="text" class="form-control" id="zona_comercial" name="cli_zonacom" 
                                                       value="{{ old('cli_zonacom', $cliente->cli_zonacom) }}">
                                            </div>

                                            <div class="mb-3">
                                                <label for="promotor" class="form-label">Promotor</label>
                                                <input type="text" class="form-control" id="promotor" name="cli_promotor" 
                                                       value="{{ old('cli_promotor', $cliente->cli_promotor) }}">
                                            </div>

                                            <div class="mb-3">
                                                <label for="observaciones" class="form-label">Observaciones Generales</label>
                                                <textarea class="form-control" id="observaciones" name="cli_obsgeneral" rows="4">{{ old('cli_obsgeneral', $cliente->cli_obsgeneral) }}</textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Solapa Facturación -->
                            <div class="tab-pane fade" id="facturacion" role="tabpanel">
                                <div class="p-4">
                                    <div class="row">
                                        <!-- Columna izquierda -->
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="condicion_iva" class="form-label">Condición de I.V.A.</label>
                                                <input type="text" class="form-control" id="condicion_iva" name="cli_codigociva" 
                                                       value="{{ old('cli_codigociva', $cliente->cli_codigociva) }}">
                                            </div>

                                            <div class="mb-3">
                                                <label for="condicion_pago" class="form-label">Condición de pago</label>
                                                <input type="text" class="form-control" id="condicion_pago" name="cli_codigopag" 
                                                       value="{{ old('cli_codigopag', $cliente->cli_codigopag) }}">
                                            </div>

                                            <div class="mb-3">
                                                <label for="tipo_cliente" class="form-label">Tipo Cliente</label>
                                                <input type="text" class="form-control" id="tipo_cliente" name="cli_codigotcli" 
                                                       value="{{ old('cli_codigotcli', $cliente->cli_codigotcli) }}">
                                            </div>

                                            <div class="mb-3">
                                                <label for="lista_precios" class="form-label">Lista de Precios</label>
                                                <input type="text" class="form-control" id="lista_precios" name="cli_codigolp" 
                                                       value="{{ old('cli_codigolp', $cliente->cli_codigolp) }}">
                                            </div>

                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="nro_precio" class="form-label">Nro Precio</label>
                                                        <select class="form-select" id="nro_precio" name="cli_nroprecio">
                                                            <option value="">Seleccionar...</option>
                                                            <option value="1" {{ old('cli_nroprecio', $cliente->cli_nroprecio) == '1' ? 'selected' : '' }}>1</option>
                                                            <option value="2" {{ old('cli_nroprecio', $cliente->cli_nroprecio) == '2' ? 'selected' : '' }}>2</option>
                                                            <option value="3" {{ old('cli_nroprecio', $cliente->cli_nroprecio) == '3' ? 'selected' : '' }}>3</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label for="tipo_factura" class="form-label">Tipo de Factura</label>
                                                <select class="form-select" id="tipo_factura" name="cli_factura">
                                                    <option value="">Seleccionar...</option>
                                                    <option value="A" {{ old('cli_factura', $cliente->cli_factura) == 'A' ? 'selected' : '' }}>A</option>
                                                    <option value="B" {{ old('cli_factura', $cliente->cli_factura) == 'B' ? 'selected' : '' }}>B</option>
                                                    <option value="C" {{ old('cli_factura', $cliente->cli_factura) == 'C' ? 'selected' : '' }}>C</option>
                                                </select>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="descuento_global" class="form-label">Descuento Global %</label>
                                                        <input type="number" step="0.01" class="form-control" id="descuento_global" name="cli_descuentoglobal" 
                                                               value="{{ old('cli_descuentoglobal', $cliente->cli_descuentoglobal ?: '0.00') }}">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Columna derecha -->
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="observaciones_facturacion" class="form-label">Observaciones Facturación</label>
                                                <textarea class="form-control" id="observaciones_facturacion" name="cli_obs" 
                                                          rows="15">{{ old('cli_obs', $cliente->cli_obs) }}</textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Botones de acción -->
                        <div class="card-footer bg-light border-top">
                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('clientes.index') }}" class="btn btn-secondary">
                                    <x-heroicon-o-x-mark style="width: 16px; height: 16px;" class="me-1" />
                                    Cancelar
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <x-heroicon-o-check style="width: 16px; height: 16px;" class="me-1" />
                                    Actualizar Cliente
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Estilos personalizados para las solapas */
    .nav-tabs-custom {
        border-bottom: 1px solid #dee2e6;
        background-color: #f8f9fa;
        padding: 0;
        margin: 0;
    }

    .nav-tabs-custom .nav-link {
        border: none;
        border-radius: 0;
        padding: 12px 20px;
        color: #495057;
        background-color: transparent;
        font-weight: 500;
        position: relative;
    }

    .nav-tabs-custom .nav-link:hover {
        background-color: #e9ecef;
        border: none;
    }

    .nav-tabs-custom .nav-link.active {
        background-color: #fff;
        color: #0d6efd;
        border: none;
        border-bottom: 2px solid #0d6efd;
    }

    .nav-tabs-custom .nav-link.disabled {
        color: #6c757d;
        background-color: transparent;
        cursor: not-allowed;
    }

    /* Estilo para los campos de formulario */
    .form-label {
        font-weight: 500;
        color: #495057;
        margin-bottom: 0.25rem;
    }

    .form-control, .form-select {
        border: 1px solid #ced4da;
        border-radius: 0.25rem;
        font-size: 0.875rem;
    }

    .form-control:focus, .form-select:focus {
        border-color: #86b7fe;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    }

    /* Header del formulario */
    .bg-light {
        background-color: #f8f9fa !important;
    }

    /* Radio buttons en línea */
    .form-check-inline .form-check-input {
        margin-right: 0.25rem;
    }

    .form-check-inline .form-check-label {
        margin-right: 1rem;
    }

    /* Espaciado de contenido */
    .tab-content {
        min-height: 400px;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Inicializar tooltips si están disponibles
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        if (tooltipTriggerList.length > 0) {
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        }

        // Manejar cambios en las solapas
        const tabs = document.querySelectorAll('#clienteTabs button[data-bs-toggle="tab"]');
        tabs.forEach(tab => {
            tab.addEventListener('shown.bs.tab', function(e) {
                // Aquí se puede agregar lógica adicional cuando se cambie de solapa
                console.log('Solapa activa:', e.target.textContent.trim());
            });
        });

        // Validación básica del formulario
        const form = document.getElementById('clienteForm');
        if (form) {
            form.addEventListener('submit', function(e) {
                const razonSocial = document.getElementById('razon_social');
                if (!razonSocial.value.trim()) {
                    e.preventDefault();
                    alert('La Razón Social es obligatoria');
                    razonSocial.focus();
                    return;
                }
            });
        }
    });
</script>
@endsection
