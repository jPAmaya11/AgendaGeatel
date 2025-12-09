<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class CampaniaSesion extends Model
{
    use HasFactory;

    protected $table = 'campania_sesiones';

    protected $fillable = [
        'campania_id',
        'nombre_sesion',
        'numero_bot',
        'estado',         // activo, inactivo, error
        'total_enviados',
        'total_errores',
        'ultimo_ping',
    ];

    protected $casts = [
        'ultimo_ping' => 'datetime',
    ];

    /* ============================================================
     * 🔹 RELACIONES
     * ============================================================ */
    public function campania()
    {
        return $this->belongsTo(Campania::class, 'campania_id');
    }

    /* ============================================================
     * 🔹 HELPERS ÚTILES
     * ============================================================ */

    // Número bot limpio
    public function numeroBotLimpio()
    {
        return preg_replace('/\D/', '', $this->numero_bot);
    }

    // chatId estilo WhatsApp
    public function chatIdBot()
    {
        return $this->numeroBotLimpio() . '@c.us';
    }

    // ¿La sesión está activa?
    public function estaActiva()
    {
        return $this->estado === 'activo';
    }

    // Ping hace menos de 1 minuto
    public function estaConectada()
    {
        if (!$this->ultimo_ping) {
            return false;
        }

        return $this->ultimo_ping->greaterThan(Carbon::now()->subMinutes(1));
    }

    // Para dashboards o monitores
    public function tiempoDesdePing()
    {
        return $this->ultimo_ping
            ? $this->ultimo_ping->diffForHumans()
            : 'Nunca';
    }

    // Incrementar enviados
    public function sumarEnviado()
    {
        $this->increment('total_enviados');
    }

    // Incrementar errores
    public function sumarError()
    {
        $this->increment('total_errores');
    }

    // Actualizar ping
    public function actualizarPing()
    {
        $this->update(['ultimo_ping' => now()]);
    }
}
