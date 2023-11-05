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
      
      $setor->empresa          = $empresa;
      $setor->cnpj          = $cnpj;
      $setor->cnae          = $cnae;
      $setor->visto_fiscal          = $visto_fiscal;
      
      $setor->logradouro          = $logradouro;
      $setor->numero_logradouro          = $numero_logradouro;
      $setor->complemento          = $complemento;
      $setor->bairro          = $bairro;
      $setor->cidade          = $cidade;
      $setor->uf          = $uf;
      $setor->cep          = $cep;

      $setor->soma_saida    = $request->soma_saida;
      $setor->soma_entrada  = $request->soma_entrada;
      $setor->save();

      return response()->json([
          'resultado' => 'ok',
          ]
      );
    }

    public function update(Request $request)
    {
      $setor = Setor::find($request->setor_id);

      if ($setor === null) {
        return response()->json([
          'resultado' => 'not-found'
        ], 404);
      }

      $setor->nome          = $request->nome;

      $setor->empresa          = $request->empresa;
      $setor->cnpj          = $request->cnpj;
      $setor->cnae          = $request->cnae;
      $setor->visto_fiscal          = $request->visto_fiscal;

      $setor->logradouro          = $request->logradouro;
      $setor->numero_logradouro          = $request->numero_logradouro;
      $setor->complemento          = $request->complemento;
      $setor->bairro          = $request->bairro;
      $setor->cidade          = $request->cidade;
      $setor->uf          = $request->uf;
      $setor->cep          = $request->cep;

      $setor->soma_saida    = $request->soma_saida;
      $setor->soma_entrada  = $request->soma_entrada;

      $setor->save();

      return response()->json([
        'resultado' => 'ok',
      ]);
    }
}
