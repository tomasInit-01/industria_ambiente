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

    .form-control,
    .form-select {
        border: 1px solid #ced4da;
        border-radius: 0.25rem;
        font-size: 0.875rem;
    }

    .form-control:focus,
    .form-select:focus {
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

    /* Estilos para resaltar ensayos */
    .table tbody tr[data-tipo="ensayo"] {
        background-color: #e7f3ff;
        font-weight: 600;
        border-top: 2px solid #0d6efd;
    }

    .table tbody tr[data-tipo="ensayo"] td {
        border-top: 2px solid #0d6efd;
    }

    /* Estilos para componentes (indentación) */
    .table tbody tr[data-tipo="componente"] {
        background-color: #ffffff;
    }

    .table tbody tr[data-tipo="componente"] td:nth-child(3) {
        padding-left: 2rem;
        position: relative;
    }

    .table tbody tr[data-tipo="componente"] td:nth-child(3)::before {
        content: "└─ ";
        position: absolute;
        left: 0.5rem;
        color: #6c757d;
        font-weight: bold;
    }

    /* Inputs en tabla */
    .table tbody input[type="number"] {
        min-width: 70px;
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

    .cotizacion-loading-overlay {
        position: fixed;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: rgba(255, 255, 255, 0.85);
        z-index: 2000;
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.2s ease-in-out, visibility 0.2s ease-in-out;
    }

    .cotizacion-loading-overlay.is-visible {
        opacity: 1;
        visibility: visible;
    }

    .tabla-items-bloqueada {
        opacity: 0.6;
        pointer-events: none;
    }

    /* Resumen de componentes seleccionados */
    .componentes-resumen-card {
        border: 1px dashed #ced4da;
        border-radius: 0.5rem;
        padding: 0.75rem;
        background-color: #f8f9fa;
        max-height: 230px;
        overflow-y: auto;
    }

    .componentes-resumen-item + .componentes-resumen-item {
        border-top: 1px solid #e9ecef;
        margin-top: 0.5rem;
        padding-top: 0.5rem;
    }

    .componentes-resumen-item .componentes-resumen-meta {
        font-size: 0.78rem;
    }

    .campo-multi-disabled {
        background-color: #f1f3f5;
        cursor: not-allowed;
    }

    .select2-results__option .componente-option-title {
        font-weight: 600;
        font-size: 0.9rem;
    }

    .select2-results__option .componente-option-meta {
        font-size: 0.78rem;
    }

</style>

