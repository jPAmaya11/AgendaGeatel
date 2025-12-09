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
        'phone',
        'password',
        'active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'google_token_expires_at'=> 'datetime',
        'password' => 'hashed',
        'active' => 'boolean',
    ];

    /* -------------------------------------------------
     * Relaciones existentes (Sistema de Carteras/Reportes)
     * -------------------------------------------------*/
    public function reportes()
    {
        return $this->belongsToMany(Reporte::class, 'reporte_user')
            ->with('cartera');
    }

    public function carteras()
    {
        return $this->belongsToMany(Cartera::class, 'cartera_user');
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

    // Campañas creadas por el usuario
    public function campanias()
    {
        return $this->hasMany(Campania::class);
    }

    // 🔹 Acceso a conexiones WAHA (desde tabla conexion_usuario_filtros)
    public function wahaConexiones()
    {
        return $this->belongsToMany(
            WahaConexion::class,
            'conexion_usuario_filtros',
            'user_id',
            'waha_conexion_id'
        )->withPivot('filtro')
         ->withTimestamps();
    }
    // Logs de envíos (según campañas creadas)
    public function logEnvios()
    {
        return $this->hasManyThrough(
            LogEnvioMensajeria::class,
            Campania::class,
            'user_id',
            'campania_id',
            'id',
            'id'
        );
    }

    // Logs de respuestas de clientes
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

    public function events()
    {
        return $this->hasMany(Event::class);
    }
}
