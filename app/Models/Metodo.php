<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Metodo extends Model
{
    use HasFactory;

    protected $table = 'metodo';
    protected $primaryKey = 'metodo_codigo';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;
    
    protected $fillable = [
        'metodo_codigo',
        'metodo_descripcion',
    ];

}
