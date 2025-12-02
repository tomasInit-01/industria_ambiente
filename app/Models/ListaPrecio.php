<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ListaPrecio extends Model
{
    protected $table = 'lp';
    protected $primaryKey = 'lp_codigo';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'lp_codigo',
        'lp_descripcion',
        'lp_estado'
    ];

    protected $casts = [
        'lp_estado' => 'boolean'
    ];

    public function clientes()
    {
        return $this->hasMany(Clientes::class, 'cli_codigolp', 'lp_codigo');
    }
}

