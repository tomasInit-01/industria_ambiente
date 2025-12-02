<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoCliente extends Model
{
    protected $table = 'tcli';
    protected $primaryKey = 'tcli_codigo';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'tcli_codigo',
        'tcli_descripcion'
    ];

    public function clientes()
    {
        return $this->hasMany(Clientes::class, 'cli_codigotcli', 'tcli_codigo');
    }
}

