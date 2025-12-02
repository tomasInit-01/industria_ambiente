<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MetodoMuestreo extends Model
{
    use HasFactory;

    protected $table = 'metodos_muestreo';
    
    protected $fillable = [
        'codigo',
        'nombre',
        'descripcion',
        'equipo_requerido',
        'procedimiento',
        'unidad_medicion',
        'costo_base',
        'activo'
    ];

    protected $casts = [
        'costo_base' => 'decimal:2',
        'activo' => 'boolean'
    ];

    /**
     * Relación con cotios que usan este método de muestreo
     */
    public function cotios()
    {
        return $this->hasMany(Cotio::class, 'cotio_codigometodo', 'codigo');
    }

    /**
     * Scope para métodos activos
     */
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    /**
     * Scope para buscar por código o nombre
     */
    public function scopeBuscar($query, $termino)
    {
        return $query->where(function($q) use ($termino) {
            $q->where('codigo', 'like', "%{$termino}%")
              ->orWhere('nombre', 'like', "%{$termino}%");
        });
    }
}
