<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\Registro;
use App\Models\User;


class RegistroController extends Controller
{
    public function createRegistro(Request $request)
    {
        $cpf = $request->cpf;
        $user = User::where('cpf', $cpf)->first();

        if ($user->timeout !== null) {
          // Checar timeout
          $atual = Carbon::now('America/Sao_Paulo');
          
          $timeout = Carbon::createFromFormat('d-m-Y H:i:s',
          Carbon::parse($user->timeout)->format('d-m-Y H:i:s'),
          'America/Sao_Paulo');

          if ($timeout->greaterThan($atual)) {
            return response()->json([
              'resultado' => 'timeout',
            ]);
          }
        }

        // Checar existência de registros.
        $data_atual = Carbon::now('America/Sao_Paulo')->format('Y-m-d');
        $ultimo_registro = Registro::where('cpf', $cpf)
          ->whereDate('data_hora', $data_atual)
          ->orderBy('data_hora', 'desc')
          ->first();
        
        // Registrar Entrada
        if ($ultimo_registro === null) {
          $registro = new Registro;
          
          $registro->cpf = $cpf;
          $registro->data_hora = Carbon::now('America/Sao_Paulo')->toDateTimeString();
          $registro->tipo = 'entrada';

          $image = request()->file('img');
          $upload = $image->store('uploadImg');
          $registro->img = $upload;
          $registro->save();

          $user->timeout = Carbon::now('America/Sao_Paulo')->addMinutes(30)->toDateTimeString();
          $user->save();

          return response()->json([
              'resultado' => 'ok',
              'tipo' => 'entrada'
          ], 200);
        }
        
        // Registrar Início do Intervalo
        if($ultimo_registro->tipo === 'entrada') {
          $registro = new Registro;
          
          $registro->cpf = $cpf;
          $registro->data_hora = Carbon::now('America/Sao_Paulo')->toDateTimeString();
          $registro->tipo = 'inicio-intervalo';

          $image = request()->file('img');
          $upload = $image->store('uploadImg');
          $registro->img = $upload;
          $registro->save();

          $user->timeout = Carbon::now('America/Sao_Paulo')->addMinutes(30)->toDateTimeString();
          $user->save();

          return response()->json([
              'resultado' => 'ok',
              'tipo' => 'inicio-intervalo'
          ], 200);

        }
        
        // Registrar Fim do Intervalo
        if ($ultimo_registro->tipo === 'inicio-intervalo') {
          $registro = new Registro;
          
          $registro->cpf = $cpf;
          $registro->data_hora = Carbon::now('America/Sao_Paulo')->toDateTimeString();
          $registro->tipo = 'fim-intervalo';

          $image = request()->file('img');
          $upload = $image->store('uploadImg');
          $registro->img = $upload;
          $registro->save();

          $user->timeout = Carbon::now('America/Sao_Paulo')->addMinutes(30)->toDateTimeString();
          $user->save();
          
          return response()->json([
            'resultado' => 'ok',
            'tipo' => 'fim-intervalo'
          ], 200);
        }

        // Registrar Saída
        if ($ultimo_registro->tipo === 'fim-intervalo') {
          $registro = new Registro;
          
          $registro->cpf = $cpf;
          $registro->data_hora = Carbon::now('America/Sao_Paulo')->toDateTimeString();
          $registro->tipo = 'saida';

          $image = request()->file('img');
          $upload = $image->store('uploadImg');
          $registro->img = $upload;
          $registro->save();

          $user->timeout = Carbon::now('America/Sao_Paulo')->addMinutes(30)->toDateTimeString();
          $user->save();
            
          return response()->json([
            'resultado' => 'ok',
            'tipo' => 'saida'
          ], 200);
        }

      // Todos os pontos foram preenchidos.
      return response()->json([
        'resultado' => 'complete',
      ]);
    }

    public function getRegistros(Request $request) {
      dd($request->query);
    }
}
