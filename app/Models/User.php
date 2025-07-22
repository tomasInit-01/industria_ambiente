<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    protected $table = 'usu';
    protected $primaryKey = 'usu_codigo';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $hidden = [
        'usu_clave',
    ];

    protected $fillable = [
        'usu_codigo', 'usu_descripcion', 'usu_clave', 'usu_nivel', 'usu_estado', 'sector_codigo'
    ];

    public function getAuthPassword()
    {
        return $this->usu_clave;
    }

    public function tareas()
    {
        return $this->hasMany(Cotio::class, 'cotio_responsable_codigo', 'usu_codigo');
    }

    public function cotios()
    {
        return $this->belongsToMany(Cotio::class, 'cotio_user', 'usu_codigo', 'cotio_numcoti');
    }

    public function sector()
    {
        return $this->belongsTo(User::class, 'sector_codigo', 'usu_codigo');
    }

    public function miembros()
    {
        return $this->hasMany(User::class, 'sector_codigo', 'usu_codigo');
    }

}