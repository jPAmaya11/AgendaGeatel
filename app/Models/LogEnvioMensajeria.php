<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogEnvioMensajeria extends Model
{
    use HasFactory;

    protected $table = 'log_envios_mensajeria';

    protected $fillable = [
        'campania_id',
        'destinatario_id',
        'sesion_waha',
        'numero_bot',
        'codigo_pais_cliente',
        'numero_cliente',
        'mensaje_enviado',
        'estado',
        'fecha_envio',
    ];

    protected $casts = [
        'fecha_envio' => 'datetime',
    ];

    public function campania()
    {
        return $this->belongsTo(Campania::class);
    }

    public function destinatario()
    {
        return $this->belongsTo(CampaniaDestinatario::class, 'destinatario_id');
    }

    public function marcarComoEnviado()
    {
        $this->update([
            'estado' => 'enviado',
            'fecha_envio' => now(),
        ]);
    }

    public function marcarComoError($mensaje = null)
    {
        $this->update([
            'estado' => 'error',
            'mensaje_enviado' => $mensaje ?? $this->mensaje_enviado,
        ]);
    }
}
