<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Variable extends Model
{
    use HasFactory;

    protected $table = 'variables';
    
    protected $fillable = [
        'codigo',
        'nombre',
        'descripcion',
        'unidad_medicion',
        'tipo_variable', // fisico-quimica, microbiologica, etc.
        'metodo_determinacion',
        'limite_minimo',
        'limite_maximo',
        'activo',
        'cotio_item_id'
    ];

    protected $casts = [
        'limite_minimo' => 'decimal:6',
        'limite_maximo' => 'decimal:6',
        'activo' => 'boolean'
    ];

    /**
     * Relación con leyes/normativas que usan esta variable
     */
    public function leyesNormativas()
    {
        return $this->belongsToMany(LeyNormativa::class, 'ley_normativa_variable')
                    ->withPivot('valor_limite', 'unidad_medida')
                    ->withTimestamps();
    }

    /**
     * Relación con CotioItems
     */
    public function cotioItem()
    {
        return $this->belongsTo(CotioItems::class, 'cotio_item_id', 'id');
    }

    /**
     * Relación con cotios que usan esta variable
     */
    public function cotios()
    {
        return $this->hasMany(Cotio::class, 'variable_codigo', 'codigo');
    }

    /**
     * Scope para variables activas
     */
    public function scopeActivas($query)
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
              ->orWhere('nombre', 'like', "%{$termino}%")
              ->orWhere('descripcion', 'like', "%{$termino}%");
        });
    }

    /**
     * Scope para filtrar por tipo
     */
    public function scopePorTipo($query, $tipo)
    {
        return $query->where('tipo_variable', $tipo);
    }

    /**
     * Obtener tipos únicos
     */
    public static function getTiposUnicos()
    {
        return static::distinct()->pluck('tipo_variable')->filter()->sort()->values();
    }
}
