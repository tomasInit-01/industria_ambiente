@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="h4 mb-0">Crear Nueva Cotización</h2>
                <div>
                    <a href="{{ route('ventas.index') }}" class="btn btn-secondary me-2">
                        <x-heroicon-o-arrow-left style="width: 16px; height: 16px;" class="me-1" />
                        Volver
                    </a>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-body p-0">
                    <form method="POST" action="{{ route('ventas.store') }}" id="cotizacionForm">
                        @csrf
                        
                        <!-- Header con información básica -->
                        <div class="border-bottom px-4 py-3 bg-light">
                            <div class="row align-items-center">
                                <div class="col-md-2">
                                    <label for="cliente_codigo" class="form-label fw-semibold mb-1">Cliente:</label>
                                    <input type="text" class="form-control form-control-sm" id="cliente_codigo" name="coti_codigocli" 
                                           value="{{ old('coti_codigocli', '4660') }}" placeholder="4660">
                                </div>
                                <div class="col-md-4">
                                    <label for="cliente_nombre" class="form-label fw-semibold mb-1">&nbsp;</label>
                                    <input type="text" class="form-control form-control-sm" id="cliente_nombre" 
                                           value="ESTABLECIMIENTOS LAS MARIAS SA" readonly>
                                </div>
                                <div class="col-md-2">
                                    <label for="sucursal" class="form-label fw-semibold mb-1">Sucursal:</label>
                                    <input type="text" class="form-control form-control-sm" id="sucursal" name="coti_codigosuc">
                                </div>
                                <div class="col-md-2">
                                    <label for="numero" class="form-label fw-semibold mb-1">Nro:</label>
                                    <input type="text" class="form-control form-control-sm" id="numero" name="coti_num" 
                                           value="NUEVO" readonly>
                                </div>
                            </div>
                        </div>

                        <!-- Navegación de solapas -->
                        <ul class="nav nav-tabs nav-tabs-custom" id="cotizacionTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="general-tab" data-bs-toggle="tab" 
                                        data-bs-target="#general" type="button" role="tab">
                                    General
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="gestion-tab" data-bs-toggle="tab" 
                                        data-bs-target="#gestion" type="button" role="tab">
                                    Gestión
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="empresa-tab" data-bs-toggle="tab" 
                                        data-bs-target="#empresa" type="button" role="tab">
                                    Empresa
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link disabled" type="button">Documentos</button>
                            </li>
                        </ul>

                        <!-- Contenido de las solapas -->
                        <div class="tab-content" id="cotizacionTabsContent">
                            <!-- Solapa General -->
                            <div class="tab-pane fade show active" id="general" role="tabpanel">
                                <div class="p-4">
                                    <!-- Información superior -->
                                    <div class="row mb-4">
                                        <div class="col-md-2">
                                            <label for="sector" class="form-label">Sector:</label>
                                            <div class="input-group">
                                                <input type="text" class="form-control" id="sector" name="coti_sector">
                                                <button class="btn btn-outline-secondary" type="button">
                                                    <x-heroicon-o-magnifying-glass style="width: 16px; height: 16px;" />
                                                </button>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <button type="button" class="btn btn-outline-primary mt-4">Clonar</button>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-check mt-4">
                                                <input class="form-check-input" type="checkbox" id="abierta" name="abierta">
                                                <label class="form-check-label" for="abierta">Abierta</label>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-check mt-4">
                                                <input class="form-check-input" type="checkbox" id="abono" name="coti_abono">
                                                <label class="form-check-label" for="abono">Abono</label>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <label for="fecha_alta" class="form-label">Alta:</label>
                                            <input type="date" class="form-control" id="fecha_alta" name="coti_fechaalta" 
                                                   value="{{ date('Y-m-d') }}">
                                        </div>
                                        <div class="col-md-2">
                                            <label for="fecha_venc" class="form-label">Venc:</label>
                                            <input type="date" class="form-control" id="fecha_venc" name="coti_fechafin">
                                        </div>
                                    </div>

                                    <!-- Segunda fila -->
                                    <div class="row mb-4">
                                        <div class="col-md-2">
                                            <label for="matriz" class="form-label">Matriz:</label>
                                            <div class="input-group">
                                                <select class="form-select" id="matriz" name="coti_codigomatriz">
                                                    <option value="$">$ Pesos</option>
                                                    <option value="U$">U$ Dólares</option>
                                                </select>
                                                <button class="btn btn-outline-secondary" type="button">
                                                    <x-heroicon-o-magnifying-glass style="width: 16px; height: 16px;" />
                                                </button>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <label for="estado" class="form-label">Estado:</label>
                                            <select class="form-select" id="estado" name="coti_estado">
                                                <option value="En Espera" selected>En Espera</option>
                                                <option value="Aprobado">Aprobado</option>
                                                <option value="Rechazado">Rechazado</option>
                                                <option value="En Proceso">En Proceso</option>
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-check mt-4">
                                                <input class="form-check-input" type="checkbox" id="oc" name="oc">
                                                <label class="form-check-label" for="oc">O.C.</label>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-check mt-4">
                                                <input class="form-check-input" type="checkbox" id="recep" name="recep">
                                                <label class="form-check-label" for="recep">Recep</label>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <label for="vigencia" class="form-label">Vigencia:</label>
                                            <div class="input-group">
                                                <input type="number" class="form-control" id="vigencia" name="coti_vigencia" value="30">
                                                <span class="input-group-text">días</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Tercera fila -->
                                    <div class="row mb-4">
                                        <div class="col-md-2">
                                            <label for="divisa" class="form-label">Divisa:</label>
                                            <div class="input-group">
                                                <input type="text" class="form-control" id="divisa" name="coti_codigodiv" value="$">
                                                <select class="form-select">
                                                    <option>Pesos</option>
                                                </select>
                                                <button class="btn btn-outline-secondary" type="button">
                                                    <x-heroicon-o-magnifying-glass style="width: 16px; height: 16px;" />
                                                </button>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <label for="paridad" class="form-label">Paridad:</label>
                                            <input type="number" step="0.01" class="form-control" id="paridad" name="coti_paridad" value="1">
                                        </div>
                                        <div class="col-md-2">
                                            <label for="factor" class="form-label">Factor:</label>
                                            <input type="number" step="0.01" class="form-control" id="factor" name="coti_factor">
                                        </div>
                                        <div class="col-md-2">
                                            <label for="telefono" class="form-label">Teléfono:</label>
                                            <input type="text" class="form-control" id="telefono" name="coti_telefono">
                                        </div>
                                        <div class="col-md-4">
                                            <label for="usuario" class="form-label">Usuario:</label>
                                            <div class="input-group">
                                                <input type="text" class="form-control" id="usuario" value="esantander" readonly>
                                                <button class="btn btn-outline-secondary" type="button">
                                                    <x-heroicon-o-magnifying-glass style="width: 16px; height: 16px;" />
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Comentarios -->
                                    <div class="row mb-4">
                                        <div class="col-md-12">
                                            <label for="comentario" class="form-label">Comentario:</label>
                                            <textarea class="form-control" id="comentario" name="coti_notas" rows="3"></textarea>
                                        </div>
                                    </div>

                                    <!-- Tabla de Items/Ensayos -->
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <h5>Items de la Cotización</h5>
                                                <div>
                                                    <button type="button" class="btn btn-success btn-sm me-2" data-bs-toggle="modal" data-bs-target="#modalAgregarEnsayo">
                                                        <x-heroicon-o-plus style="width: 16px; height: 16px;" class="me-1" />
                                                        Agregar Ensayo
                                                    </button>
                                                    <button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#modalAgregarComponente">
                                                        <x-heroicon-o-plus style="width: 16px; height: 16px;" class="me-1" />
                                                        Agregar Componente
                                                    </button>
                                                </div>
                                            </div>
                                            
                                            <div class="table-responsive">
                                                <table class="table table-sm table-bordered">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th style="width: 80px;">Item</th>
                                                            <th style="width: 120px;">Ensayo</th>
                                                            <th>Título</th>
                                                            <th style="width: 120px;">Detalle</th>
                                                            <th style="width: 80px;">Cantidad</th>
                                                            <th style="width: 100px;">Prec. Unit</th>
                                                            <th style="width: 100px;">Total</th>
                                                            <th style="width: 60px;">Acciones</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="tablaItems">
                                                        <tr>
                                                            <td colspan="8" class="text-center text-muted py-4">
                                                                No hay items agregados. Utilice los botones "Agregar Ensayo" o "Agregar Componente" para comenzar.
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                    <tfoot class="table-light">
                                                        <tr>
                                                            <td colspan="6" class="text-end fw-bold">Total:</td>
                                                            <td class="fw-bold">
                                                                <span id="totalGeneral">0.00</span>
                                                            </td>
                                                            <td></td>
                                                        </tr>
                                                    </tfoot>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Solapa Gestión -->
                            <div class="tab-pane fade" id="gestion" role="tabpanel">
                                <div class="p-4">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="responsable" class="form-label">Responsable:</label>
                                                <input type="text" class="form-control" id="responsable" name="coti_responsable">
                                            </div>
                                            <div class="mb-3">
                                                <label for="fecha_aprobado" class="form-label">Fecha Aprobado:</label>
                                                <input type="date" class="form-control" id="fecha_aprobado" name="coti_fechaaprobado">
                                            </div>
                                            <div class="mb-3">
                                                <label for="aprobo" class="form-label">Aprobó:</label>
                                                <input type="text" class="form-control" id="aprobo" name="coti_aprobo">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="fecha_en_curso" class="form-label">Fecha En Curso:</label>
                                                <input type="date" class="form-control" id="fecha_en_curso" name="coti_fechaencurso">
                                            </div>
                                            <div class="mb-3">
                                                <label for="fecha_alta_tecnica" class="form-label">Fecha Alta Técnica:</label>
                                                <input type="date" class="form-control" id="fecha_alta_tecnica" name="coti_fechaaltatecnica">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Solapa Empresa -->
                            <div class="tab-pane fade" id="empresa" role="tabpanel">
                                <div class="p-4">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="empresa" class="form-label">Empresa:</label>
                                                <input type="text" class="form-control" id="empresa" name="coti_empresa">
                                            </div>
                                            <div class="mb-3">
                                                <label for="establecimiento" class="form-label">Establecimiento:</label>
                                                <input type="text" class="form-control" id="establecimiento" name="coti_establecimiento">
                                            </div>
                                            <div class="mb-3">
                                                <label for="direccion_cliente" class="form-label">Dirección Cliente:</label>
                                                <input type="text" class="form-control" id="direccion_cliente" name="coti_direccioncli">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="localidad_cliente" class="form-label">Localidad:</label>
                                                <input type="text" class="form-control" id="localidad_cliente" name="coti_localidad">
                                            </div>
                                            <div class="mb-3">
                                                <label for="partido" class="form-label">Partido:</label>
                                                <input type="text" class="form-control" id="partido" name="coti_partido">
                                            </div>
                                            <div class="mb-3">
                                                <label for="cuit_cliente" class="form-label">CUIT:</label>
                                                <input type="text" class="form-control" id="cuit_cliente" name="coti_cuit">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Botones de acción -->
                        <div class="card-footer bg-light border-top">
                            <div class="d-flex justify-content-between">
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-success">
                                        <x-heroicon-o-plus style="width: 16px; height: 16px;" class="me-1" />
                                        Agregar
                                    </button>
                                    <button type="button" class="btn btn-warning">
                                        <x-heroicon-o-pencil style="width: 16px; height: 16px;" class="me-1" />
                                        Modificar
                                    </button>
                                    <button type="button" class="btn btn-danger" onclick="borrarCotizacion()">
                                        <x-heroicon-o-trash style="width: 16px; height: 16px;" class="me-1" />
                                        Borrar
                                    </button>
                                </div>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('ventas.index') }}" class="btn btn-secondary">
                                        <x-heroicon-o-x-mark style="width: 16px; height: 16px;" class="me-1" />
                                        Cancelar
                                    </a>
                                    <button type="button" class="btn btn-info">
                                        <x-heroicon-o-arrow-path style="width: 16px; height: 16px;" class="me-1" />
                                        Salir
                                    </button>
                                    <button type="submit" class="btn btn-primary">
                                        <x-heroicon-o-check style="width: 16px; height: 16px;" class="me-1" />
                                        Guardar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Agregar Ensayo -->
