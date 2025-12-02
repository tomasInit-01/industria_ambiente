<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MetodoAnalisis extends Model
{
    use HasFactory;

    protected $table = 'metodos_analisis';
    
    protected $fillable = [
        'codigo',
        'nombre',
        'descripcion',
        'equipo_requerido',
        'procedimiento',
        'unidad_medicion',
        'limite_deteccion_default',
        'limite_cuantificacion_default',
        'costo_base',
        'tiempo_estimado_horas',
        'requiere_calibracion',
        'activo'
    ];

    protected $casts = [
        'limite_deteccion_default' => 'decimal:6',
        'limite_cuantificacion_default' => 'decimal:6',
        'costo_base' => 'decimal:2',
        'tiempo_estimado_horas' => 'integer',
        'requiere_calibracion' => 'boolean',
        'activo' => 'boolean'
    ];

    /**
     * Relación con cotios que usan este método de análisis
     */
    public function cotios()
    {
        return $this->hasMany(Cotio::class, 'cotio_codigometodo_analisis', 'codigo');
    }

    /**
     * Scope para métodos activos
     */
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    /**
     * Scope para métodos que requieren calibración
     */
    public function scopeRequierenCalibracion($query)
    {
        return $query->where('requiere_calibracion', true);
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

    /**
     * Accessor para tiempo estimado formateado
     */
    public function getTiempoEstimadoFormateadoAttribute()
    {
        if (!$this->tiempo_estimado_horas) {
            return '-';
        }
        
        if ($this->tiempo_estimado_horas == 1) {
            return '1 hora';
        }
        
        if ($this->tiempo_estimado_horas < 24) {
            return $this->tiempo_estimado_horas . ' horas';
        }

        $dias = floor($this->tiempo_estimado_horas / 24);
        $horas = $this->tiempo_estimado_horas % 24;
        
        return $dias . ' días' . ($horas > 0 ? ' y ' . $horas . ' horas' : '');
    }
}
