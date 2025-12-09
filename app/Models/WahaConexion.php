<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class WahaConexion extends Model
{
    use HasFactory;

    protected $table = 'waha_conexiones';

    protected $fillable = [
        'nombre',
        'host',
        'token_api',
        'estado',
        'ultimo_ping',
        'user_id_admin'
    ];

    protected $casts = [
        'ultimo_ping' => 'datetime'
    ];

    /* ============================================================
     * RELACIONES
     * ============================================================ */

    // Usuario administrador de la conexión
    public function admin()
    {
        return $this->belongsTo(User::class, 'user_id_admin');
    }

    // Filtros asignados a esta conexión
    public function filtros()
    {
        return $this->hasMany(ConexionUsuarioFiltro::class, 'waha_conexion_id');
    }

    // Usuarios asociados vía filtros
    public function usuarios()
    {
        return $this->belongsToMany(
            User::class,
            'conexion_usuario_filtros',
            'waha_conexion_id',
            'user_id'
        )->withPivot('filtro')
         ->withTimestamps();
    }

    /* ============================================================
     * TOKEN API — CIFRADO
     * ============================================================ */

    public function setTokenApiAttribute($value)
    {
        if ($value) {
            $this->attributes['token_api'] = Crypt::encryptString($value);
        }
    }

    public function getTokenApiAttribute($value)
    {
        return $value ? Crypt::decryptString($value) : null;
    }
}
