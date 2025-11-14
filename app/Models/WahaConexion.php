<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    // Relación opcional con el administrador
    public function admin()
    {
        return $this->belongsTo(User::class, 'user_id_admin');
    }

    // Obtener campañas asociadas (por sesiones)
    public function campanias()
    {
        return $this->hasMany(Campania::class, 'waha_sesiones', 'nombre');
    }
}