<div class="modal fade" id="modalAgregarEnsayo" tabindex="-1" aria-labelledby="modalAgregarEnsayoLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalAgregarEnsayoLabel">Ensayo</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formEnsayo">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="ensayo_codigo" class="form-label">Ensayo:</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="ensayo_codigo" name="ensayo_codigo" 
                                       value="000010000100531" placeholder="Código del ensayo">
                                <button class="btn btn-outline-secondary" type="button">
                                    <x-heroicon-o-magnifying-glass style="width: 16px; height: 16px;" />
                                </button>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check mt-4">
                                <input class="form-check-input" type="checkbox" id="no_requiere_custodia">
                                <label class="form-check-label" for="no_requiere_custodia">
                                    No Requiere Cadena de Custodia
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="unidad_medida" class="form-label">U.Medida:</label>
                            <select class="form-select" id="unidad_medida" name="unidad_medida">
                                <option value="" selected></option>
                                <option value="kg">kg</option>
                                <option value="l">l</option>
                                <option value="m">m</option>
                                <option value="unidad">unidad</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="metodo" class="form-label">Método:</label>
                            <select class="form-select" id="metodo" name="metodo">
                                <option value="" selected></option>
                                <option value="EPA CFR 40 Part 50 App J">EPA CFR 40 Part 50 App J</option>
                                <option value="IRAM">IRAM</option>
                                <option value="ASTM">ASTM</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="normativa" class="form-label">Normativa:</label>
                            <select class="form-select" id="normativa" name="normativa">
                                <option value="" selected></option>
                                <option value="IRAM">IRAM</option>
                                <option value="EPA">EPA</option>
                                <option value="ISO">ISO</option>
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-2">
                            <label for="cantidad_ensayo" class="form-label">Cantidad:</label>
                            <input type="number" class="form-control" id="cantidad_ensayo" name="cantidad" value="3" min="1">
                        </div>
                        <div class="col-md-2">
                            <div class="form-check mt-4">
                                <input class="form-check-input" type="checkbox" id="flexible">
                                <label class="form-check-label" for="flexible">Flexible</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label for="precio_ensayo" class="form-label">Precios:</label>
                            <input type="number" step="0.01" class="form-control" id="precio_ensayo" name="precio" value="0.00">
                        </div>
                        <div class="col-md-4">
                            <div class="form-check mt-4">
                                <input class="form-check-input" type="checkbox" id="bonificado">
                                <label class="form-check-label" for="bonificado">Bonificado</label>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <input type="number" step="0.01" class="form-control" placeholder="0.00" readonly>
                            <small class="text-muted">Última Cotización</small>
                        </div>
                        <div class="col-md-6">
                            <input type="number" step="0.01" class="form-control" placeholder="0.00" readonly>
                            <small class="text-muted">Última Factura</small>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="precio_final" class="form-label">Precio:</label>
                            <input type="number" step="0.01" class="form-control" id="precio_final" name="precio_final" value="1.00">
                        </div>
                    </div>

                    <!-- Notas -->
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="nota_tipo" id="nota_imprimible" value="imprimible" checked>
                                <label class="form-check-label" for="nota_imprimible">Nota Imprimible</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="nota_tipo" id="nota_interna" value="interna">
                                <label class="form-check-label" for="nota_interna">Nota Interna</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="nota_tipo" id="nota_fact" value="fact">
                                <label class="form-check-label" for="nota_fact">Nota Fact.</label>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-12">
                            <button type="button" class="btn btn-sm btn-outline-secondary mb-2">Insertar Nota Predefinida</button>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="predeterminar">
                                <label class="form-check-label" for="predeterminar">Predeterminar</label>
                            </div>
                            <textarea class="form-control" rows="4" placeholder="Comprende el análisis puntual de calidad de aire exterior en sitios sobre a definir en función de los vientos predominantes para la determinación de MP TOTAL (EPA O 2 1) o MP10 (EPA O 2 3)."></textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="agregarEnsayo()">Aceptar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Agregar Componente -->
