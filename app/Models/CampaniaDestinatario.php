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
        'nombre',
        'codigo_pais',
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

    public function logs()
    {
        return $this->hasMany(LogEnvioMensajeria::class, 'destinatario_id');
    }

    public function marcarEnviado()
    {
        return $this->update(['estado_envio' => 'enviado']);
    }

    public function marcarError()
    {
        return $this->update(['estado_envio' => 'error']);
    }

    public function estaPendiente()
    {
        return $this->estado_envio === 'pendiente';
    }

    public function numeroLimpio()
    {
        return preg_replace('/\D/', '', $this->codigo_pais . $this->numero);
    }

    public function chatId()
    {
        return $this->numeroLimpio() . '@c.us';
    }
}
