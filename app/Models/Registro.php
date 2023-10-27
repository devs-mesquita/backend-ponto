<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Registro extends Model
{
    use HasFactory;

    protected $table = 'registros';

    protected $fillable = [
        'cpf',
        'data',
        'img',
        'tipo',
        'data_hora',
        'creator_id'
    ];

    public function creator()
    {
        return $this->belongsTo('App\Models\User','creator_id');
    }
}
