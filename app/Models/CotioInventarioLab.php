<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CotioInventarioLab extends Model
{
    protected $table = 'cotio_inventario_lab';
    public $timestamps = false;


    protected $primaryKey = ['cotio_numcoti', 'cotio_item', 'cotio_subitem', 'instance_number', 'inventario_lab_id'];
    public $incrementing = false;

    protected $fillable = [
        'cotio_numcoti',
        'cotio_item',
        'cotio_subitem',
        'inventario_lab_id',
        'cantidad',
        'observaciones',
    ];

    public function herramienta()
    {
        return $this->belongsTo(InventarioLab::class, 'inventario_lab_id');
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
