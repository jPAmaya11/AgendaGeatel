<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Campania extends Model
{
    use HasFactory;

    protected $table = 'campanias';

    protected $fillable = [
        'user_id',
        'nombre',
        'descripcion',
        'estado',
        'fecha_inicio',
        'fecha_fin',
        'total_destinatarios',
        'total_enviados',
        'total_pendientes',
        'total_errores',
        'usar_retraso_lote',
        'retraso_lote_min',
        'retraso_lote_max',
        'retraso_lote_cada',
        'usar_retraso_mensaje',
        'retraso_mensaje_min',
        'retraso_mensaje_max',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'total_destinatarios' => 'integer',
        'total_enviados' => 'integer',
        'total_pendientes' => 'integer',
        'total_errores' => 'integer',
        'usar_retraso_lote' => 'boolean',
        'usar_retraso_mensaje' => 'boolean',
        'fecha_inicio' => 'datetime',
        'fecha_fin' => 'datetime',
    ];

    /* RELACIONES */

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function sesiones()
    {
        return $this->hasMany(CampaniaSesion::class, 'campania_id');
    }

    public function mensajes()
    {
        return $this->hasMany(CampaniaMensaje::class, 'campania_id');
    }

    public function destinatarios()
    {
        return $this->hasMany(CampaniaDestinatario::class, 'campania_id');
    }

    public function logsEnvios()
    {
        return $this->hasMany(LogEnvioMensajeria::class, 'campania_id');
    }

    public function respuestasClientes()
    {
        return $this->hasMany(LogRespuestaCliente::class, 'campania_id');
    }

    /* SCOPES */

    public function scopeActivas($query)
    {
        return $query->where('estado', 'activa');
    }

    public function scopePendientes($query)
    {
        return $query->where('estado', 'pendiente');
    }
}
