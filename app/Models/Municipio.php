<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Municipio extends Model
{
    use HasFactory;

    protected $fillable = [
        'codigo',
        'nombre',
        'categoria',
        'centroide',
        'provincia_id',
    ];

    protected $casts = [
        'centroide' => 'array',
    ];

    public function provincia()
    {
        return $this->belongsTo(Provincia::class);
    }

    public function localidades()
    {
        return $this->hasMany(Localidad::class);
    }
}
