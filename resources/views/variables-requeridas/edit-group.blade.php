@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 font-weight-bold">Editar Grupo: {{ $groupName }}</h1>
        <a href="{{ route('variables-requeridas.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left mr-1"></i> Volver
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ route('variables-requeridas.update-group', urlencode($groupName)) }}" method="POST">
                @csrf
                @method('PUT')
                
                <input type="hidden" name="group_name" value="{{ $groupName }}">
                <input type="hidden" name="cotio_id" value="{{ $cotioId }}">

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                {{-- <th>Unidad de Medición</th> --}}
                                <th>Obligatorio</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($variables as $variable)
                            <tr>
                                <td>{{ $variable->id }}</td>
                                <td>
                                    <input type="hidden" name="variables[{{ $loop->index }}][id]" 
                                           value="{{ $variable->id }}">
                                    <input type="text" 
                                           name="variables[{{ $loop->index }}][nombre]" 
                                           value="{{ old('variables.'.$loop->index.'.nombre', $variable->nombre) }}" 
                                           class="form-control form-control-sm" required>
                                </td>
                                {{-- <td>
                                    <input type="text" 
                                           name="variables[{{ $loop->index }}][unidad_medicion]" 
                                           value="{{ old('variables.'.$loop->index.'.unidad_medicion', $variable->unidad_medicion) }}" 
                                           class="form-control form-control-sm" placeholder="Unidad de Medición">
                                </td> --}}
                                <td>
                                    <select name="variables[{{ $loop->index }}][obligatorio]" 
                                            class="form-select form-select-sm" required>
                                        <option value="1" {{ $variable->obligatorio ? 'selected' : '' }}>Sí</option>
                                        <option value="0" {{ !$variable->obligatorio ? 'selected' : '' }}>No</option>
                                    </select>
                                </td>
                                <td>
                                    <span class="text-muted">Variable existente</span>
                                </td>
                            </tr>
                            @endforeach
                            
                            <!-- Fila para nueva variable -->
                            <tr class="table-light new-variable-row">
                                <td>Nueva</td>
                                <td>
                                    <input type="text" 
                                           name="new_variables[0][nombre]" 
                                           class="form-control form-control-sm new-variable-nombre" 
                                           placeholder="Nombre de la nueva variable">
                                </td>
                                {{-- <td>
                                    <input type="text" 
                                           name="new_variables[0][unidad_medicion]" 
                                           class="form-control form-control-sm new-variable-unidad-medicion" 
                                           placeholder="Unidad de Medición">
                                </td> --}}
                                <td>
                                    <select name="new_variables[0][obligatorio]" class="form-select form-select-sm">
                                        <option value="1">Sí</option>
                                        <option value="0">No</option>
                                    </select>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-outline-success add-variable">
                                        <x-heroicon-o-plus style="width: 16px; height: 16px;"/>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-end mt-4">
                    <button type="submit" class="btn btn-primary">
                        Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        let rowCount = 1;
        
        // Agregar nueva fila
        document.querySelector('.add-variable').addEventListener('click', function() {
            const newRow = `
                <tr class="table-light new-variable-row">
                    <td>Nueva</td>
                    <td>
                        <input type="text" 
                               name="new_variables[${rowCount}][nombre]" 
                               class="form-control form-control-sm new-variable-nombre" 
                               placeholder="Nombre de la nueva variable">
                    </td>
                    <td>
                        <input type="text" 
                               name="new_variables[${rowCount}][unidad_medicion]" 
                               class="form-control form-control-sm new-variable-unidad-medicion" 
                               placeholder="Unidad de Medición">
                    </td>
                    <td>
                        <select name="new_variables[${rowCount}][obligatorio]" 
                                class="form-select form-select-sm">
                            <option value="1">Sí</option>
                            <option value="0">No</option>
                        </select>
                    </td>
                    <td>
                        <button type="button" class="btn btn-sm btn-outline-danger remove-variable">
                            <x-heroicon-o-trash style="width: 16px; height: 16px;"/>
                        </button>
                    </td>
                </tr>
            `;
            
            // Insertar antes de la última fila (que es la fila de "agregar")
            const lastRow = document.querySelector('.new-variable-row:last-child');
            lastRow.insertAdjacentHTML('beforebegin', newRow);
            rowCount++;
        });

        // Eliminar fila
        $(document).on('click', '.remove-variable', function() {
            $(this).closest('tr').remove();
        });

        // Validación del formulario antes de enviar
        document.querySelector('form').addEventListener('submit', function(e) {
            const newVariableInputs = document.querySelectorAll('.new-variable-nombre');
            let hasEmptyFields = false;
            
            newVariableInputs.forEach(input => {
                if (input.value.trim() === '') {
                    input.classList.add('is-invalid');
                    hasEmptyFields = true;
                } else {
                    input.classList.remove('is-invalid');
                }
            });
            
            if (hasEmptyFields) {
                e.preventDefault();
                alert('Por favor, completa todos los campos de nuevas variables o elimina las filas vacías.');
                return false;
            }
        });
    });
</script>

@endsection