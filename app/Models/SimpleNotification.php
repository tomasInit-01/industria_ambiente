<?php

// app/Models/SimpleNotification.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SimpleNotification extends Model
{
    protected $fillable = [
        'coordinador_codigo', // receptor
        'sender_codigo',       // emisor (nuevo campo)
        'instancia_id',
        'mensaje',
        'leida'
    ];

    public function coordinador()
    {
        return $this->belongsTo(User::class, 'coordinador_codigo', 'usu_codigo');
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_codigo', 'usu_codigo');
    }

    public function instancia()
    {
        return $this->belongsTo(CotioInstancia::class, 'instancia_id');
    }
}