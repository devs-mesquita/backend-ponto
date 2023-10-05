<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Setor;

class SetoresController extends Controller
{
    public function index()
    {
        $setor = Setor::all();

        return response()->json([
            'setores' => $setor,
        ],200);
    }

    // public function create()
    // {
    // }

    public function store(Request $request)
    {

        // dd($request->nome);
        $setor = new Setor;

        $setor->nome          = $request->nome;
        $setor->soma_saida    = $request->soma_saida;
        $setor->soma_entrada  = $request->soma_entrada;

        $setor->save();

        return response()->json([
            'resultado' => 'ok',
            ]
        );

    }
}
