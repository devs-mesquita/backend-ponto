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

    public function show($id)
    {
      $setor = Setor::find($id);

      if ($setor === null) {
        return response()->json([
          "resultado" => "not-found"
        ], 404);
      }

      return response()->json([
        "setor" => $setor,
      ]);
    }

    public function store(Request $request)
    {
      $checa_setor = Setor::where('nome', mb_strtoupper($request->nome))->count();
      if ($checa_setor > 0) {
        return response()->json([
          'resultado' => 'existent'
        ], 400);
      }

      $setor = new Setor;
      $setor->nome          = mb_strtoupper($request->nome);
      
      $setor->empresa          = mb_strtoupper($request->empresa);
      $setor->cnpj          = $request->cnpj;
      $setor->cnae          = $request->cnae;
      $setor->visto_fiscal          = $request->visto_fiscal;
      
      $setor->logradouro          = mb_strtoupper($request->logradouro);
      $setor->numero_logradouro          = $request->numero_logradouro;
      $setor->complemento          = mb_strtoupper($request->complemento);
      $setor->bairro          = mb_strtoupper($request->bairro);
      $setor->cidade          = mb_strtoupper($request->cidade);
      $setor->uf          = mb_strtoupper($request->uf);
      $setor->cep          = $request->cep;

      $setor->soma_saida    = $request->soma_saida;
      $setor->soma_entrada  = $request->soma_entrada;
      $setor->save();

      return response()->json([
        'resultado' => 'ok',
      ]);
    }

    public function update(Request $request)
    {
      $setor = Setor::find($request->setor_id);

      if ($setor === null) {
        return response()->json([
          'resultado' => 'not-found'
        ], 404);
      }

      $setor->nome          = mb_strtoupper($request->nome);

      $setor->empresa          = mb_strtoupper($request->empresa);
      $setor->cnpj          = $request->cnpj;
      $setor->cnae          = $request->cnae;
      $setor->visto_fiscal          = $request->visto_fiscal;

      $setor->logradouro          = mb_strtoupper($request->logradouro);
      $setor->numero_logradouro          = $request->numero_logradouro;
      $setor->complemento          = mb_strtoupper($request->complemento);
      $setor->bairro          = mb_strtoupper($request->bairro);
      $setor->cidade          = mb_strtoupper($request->cidade);
      $setor->uf          = mb_strtoupper($request->uf);
      $setor->cep          = $request->cep;

      $setor->soma_saida    = $request->soma_saida;
      $setor->soma_entrada  = $request->soma_entrada;

      $setor->save();

      return response()->json([
        'resultado' => 'ok',
      ]);
    }
}
