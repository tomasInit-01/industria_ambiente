@extends('layouts.app')

@section('content')
<style>
    .audit-card {
        border-left: 4px solid #0d6efd;
        transition: all 0.3s ease;
    }
    .audit-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    .status-success {
        border-left-color: #198754;
    }
    .status-warning {
        border-left-color: #ffc107;
    }
    .status-danger {
        border-left-color: #dc3545;
    }
    .sidebar {
        background-color: #f8f9fa;
        min-height: 100vh;
    }
</style>

<div class="container-fluid">
    <div class="row">
        <!-- Main content -->
        <main class="col-md-9 col-lg-10 px-md-4" style="margin: 0 auto;">
            <!-- Header -->
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Dashboard de Auditoría</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <button class="btn btn-sm btn-outline-primary" id="exportarReporte">
                        <i class="bi bi-download"></i> Exportar Reporte
                    </button>
                </div>
            </div>

            <!-- Filtros -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-funnel"></i> Filtros de Reporte
                    </h5>
                </div>
                <div class="card-body">
                    <form id="filtrosForm">
                        <div class="row">
                            <div class="col-md-4">
                                <label for="fecha_desde" class="form-label">Fecha Desde</label>
                                <input type="date" class="form-control" id="fecha_desde" name="fecha_desde">
                            </div>
                            <div class="col-md-4">
                                <label for="fecha_hasta" class="form-label">Fecha Hasta</label>
                                <input type="date" class="form-control" id="fecha_hasta" name="fecha_hasta">
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <button type="button" class="btn btn-secondary me-2" id="limpiarFiltros">
                                    <i class="bi bi-arrow-clockwise"></i> Limpiar
                                </button>
                                <button type="button" class="btn btn-primary" id="aplicarFiltros">
                                    <i class="bi bi-check-lg"></i> Aplicar
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i> Sección en desarrollo...
            </div>

        </main>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    document.getElementById('exportarReporte').addEventListener('click', function() {
        exportarReporte();
    });

    document.getElementById('aplicarFiltros').addEventListener('click', function() {
        // Aquí puedes agregar lógica para aplicar filtros en la vista si es necesario
        mostrarFiltrosAplicados();
    });

    document.getElementById('limpiarFiltros').addEventListener('click', function() {
        document.getElementById('filtrosForm').reset();
    });

    async function exportarReporte() {
    try {
        // Mostrar loading
        const btn = document.getElementById('exportarReporte');
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Exportando...';
        btn.disabled = true;

        // Obtener valores de los filtros
        const fechaDesde = document.getElementById('fecha_desde').value;
        const fechaHasta = document.getElementById('fecha_hasta').value;

        // Construir URL con parámetros
        let url = '/auditoria/exportar?';
        const params = new URLSearchParams();
        
        if (fechaDesde) params.append('fecha_desde', fechaDesde);
        if (fechaHasta) params.append('fecha_hasta', fechaHasta);
        
        url += params.toString();

        const response = await fetch(url);
        if (!response.ok) {
            const errorData = await response.json();
            throw new Error(errorData.message || 'Error al exportar el reporte');
        }
        
        const blob = await response.blob();
        const urlBlob = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = urlBlob;
        
        // Nombre del archivo con timestamp
        const timestamp = new Date().toISOString().slice(0, 19).replace(/:/g, '-');
        a.download = `reporte_auditoria_${timestamp}.xlsx`;
        
        document.body.appendChild(a);
        a.click();
        a.remove();
        window.URL.revokeObjectURL(urlBlob);
        
    } catch (error) {
        console.error('Error al exportar el reporte:', error);
        alert('Error al exportar el reporte: ' + error.message);
    } finally {
        // Restaurar botón
        if (btn) {
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    }
}

    function mostrarFiltrosAplicados() {
        const fechaDesde = document.getElementById('fecha_desde').value;
        const fechaHasta = document.getElementById('fecha_hasta').value;
        
        let mensaje = 'Filtros aplicados: ';
        if (fechaDesde || fechaHasta) {
            mensaje += `Período ${fechaDesde || 'inicio'} a ${fechaHasta || 'fin'}`;
        } else {
            mensaje += 'Todos los registros';
        }
        
        // Aquí puedes mostrar los filtros aplicados o recargar una tabla
        console.log(mensaje);
    }
</script>
@endsection