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
        'obligatorio'
    ];

    protected $casts = [
        'obligatorio' => 'boolean',
    ];

}
