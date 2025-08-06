<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VariableRequerida extends Model
{
    protected $table = 'variables_requeridas';

    protected $fillable = [
        'cotio_id',
        'cotio_descripcion',
        'nombre',
        'obligatorio',
        'unidad_medicion'
    ];

    protected $casts = [
        'obligatorio' => 'boolean',
    ];

}
