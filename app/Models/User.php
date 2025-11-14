<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
        'active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'active' => 'boolean',
    ];

    /* -------------------------------------------------
     * Relaciones existentes (Sistema de Carteras/Reportes)
     * -------------------------------------------------*/
    public function reportes()
    {
        return $this
            ->belongsToMany(Reporte::class, 'reporte_user')
            ->with('cartera');
    }

    public function carteras()
    {
        return $this
            ->belongsToMany(Cartera::class, 'cartera_user');
    }

    public function getEffectiveCarteras()
    {
        return $this->roles->flatMap->carteras
            ->merge($this->carteras)
            ->unique('id')
            ->filter(fn($cartera) => $cartera->estado)
            ->values();
    }

    public function getEffectiveReportes()
    {
        return $this->roles->flatMap->reportes
            ->merge($this->reportes)
            ->unique('id')
            ->values();
    }

    /* -------------------------------------------------
     * Relaciones del módulo de Mensajería Masiva
     * -------------------------------------------------*/

    // Un usuario puede crear muchas campañas
    public function campanias()
    {
        return $this->hasMany(Campania::class);
    }

    // Un usuario puede tener acceso a varias sesiones WAHA
    public function wahaSesiones()
    {
        return $this->belongsToMany(WahaSesion::class, 'waha_sesion_user')
            ->withPivot(['permitido', 'asignado_en'])
            ->wherePivot('permitido', true);
    }


    // Logs de envíos (según las campañas creadas)
    public function logEnvios()
    {
        return $this->hasManyThrough(
            LogEnvioMensajeria::class,
            Campania::class,
            'user_id', // Clave foránea en campañas
            'campania_id', // Clave foránea en logs
            'id', // Clave local en users
            'id'  // Clave local en campañas
        );
    }

    //  Logs de respuestas (de clientes que responden)
    public function logRespuestas()
    {
        return $this->hasManyThrough(
            LogRespuestaCliente::class,
            Campania::class,
            'user_id',
            'campania_id',
            'id',
            'id'
        );
    }

    /* -------------------------------------------------
     * Scopes
     * -------------------------------------------------*/
    public function scopeActivos($query)
    {
        return $query->where('active', true);
    }

    public function scopeInactivos($query)
    {
        return $query->where('active', false);
    }
}
