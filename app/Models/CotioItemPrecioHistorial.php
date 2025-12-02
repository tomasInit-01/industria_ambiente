<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CotioItemPrecioHistorial extends Model
{
    protected $table = 'cotio_item_precio_historial';
    
    public $timestamps = false; // Usamos fecha_cambio en lugar de timestamps

    protected $fillable = [
        'operacion_id',
        'item_id',
        'precio_anterior',
        'precio_nuevo',
        'tipo_cambio',
        'valor_aplicado',
        'descripcion',
        'usuario_id',
        'revertido',
        'fecha_cambio',
        'fecha_reversion',
        'usuario_reversion_id'
    ];

    protected $casts = [
        'precio_anterior' => 'decimal:2',
        'precio_nuevo' => 'decimal:2',
        'valor_aplicado' => 'decimal:2',
        'revertido' => 'boolean',
        'fecha_cambio' => 'datetime',
        'fecha_reversion' => 'datetime',
    ];

    /**
     * Relación con el item
     */
    public function item()
    {
        return $this->belongsTo(CotioItems::class, 'item_id');
    }

    /**
     * Relación con el usuario que realizó el cambio
     */
    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id', 'usu_codigo');
    }

    /**
     * Relación con el usuario que revirtió el cambio
     */
    public function usuarioReversion()
    {
        return $this->belongsTo(User::class, 'usuario_reversion_id', 'usu_codigo');
    }

    /**
     * Scope para obtener cambios no revertidos
     */
    public function scopeNoRevertidos($query)
    {
        return $query->where('revertido', false);
    }

    /**
     * Scope para obtener cambios por operación
     */
    public function scopePorOperacion($query, $operacionId)
    {
        return $query->where('operacion_id', $operacionId);
    }
}
