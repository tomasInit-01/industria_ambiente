<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;


class InventarioMuestreo extends Model
{
    use HasFactory;

    protected $table = 'inventario_muestreo';

    protected $fillable = [
        'equipamiento',
        'marca_modelo',
        'n_serie_lote',
        'observaciones',
        'activo',
        'fecha_calibracion',
        'certificado',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public $timestamps = true;

    public function tareas()
    {
        return $this->belongsToMany(
            Cotio::class,
            'cotio_inventario_muestreo',
            'inventario_muestreo_id',
            'cotio_numcoti'
        )->withPivot('cotio_item', 'cotio_subitem', 'cantidad', 'observaciones');
    }

    public function cotioInstancias(): BelongsToMany
    {
        return $this->belongsToMany(
            CotioInstancia::class,
            'cotio_inventario_muestreo',
            'inventario_muestreo_id',
            'cotio_instancia_id'
        )->withPivot(['cantidad', 'observaciones']);
    }

}