<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Registro extends Model
{
    use HasFactory;

    protected $fillable = [
        'chip_id',
        'estado_id',
        'usuario_id',
        'conteo_cambios',
        'fecha_revision'
    ];

    // Relación con chip
    public function chip()
    {
        return $this->belongsTo(Chip::class);
    }

    // Relación con estado
    public function estado()
    {
        return $this->belongsTo(Estado::class);
    }

    // Relación con usuario
    public function usuario()
    {
        return $this->belongsTo(Usuario::class);
    }
}
