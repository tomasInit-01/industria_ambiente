<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CotioInventarioMuestreo extends Model
{
    protected $table = 'cotio_inventario_muestreo';
    public $timestamps = false;
    
    // Especifica que no hay clave primaria autoincremental
    protected $primaryKey = null;
    public $incrementing = false;

    protected $fillable = [
        'cotio_numcoti',
        'cotio_item',
        'cotio_subitem',
        'instance_number',
        'inventario_muestreo_id',
        'cantidad',
        'observaciones',
    ];

    public function herramienta()
    {
        return $this->belongsTo(InventarioMuestreo::class, 'inventario_muestreo_id');
    }

    
    public function instancia()
    {
        return $this->belongsTo(CotioInstancia::class, [
            'cotio_numcoti', 
            'cotio_item', 
            'cotio_subitem', 
            'instance_number'
        ], [
            'cotio_numcoti', 
            'cotio_item', 
            'cotio_subitem', 
            'instance_number'
        ]);
    }
}