<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Localidad extends Model
{
    use HasFactory;

    protected $table = 'localidades'; // aquí va el nombre real de tu tabla


    protected $fillable = [
        'codigo',
        'nombre',
        'categoria',
        'centroide',
        'provincia_id',
        'municipio_id',
    ];

    protected $casts = [
        'centroide' => 'array',
    ];

    public function provincia()
    {
        return $this->belongsTo(Provincia::class);
    }

    public function municipio()
    {
        return $this->belongsTo(Municipio::class);
    }
}
