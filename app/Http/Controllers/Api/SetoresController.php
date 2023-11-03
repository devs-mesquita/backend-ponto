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
      ], 200);
    }

    public function store(Request $request)
    {
      $nome = mb_strtoupper($request->nome);

      $checa_setor = Setor::where('nome', $nome)->count();
      if ($checa_setor > 0) {
        return response()->json([
          'resultado' => 'existent'
        ], 400);
      }

      $setor = new Setor;
      $setor->nome          = $nome;
      $setor->soma_saida    = $request->soma_saida;
      $setor->soma_entrada  = $request->soma_entrada;
      $setor->save();

      return response()->json([
          'resultado' => 'ok',
          ]
      );
    }
}
