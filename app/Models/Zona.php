<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Zona extends Model
{
    protected $table = 'zon';
    protected $primaryKey = 'zon_codigo';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'zon_codigo',
        'zon_descripcion',
        'zon_abreviada',
        'zon_estado'
    ];

    protected $casts = [
        'zon_estado' => 'boolean'
    ];

    public function clientes()
    {
        return $this->hasMany(Clientes::class, 'cli_codigozon', 'zon_codigo');
    }
}

