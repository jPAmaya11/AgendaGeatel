<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogRespuestaCliente extends Model
{
    use HasFactory;

    protected $table = 'log_respuestas_clientes';

    protected $fillable = [
        'campania_id',
        'numero_cliente',
        'numero_bot',
        'tipo_wsp_cliente',
        'formato_mensaje',
        'mensaje',
        'fecha_registro'
    ];

    protected $casts = [
        'fecha_registro' => 'datetime'
    ];

    public function campania()
    {
        return $this->belongsTo(Campania::class);
    }
}
