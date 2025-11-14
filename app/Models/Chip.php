<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chip extends Model
{
    use HasFactory;

    protected $fillable = [
        'numero',
        'compania_id',
        'tiene_senal',
        'bloqueado'
    ];

    // Relación con compañía
    public function compania()
    {
        return $this->belongsTo(Compania::class);
    }

    // Relación con registros
    public function registros()
    {
        return $this->hasMany(Registro::class);
    }
}
