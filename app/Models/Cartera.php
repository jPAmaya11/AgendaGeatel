<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cartera extends Model
{
    protected $fillable = [
        'nombre',
        'descripcion',
        'orden',
        'estado',
    ];

    public function reportes()
    {
        return $this->hasMany(Reporte::class);
    }
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_cartera');
    }
}
