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
        'waha_sesion_nombre',
        'numero_bot',
        'numero_cliente',
        'mensaje_enviado',
        'estado',
        'fecha_envio',
        'fecha_entregado',
        'fecha_leido',
    ];

    protected $casts = [
        'fecha_envio' => 'datetime',
        'fecha_entregado' => 'datetime',
        'fecha_leido' => 'datetime',
    ];

    /* ============================================================
     * 🔹 RELACIONES
     * ============================================================ */

    public function campania()
    {
        return $this->belongsTo(Campania::class);
    }

    public function destinatario()
    {
        return $this->belongsTo(CampaniaDestinatario::class, 'destinatario_id');
    }

    /* ============================================================
     * 🔹 MÉTODOS AUXILIARES (opcional, pero útil)
     * ============================================================ */

    public function marcarComoEnviado()
    {
        $this->update([
            'estado' => 'enviado',
            'fecha_envio' => now(),
        ]);
    }

    public function marcarComoEntregado()
    {
        $this->update([
            'estado' => 'entregado',
            'fecha_entregado' => now(),
        ]);
    }

    public function marcarComoLeido()
    {
        $this->update([
            'estado' => 'leido',
            'fecha_leido' => now(),
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
