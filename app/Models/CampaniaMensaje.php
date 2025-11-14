<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CampaniaMensaje extends Model
{
    use HasFactory;

    protected $table = 'campania_mensajes';

    protected $fillable = [
        'campania_id',
        'mensaje',
        'tipo_mensaje',
        'url_archivo',
    ];

    protected $casts = [
        'mensaje' => 'string',
    ];

    public function campania()
    {
        return $this->belongsTo(Campania::class, 'campania_id');
    }

    public function esTexto()
    {
        return $this->tipo_mensaje === 'texto';
    }

    public function esArchivo()
    {
        return in_array($this->tipo_mensaje, ['imagen', 'video', 'documento']);
    }
}
