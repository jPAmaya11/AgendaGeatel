<?php

namespace App\Models;

use Spatie\Permission\Models\Role as SpatieRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Cartera;
use App\Models\Reporte;

class Role extends SpatieRole
{
    use HasFactory;

    // Valor por defecto para guard_name si lo usas en la DB
    protected $attributes = [
        'guard_name' => 'web',
    ];

    // Permitir asignar 'guard_name' en mass assignment
    protected $fillable = [
        'name',
        'guard_name',
    ];

    public function carteras()
    {
        return $this->belongsToMany(Cartera::class, 'role_cartera');
    }

    public function reportes()
    {
        return $this->belongsToMany(Reporte::class, 'role_reporte');
    }
}
