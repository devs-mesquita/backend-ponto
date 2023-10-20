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

        if ($cpf === "sistema") {
          $tipo = $request->tipo;
          $data_registro = Carbon::parse($request->date)->midDay();

          $checa_registro = Registro::where('cpf', $cpf)
          ->whereDate('data_hora', $data_registro)
          ->first();

          if ($checa_registro !== null) {
            return response()->json([
              'resultado' => 'existente',
              'tipo' => $checa_registro->tipo,
            ], 401);
          }

          $registro = new Registro;

          $registro->cpf = $cpf;
          $registro->data_hora = $data_registro;
          $registro->tipo = $tipo;

          $registro->img = "sistema";
          $registro->save();

          return response()->json([
              'resultado' => 'ok',
              'tipo' => $tipo,
          ], 200);
        }

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
      $from = Carbon::parse($request->from)->startOfDay();
      $to = Carbon::parse($request->to)->endOfDay();
      $cpf = $request->cpf;

      $registros = Registro::whereBetween('data_hora', [$from, $to])
      ->where('cpf', $cpf)
      ->orWhere(function ($query) use ($from, $to) {
        return $query->whereBetween('data_hora', [$from, $to])
          ->whereIn('tipo', ['facultativo', 'feriado']);
      })
      ->orderBy('data_hora', 'asc')->get();

      return response()->json([
        'registros' => $registros
      ]);
    }

    public function deleteRegistro(Request $request) {
      $date = Carbon::parse($request->date);
      $cpf = $request->cpf;

      $deleted_registro = Registro::where('cpf', $cpf)->whereDate('data_hora', $date)->delete();

      return response()->json([
        'resultado' => 'ok'
      ], 200);
    }

    public function createFerias(Request $request)
    {
        $cpf = $request->cpf;
        $dates = $request->dates;
        $user = User::where('cpf', $cpf)->first();

        $date_start = Carbon::parse($dates[0])->startOfDay();
        $date_end = Carbon::parse($dates[count($dates)-1])->endOfDay();

        // Checa existência de registros de férias no período.
        /* $checa_registros = Registro::where([
            ['cpf', '=', $cpf],
            ['tipo', '=', 'ferias']
          ])->whereBetween('data_hora', [$date_start, $date_end])
          ->count();
        
        if ($checa_registro > 0) {
          return response()->json([
            'resultado' => 'existente',
            'tipo' => 'ferias',
          ], 401);
        } */

        foreach ($dates as $date) {
          $registro = Registro::firstOrCreate([
            'cpf' => $cpf,
            'data_hora' => Carbon::parse($date)->toDateTimeString(),
            'tipo' => 'ferias',
            'img' => "ferias"
          ], [
            'cpf' => $cpf,
            'data_hora' => Carbon::parse($date)->toDateTimeString(),
            'tipo' => 'ferias',
            'img' => "ferias"
          ]);
        }

        return response()->json([
            'resultado' => 'ok',
            'tipo' => 'ferias'
        ], 200);
    }
}
