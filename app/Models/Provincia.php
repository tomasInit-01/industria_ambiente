<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Provincia extends Model
{
    use HasFactory;

    protected $fillable = [
        'codigo',
        'nombre',
        'iso_nombre',
        'centroide',
    ];

    protected $casts = [
        'centroide' => 'array',
    ];

    public function municipios()
    {
        return $this->hasMany(Municipio::class);
    }

    public function localidades()
    {
        return $this->hasMany(Localidad::class);
    }
}
