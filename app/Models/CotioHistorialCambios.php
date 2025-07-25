<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class CotioHistorialCambios extends Model
{
    protected $table = 'cotio_historial_cambios';
    
    protected $fillable = [
        'tabla_afectada',
        'registro_id',
        'campo_modificado',
        'valor_anterior',
        'valor_nuevo',
        'usuario_id',
        'fecha_cambio',
        'ip_origen',
        'accion'
    ];

    public $timestamps = false; // Usamos fecha_cambio en lugar de created_at/updated_at

    protected $casts = [
        'fecha_cambio' => 'datetime',
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id', 'usu_codigo');
    }
}
