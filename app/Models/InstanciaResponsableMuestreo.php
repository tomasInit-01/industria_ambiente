<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class InstanciaResponsableMuestreo extends Pivot
{
    protected $table = 'instancia_responsable_muestreo';

    // Indicate that there is no auto-incrementing ID
    public $incrementing = false;

    // Define the composite key (optional, for clarity)
    protected $primaryKey = ['cotio_instancia_id', 'usu_codigo'];

    protected $fillable = [
        'cotio_instancia_id',
        'usu_codigo',
        'created_at',
        'updated_at',
    ];

    // Cast timestamps correctly
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}