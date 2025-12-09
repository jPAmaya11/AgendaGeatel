<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;           // ← NECESARIO
use App\Models\WahaConexion;   // ← NECESARIO

class ConexionUsuarioFiltro extends Model
{
    use HasFactory;

    protected $table = 'conexion_usuario_filtros';

    protected $fillable = [
        'user_id',
        'waha_conexion_id',
        'filtro'
    ];

    protected $casts = [
        'user_id' => 'integer',
        'waha_conexion_id' => 'integer',
    ];

    /* ============================================================
     * RELACIONES
     * ============================================================ */

    // Usuario asignado al filtro
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Conexión WAHA asociada
    public function wahaConexion()
    {
        return $this->belongsTo(WahaConexion::class, 'waha_conexion_id');
    }
}
