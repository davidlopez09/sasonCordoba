<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class RegistroVisitante extends Model
{
    protected $table = 'registros_visitantes';
    public $timestamps = false; // asumiendo que no tiene timestamps o se manejan a nivel DB
    protected $fillable = ['nombre', 'correo', 'telefono'];
}
