<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventarioLab extends Model
{
    use HasFactory;

    protected $table = 'inventario_lab';

    protected $fillable = [
        'equipamiento',
        'marca_modelo',
        'n_serie_lote',
        'codigo_ficha',
        'observaciones',
        'activo',
        'estado',
        'fecha_calibracion',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public $timestamps = true;

    /**
     * Relación con CotioInstancia a través de la tabla pivot cotio_inventario_lab
     */
    public function cotioInstancias(): BelongsToMany
    {
        return $this->belongsToMany(
            CotioInstancia::class,
            'cotio_inventario_lab',
            'inventario_lab_id',
            'cotio_instancia_id'
        )->withPivot(['cantidad', 'observaciones']);
    }

    /**
     * Relación directa con la tabla pivot (para consultas avanzadas)
     */
    public function cotioInventarioLab(): HasMany
    {
        return $this->hasMany(CotioInventarioLab::class, 'inventario_lab_id');
    }

    /**
     * Relación con Cotio (opcional - mantener si es necesaria)
     */
    public function tareas(): BelongsToMany
    {
        return $this->belongsToMany(
            Cotio::class,
            'cotio_inventario_lab',
            'inventario_lab_id',
            'cotio_numcoti'
        )->withPivot(['cotio_item', 'cotio_subitem', 'cantidad', 'observaciones']);
    }
}