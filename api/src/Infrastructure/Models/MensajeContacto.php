<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class MensajeContacto extends Model
{
    protected $table = 'mensajes_contacto';
    public $timestamps = false;
    protected $fillable = ['nombre', 'correo', 'telefono', 'mensaje'];
}