<div class="modal fade" id="modalAgregarComponente" tabindex="-1" aria-labelledby="modalAgregarComponenteLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="modalAgregarComponenteLabel">Componente</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formComponente">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="componente_codigo" class="form-label">Ensayo:</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="componente_codigo" name="componente_codigo" 
                                       value="000120000200100" placeholder="Código del componente">
                                <button class="btn btn-outline-secondary" type="button">
                                    <x-heroicon-o-magnifying-glass style="width: 16px; height: 16px;" />
                                </button>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check mt-4">
                                <input class="form-check-input" type="checkbox" id="comp_no_requiere_custodia">
                                <label class="form-check-label" for="comp_no_requiere_custodia">
                                    No Requiere Cadena de Custodia
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="comp_unidad_medida" class="form-label">U.Medida:</label>
                            <select class="form-select" id="comp_unidad_medida" name="comp_unidad_medida">
                                <option value="" selected></option>
                                <option value="kg">kg</option>
                                <option value="l">l</option>
                                <option value="m">m</option>
                                <option value="unidad">unidad</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="comp_metodo" class="form-label">Método:</label>
                            <select class="form-select" id="comp_metodo" name="comp_metodo">
                                <option value="EPA CFR 40 Part 50 App J" selected>EPA CFR 40 Part 50 App J</option>
                                <option value="IRAM">IRAM</option>
                                <option value="ASTM">ASTM</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="comp_normativa" class="form-label">Normativa:</label>
                            <select class="form-select" id="comp_normativa" name="comp_normativa">
                                <option value="" selected></option>
                                <option value="IRAM">IRAM</option>
                                <option value="EPA">EPA</option>
                                <option value="ISO">ISO</option>
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-2">
                            <label for="comp_cantidad" class="form-label">Cantidad:</label>
                            <input type="number" class="form-control" id="comp_cantidad" name="comp_cantidad" value="1" min="1">
                        </div>
                        <div class="col-md-2">
                            <div class="form-check mt-4">
                                <input class="form-check-input" type="checkbox" id="comp_flexible">
                                <label class="form-check-label" for="comp_flexible">Flexible</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label for="comp_precio_lista" class="form-label">P.Lista:</label>
                            <div class="input-group">
                                <input type="number" step="0.01" class="form-control" id="comp_precio_lista" 
                                       name="comp_precio_lista" value="237055.00" readonly>
                                <span class="input-group-text">x Factor:</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check mt-4">
                                <input class="form-check-input" type="checkbox" id="comp_bonificado">
                                <label class="form-check-label" for="comp_bonificado">Bonificado</label>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="comp_precios" class="form-label">Precios:</label>
                            <input type="number" step="0.01" class="form-control" id="comp_precios" name="comp_precios" value="237055.00">
                        </div>
                        <div class="col-md-4">
                            <input type="number" step="0.01" class="form-control" placeholder="0.00" readonly>
                            <small class="text-muted">Última Cotización</small>
                        </div>
                        <div class="col-md-4">
                            <input type="number" step="0.01" class="form-control" placeholder="0.00" readonly>
                            <small class="text-muted">Última Factura</small>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="comp_precio_final" class="form-label">Precio:</label>
                            <input type="number" step="0.01" class="form-control" id="comp_precio_final" 
                                   name="comp_precio_final" value="237055.00">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Precio de lista</label>
                        </div>
                    </div>

                    <!-- Notas -->
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="comp_nota_tipo" id="comp_nota_imprimible" value="imprimible" checked>
                                <label class="form-check-label" for="comp_nota_imprimible">Nota Imprimible</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="comp_nota_tipo" id="comp_nota_interna" value="interna">
                                <label class="form-check-label" for="comp_nota_interna">Nota Interna</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="comp_nota_tipo" id="comp_nota_fact" value="fact">
                                <label class="form-check-label" for="comp_nota_fact">Nota Fact.</label>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-12">
                            <button type="button" class="btn btn-sm btn-outline-secondary mb-2">Insertar Nota Predefinida</button>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="comp_predeterminar">
                                <label class="form-check-label" for="comp_predeterminar">Predeterminar</label>
                            </div>
                            <textarea class="form-control" rows="4" placeholder="Descripción del componente..."></textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="agregarComponente()">Aceptar</button>
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

    /* Radio buttons y checkboxes */
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

    /* Tabla de items */
    .table th {
        background-color: #f8f9fa;
        border-color: #dee2e6;
        font-weight: 600;
        font-size: 0.8rem;
        padding: 0.5rem;
    }

    .table td {
        padding: 0.5rem;
        vertical-align: middle;
        font-size: 0.875rem;
    }

    /* Botones de búsqueda */
    .btn-outline-secondary {
        border-color: #ced4da;
        color: #6c757d;
    }

    .btn-outline-secondary:hover {
        background-color: #6c757d;
        border-color: #6c757d;
    }

    /* Input groups */
    .input-group .form-control {
        border-right: 0;
    }

    .input-group .form-control:not(:last-child) {
        border-top-right-radius: 0;
        border-bottom-right-radius: 0;
    }

    .input-group .form-control:not(:first-child) {
        border-top-left-radius: 0;
        border-bottom-left-radius: 0;
        border-left: 0;
    }

    .input-group .btn {
        border-left: 0;
    }

    /* Modales */
    .modal-header.bg-primary {
        background-color: #0d6efd !important;
    }

    .modal-header.bg-info {
        background-color: #0dcaf0 !important;
    }

    /* Campos pequeños en modales */
    .modal-body .form-control, 
    .modal-body .form-select {
        font-size: 0.875rem;
    }

    /* Botones pequeños */
    .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.8rem;
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
        const tabs = document.querySelectorAll('#cotizacionTabs button[data-bs-toggle="tab"]');
        tabs.forEach(tab => {
            tab.addEventListener('shown.bs.tab', function(e) {
                console.log('Solapa activa:', e.target.textContent.trim());
            });
        });

        // Validación básica del formulario
        const form = document.getElementById('cotizacionForm');
        if (form) {
            form.addEventListener('submit', function(e) {
                const clienteCodigo = document.getElementById('cliente_codigo');
                if (!clienteCodigo.value.trim()) {
                    e.preventDefault();
                    alert('El código de cliente es obligatorio');
                    clienteCodigo.focus();
                    return;
                }
            });
        }
    });

    // Contador para items
    let contadorItems = 0;

    // Función para agregar ensayo
    function agregarEnsayo() {
        const ensayoCodigo = document.getElementById('ensayo_codigo').value;
        const cantidad = document.getElementById('cantidad_ensayo').value || 1;
        const precio = document.getElementById('precio_final').value || 0;
        const total = (cantidad * precio).toFixed(2);
        
        contadorItems++;
        
        const tabla = document.getElementById('tablaItems');
        
        // Remover fila de "no hay items" si existe
        const filaVacia = tabla.querySelector('tr td[colspan="8"]');
        if (filaVacia) {
            filaVacia.parentElement.remove();
        }
        
        // Crear nueva fila
        const nuevaFila = document.createElement('tr');
        nuevaFila.innerHTML = `
            <td>${contadorItems}</td>
            <td>${ensayoCodigo}</td>
            <td>CALIDAD DE AIRE</td>
            <td><button class="btn btn-sm btn-outline-info" onclick="verDetalle(${contadorItems})">Ver</button></td>
            <td>${cantidad}</td>
            <td>$${parseFloat(precio).toFixed(2)}</td>
            <td>$${total}</td>
            <td>
                <button class="btn btn-sm btn-outline-danger" onclick="eliminarItem(this)">
                    ✕
                </button>
            </td>
        `;
        
        tabla.appendChild(nuevaFila);
        
        // Actualizar total
        actualizarTotal();
        
        // Cerrar modal y limpiar formulario
        bootstrap.Modal.getInstance(document.getElementById('modalAgregarEnsayo')).hide();
        document.getElementById('formEnsayo').reset();
        document.getElementById('ensayo_codigo').value = '000010000100531';
        document.getElementById('cantidad_ensayo').value = 3;
        document.getElementById('precio_final').value = 1.00;
    }

    // Función para agregar componente
    function agregarComponente() {
        const componenteCodigo = document.getElementById('componente_codigo').value;
        const cantidad = document.getElementById('comp_cantidad').value || 1;
        const precio = document.getElementById('comp_precio_final').value || 0;
        const total = (cantidad * precio).toFixed(2);
        
        contadorItems++;
        
        const tabla = document.getElementById('tablaItems');
        
        // Remover fila de "no hay items" si existe
        const filaVacia = tabla.querySelector('tr td[colspan="8"]');
        if (filaVacia) {
            filaVacia.parentElement.remove();
        }
        
        // Crear nueva fila
        const nuevaFila = document.createElement('tr');
        nuevaFila.innerHTML = `
            <td>${contadorItems}</td>
            <td>${componenteCodigo}</td>
            <td>MATERIAL PARTICULADO PM 10</td>
            <td><button class="btn btn-sm btn-outline-info" onclick="verDetalle(${contadorItems})">Ver</button></td>
            <td>${cantidad}</td>
            <td>$${parseFloat(precio).toFixed(2)}</td>
            <td>$${total}</td>
            <td>
                <button class="btn btn-sm btn-outline-danger" onclick="eliminarItem(this)">
                    ✕
                </button>
            </td>
        `;
        
        tabla.appendChild(nuevaFila);
        
        // Actualizar total
        actualizarTotal();
        
        // Cerrar modal y limpiar formulario
        bootstrap.Modal.getInstance(document.getElementById('modalAgregarComponente')).hide();
        document.getElementById('formComponente').reset();
        document.getElementById('componente_codigo').value = '000120000200100';
        document.getElementById('comp_cantidad').value = 1;
        document.getElementById('comp_precio_final').value = 237055.00;
    }

    // Función para eliminar item
    function eliminarItem(boton) {
        if (confirm('¿Está seguro de que desea eliminar este item?')) {
            boton.closest('tr').remove();
            actualizarTotal();
            
            // Si no quedan items, mostrar mensaje
            const tabla = document.getElementById('tablaItems');
            if (tabla.children.length === 0) {
                tabla.innerHTML = `
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">
                            No hay items agregados. Utilice los botones "Agregar Ensayo" o "Agregar Componente" para comenzar.
                        </td>
                    </tr>
                `;
            }
        }
    }

    // Función para actualizar total
    function actualizarTotal() {
        const tabla = document.getElementById('tablaItems');
        let total = 0;
        
        // Sumar todos los totales de las filas
        tabla.querySelectorAll('tr').forEach(fila => {
            const celdaTotal = fila.querySelector('td:nth-child(7)');
            if (celdaTotal && celdaTotal.textContent.includes('$')) {
                const valor = parseFloat(celdaTotal.textContent.replace('$', ''));
                if (!isNaN(valor)) {
                    total += valor;
                }
            }
        });
        
        document.getElementById('totalGeneral').textContent = total.toFixed(2);
    }

    // Función para ver detalle (placeholder)
    function verDetalle(item) {
        alert('Ver detalle del item ' + item + ' (funcionalidad pendiente)');
    }

    // Función para borrar cotización
    function borrarCotizacion() {
        if (confirm('¿Está seguro de que desea borrar esta cotización?')) {
            // Limpiar formulario
            document.getElementById('cotizacionForm').reset();
            
            // Limpiar tabla
            document.getElementById('tablaItems').innerHTML = `
                <tr>
                    <td colspan="8" class="text-center text-muted py-4">
                        No hay items agregados. Utilice los botones "Agregar Ensayo" o "Agregar Componente" para comenzar.
                    </td>
                </tr>
            `;
            
            // Resetear contador
            contadorItems = 0;
            actualizarTotal();
            
            alert('Cotización borrada');
        }
    }
</script>
@endsection
