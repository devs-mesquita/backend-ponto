<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Registro extends Model
{
    use HasFactory;

    protected $table = 'registros';

    protected $fillable = [
        // 'nome',
        'cpf',
        'data',
        'entrada',
        'entrada_img',
        'entrada_alm',
        'entrada_alm_img',        
        'volta_alm',
        'volta_alm_img',
        'saida',
        'saida_img',
        'timeout'
    ];
}
