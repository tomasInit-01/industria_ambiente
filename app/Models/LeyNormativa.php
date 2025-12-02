<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class LeyNormativa extends Model
{
    use HasFactory;

    protected $table = 'leyes_normativas';
    
    protected $fillable = [
        'codigo',
        'nombre',
        'grupo',
        'articulo',
        'descripcion',
        'variables_aplicables',
        'organismo_emisor',
        'fecha_vigencia',
        'fecha_actualizacion',
        'observaciones',
        'activo'
    ];

    protected $casts = [
        'fecha_vigencia' => 'date',
        'fecha_actualizacion' => 'date',
        'activo' => 'boolean'
    ];

    /**
     * Relación con cotios que usan esta normativa
     */
    public function cotios()
    {
        return $this->hasMany(Cotio::class, 'ley_aplicacion', 'codigo');
    }

    /**
     * Relación con variables asociadas a esta ley/normativa
     */
    public function variables()
    {
        return $this->belongsToMany(Variable::class, 'ley_normativa_variable')
                    ->withPivot('valor_limite', 'unidad_medida')
                    ->withTimestamps();
    }

    /**
     * Scope para normativas activas
     */
    public function scopeActivas($query)
    {
        return $query->where('activo', true);
    }

    /**
     * Scope para filtrar por grupo
     */
    public function scopePorGrupo($query, $grupo)
    {
        return $query->where('grupo', $grupo);
    }

    /**
     * Scope para buscar por código, nombre o grupo
     */
    public function scopeBuscar($query, $termino)
    {
        return $query->where(function($q) use ($termino) {
            $q->where('codigo', 'like', "%{$termino}%")
              ->orWhere('nombre', 'like', "%{$termino}%")
              ->orWhere('grupo', 'like', "%{$termino}%")
              ->orWhere('articulo', 'like', "%{$termino}%");
        });
    }

    /**
     * Scope para normativas vigentes
     */
    public function scopeVigentes($query)
    {
        return $query->where('fecha_vigencia', '<=', Carbon::now())
                    ->where('activo', true);
    }

    /**
     * Accessor para obtener las variables como array
     */
    public function getVariablesAplicablesArrayAttribute()
    {
        if (!$this->variables_aplicables) {
            return [];
        }

        return array_map('trim', explode(',', $this->variables_aplicables));
    }

    /**
     * Mutator para guardar las variables como string
     */
    public function setVariablesAplicablesArrayAttribute($value)
    {
        if (is_array($value)) {
            $this->attributes['variables_aplicables'] = implode(', ', $value);
        }
    }

    /**
     * Accessor para obtener el nombre completo con artículo
     */
    public function getNombreCompletoAttribute()
    {
        return $this->articulo ? $this->articulo . ' - ' . $this->nombre : $this->nombre;
    }

    /**
     * Método para verificar si la normativa está vigente
     */
    public function estaVigente()
    {
        return $this->activo && 
               $this->fecha_vigencia && 
               $this->fecha_vigencia->lte(Carbon::now());
    }

    /**
     * Método para obtener grupos únicos
     */
    public static function getGruposUnicos()
    {
        return self::activas()
                   ->distinct()
                   ->pluck('grupo')
                   ->sort()
                   ->values();
    }
}
