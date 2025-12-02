@extends('layouts.app')

@section('title', 'Cambios Masivos de Precios')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">Cambios Masivos de Precios</h1>
        <div>
            <a href="{{ route('items.historial-precios') }}" class="btn btn-outline-info">Ver Historial</a>
            <a href="{{ route('items.index') }}" class="btn btn-outline-secondary">Volver</a>
        </div>
    </div>

    @if(session('success'))
        <div id="flash-success" data-message="{{ session('success') }}" style="display:none"></div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('items.aplicar-cambios-masivos') }}" id="formCambiosMasivos">
                @csrf

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="tipo_cambio" class="form-label">Tipo de Cambio <span class="text-danger">*</span></label>
                        <select name="tipo_cambio" id="tipo_cambio" class="form-select @error('tipo_cambio') is-invalid @enderror" required>
                            <option value="">Seleccione...</option>
                            <option value="porcentaje" {{ old('tipo_cambio') == 'porcentaje' ? 'selected' : '' }}>Porcentaje (%)</option>
                            <option value="valor_fijo" {{ old('tipo_cambio') == 'valor_fijo' ? 'selected' : '' }}>Valor Fijo</option>
                        </select>
                        @error('tipo_cambio')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">
                            <span id="help-porcentaje" style="display:none;">Ejemplo: 10 = aumentar 10%, -5 = disminuir 5%</span>
                            <span id="help-valor_fijo" style="display:none;">Ejemplo: 50 = sumar $50, -30 = restar $30</span>
                        </small>
                    </div>

                    <div class="col-md-6">
                        <label for="valor" class="form-label">Valor <span class="text-danger">*</span></label>
                        <input type="number" 
                               name="valor" 
                               id="valor" 
                               step="0.01" 
                               class="form-control @error('valor') is-invalid @enderror" 
                               value="{{ old('valor') }}" 
                               required
                               min="-999999"
                               max="999999">
                        @error('valor')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">
                            <span id="unidad-valor"></span>
                        </small>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="filtro_tipo" class="form-label">Filtrar por Tipo</label>
                        <select name="filtro_tipo" id="filtro_tipo" class="form-select">
                            <option value="todos" {{ old('filtro_tipo', 'todos') == 'todos' ? 'selected' : '' }}>Todos</option>
                            <option value="muestras" {{ old('filtro_tipo') == 'muestras' ? 'selected' : '' }}>Solo Agrupadores (Muestras)</option>
                            <option value="componentes" {{ old('filtro_tipo') == 'componentes' ? 'selected' : '' }}>Solo Componentes</option>
                        </select>
                        <small class="form-text text-muted">Solo se actualizarán ítems que tengan precio definido</small>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="descripcion" class="form-label">Descripción (Opcional)</label>
                    <textarea name="descripcion" 
                              id="descripcion" 
                              class="form-control @error('descripcion') is-invalid @enderror" 
                              rows="3" 
                              maxlength="500"
                              placeholder="Ej: Aumento por inflación, Actualización de precios 2025, etc.">{{ old('descripcion') }}</textarea>
                    @error('descripcion')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="alert alert-warning">
                    <strong>⚠️ Advertencia:</strong> Esta operación modificará los precios de múltiples determinaciones. 
                    Los cambios quedarán registrados en el historial y podrán ser revertidos.
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('items.index') }}" class="btn btn-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-primary" id="btnAplicar">
                        Aplicar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const flash = document.getElementById('flash-success');
        if (flash && flash.dataset.message) {
            Swal.fire({
                icon: 'success',
                title: 'Éxito',
                text: flash.dataset.message,
                timer: 3000,
                showConfirmButton: false
            });
        }

        const tipoCambio = document.getElementById('tipo_cambio');
        const valor = document.getElementById('valor');
        const unidadValor = document.getElementById('unidad-valor');
        const helpPorcentaje = document.getElementById('help-porcentaje');
        const helpValorFijo = document.getElementById('help-valor_fijo');

        function actualizarAyuda() {
            const tipo = tipoCambio.value;
            helpPorcentaje.style.display = tipo === 'porcentaje' ? 'inline' : 'none';
            helpValorFijo.style.display = tipo === 'valor_fijo' ? 'inline' : 'none';
            
            if (tipo === 'porcentaje') {
                unidadValor.textContent = 'Ingrese el porcentaje (puede ser negativo para disminuir)';
            } else if (tipo === 'valor_fijo') {
                unidadValor.textContent = 'Ingrese el valor a sumar/restar (puede ser negativo)';
            } else {
                unidadValor.textContent = '';
            }
        }

        tipoCambio.addEventListener('change', actualizarAyuda);
        actualizarAyuda();

        document.getElementById('formCambiosMasivos').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const tipo = tipoCambio.value;
            const valorInput = parseFloat(valor.value);
            
            if (!tipo || isNaN(valorInput)) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Por favor complete todos los campos requeridos.'
                });
                return;
            }

            let mensaje = '';
            if (tipo === 'porcentaje') {
                mensaje = `¿Está seguro de aplicar un cambio del ${valorInput > 0 ? '+' : ''}${valorInput}% a los precios?`;
            } else {
                mensaje = `¿Está seguro de ${valorInput >= 0 ? 'sumar' : 'restar'} $${Math.abs(valorInput)} a los precios?`;
            }

            Swal.fire({
                title: '¿Confirmar cambios masivos?',
                text: mensaje,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, aplicar cambios',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#d33'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.submit();
                }
            });
        });
    });
    </script>
</div>
@endsection

