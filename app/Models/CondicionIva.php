<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CondicionIva extends Model
{
    protected $table = 'civa';
    protected $primaryKey = 'civa_codigo';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'civa_codigo',
        'civa_descripcion',
        'civa_abreviada',
        'civa_codigotasaiva',
        'civa_codigotasaacre',
        'civa_codigotasaimp',
        'civa_codigotasaexento',
        'civa_codigotasapercepcion',
        'civa_codigotasapercepiibb',
        'civa_codigotasapercepgcia',
        'civa_tipo',
        'civa_letra',
        'civa_cuit',
        'civa_estado',
        'civa_codigoretpatronal'
    ];

    protected $casts = [
        'civa_cuit' => 'boolean',
        'civa_estado' => 'boolean'
    ];

    public function clientes()
    {
        return $this->hasMany(Clientes::class, 'cli_codigociva', 'civa_codigo');
    }
}
