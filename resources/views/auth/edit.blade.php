@extends('layouts.app')

@section('content')



<div class="container">
    <form action="{{ route('auth.update', $user->usu_codigo) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="row g-3">
            <div class="col-md-6">
                <label for="usu_descripcion" class="form-label">Nombre</label>
                <input type="text" class="form-control" id="usu_descripcion" name="usu_descripcion" value="{{ $user->usu_descripcion }}" required>
            </div>

            <div class="col-md-6">
                <label for="usu_codigo" class="form-label">Código</label>
                <input type="text" class="form-control" id="usu_codigo" name="usu_codigo" value="{{ $user->usu_codigo }}" readonly>
            </div>

            @if(Auth::user()->usu_nivel >= 900)
                <div class="col-md-6">
                    <label for="usu_nivel" class="form-label">Nivel</label>
                    <input type="number" class="form-control" id="usu_nivel" name="usu_nivel" value="{{ $user->usu_nivel }}">
                </div>

                <div class="col-md-6">
                    <label for="usu_estado" class="form-label">Estado</label>
                    <select class="form-select" id="usu_estado" name="usu_estado">
                        <option value="1" {{ $user->usu_estado ? 'selected' : '' }}>Activo</option>
                        <option value="0" {{ !$user->usu_estado ? 'selected' : '' }}>Inactivo</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label for="usu_rol" class="form-label">Rol</label>
                    <input type="text" class="form-control" id="usu_rol" name="rol" value="{{ $user->rol }}">
                </div>
            @endif

            <div class="col-md-6">
                <label for="usu_clave" class="form-label">Contraseña (opcional)</label>
                <input type="password" class="form-control" id="usu_clave" name="usu_clave">
            </div>
        </div>

        <div class="mt-4 d-flex justify-content-end">
            <button type="submit" class="btn btn-success">
                <i class="bi bi-check-circle me-1"></i> Guardar Cambios
            </button>
        </div>
    </form>
</div>
@endsection
