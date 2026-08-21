<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class RegistroExpositor extends Model
{
    protected $table = 'registros_expositores';
    public $timestamps = false;
    protected $fillable = ['nombre_empresa', 'categoria', 'nombre_contacto', 'correo', 'telefono', 'descripcion'];
}
