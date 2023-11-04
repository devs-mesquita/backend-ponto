<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setor extends Model
{
    use HasFactory;

    protected $table = 'setores';

    protected $fillable = [
        'nome',
        'cnpj',
        'cnae',
        'empresa',
        'visto_fiscal',

        'logradouro',
        'numero_logradouro',
        'complemento',
        'bairro',
        'cidade',
        'uf',
        'cep',

        'soma_entrada',
        'soma_saida',
    ];

    public function users()
    {
        return $this->hasMany('App\Models\User', 'setor_id'); 
    }
}
