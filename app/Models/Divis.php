<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Divis extends Model
{
    protected $table = 'divis';
    protected $primaryKey = 'divis_codigo';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'divis_codigo',
        'divis_descripcion',
        'divis_lab'
    ];

    protected $casts = [
        'divis_lab' => 'boolean'
    ];

    public function cotizaciones()
    {
        return $this->hasMany(Ventas::class, 'coti_sector', 'divis_codigo');
    }

    public function matrices()
    {
        return $this->hasMany(Matriz::class, 'matriz_tmuestra', 'divis_codigo');
    }
}

