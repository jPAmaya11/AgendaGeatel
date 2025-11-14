<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CampaniaDestinatario extends Model
{
    use HasFactory;

    protected $table = 'campania_destinatarios';

    protected $fillable = [
        'campania_id',
        'codigo_pais',
        'nombre',
        'numero',
        'variables_json',
        'estado_envio',
    ];

    protected $casts = [
        'variables_json' => 'array',
    ];

    public function campania()
    {
        return $this->belongsTo(Campania::class, 'campania_id');
    }

    public function log()
    {
        return $this->hasMany(LogEnvioMensajeria::class, 'destinatario_id');
    }

    /* ============================================================
     * MÉTODOS AUXILIARES
     * ============================================================ */
    public function marcarEnviado()
    {
        $this->update(['estado_envio' => 'enviado']);
    }

    public function marcarError()
    {
        $this->update(['estado_envio' => 'error']);
    }

    public function chatId()
    {
        return preg_replace('/\D/', '', $this->numero) . '@c.us';
    }
}
