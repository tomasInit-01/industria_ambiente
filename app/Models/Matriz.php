<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Matriz extends Model
{
    protected $table = 'matriz';
    protected $primaryKey = 'matriz_codigo';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'matriz_codigo',
        'matriz_descripcion',
        'matriz_tmuestra'
    ];

    public function cotizaciones()
    {
        return $this->hasMany(Ventas::class, 'coti_codigomatriz', 'matriz_codigo');
    }

    public function divis()
    {
        return $this->belongsTo(Divis::class, 'matriz_tmuestra', 'divis_codigo');
    }
}