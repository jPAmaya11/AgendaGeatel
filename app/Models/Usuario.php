<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Usuario extends Model
{
    use HasFactory;

    protected $table = 'usuarios'; // por si acaso, ya que Laravel por defecto buscaría 'usuarios'
    
    protected $fillable = [
        'nombre',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
    ];
     public function registros()
    {
        return $this->hasMany(Registro::class);
    }
}
