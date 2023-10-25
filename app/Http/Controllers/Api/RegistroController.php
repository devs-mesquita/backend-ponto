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
          if(!in_array(auth()->user()->nivel(), ['Super-Admin', 'Admin'])) {
            return response()->json(['message' => 'Unauthorized.'], 402);
          }

          $tipo = $request->tipo;
          $data_registro = Carbon::parse($request->date)->startOfDay();

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

        if ($request->tipo === "falta") {
          if(!in_array(auth()->user()->nivel, ['Super-Admin', 'Admin'])) {
            return response()->json(['message' => 'Unauthorized.'], 402);
          }

          $tipo = $request->tipo;
          $data_registro = Carbon::parse($request->date)->startOfDay();

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

        if(auth()->user()->setor->nome !== "PONTO") {
          return response()->json(['resultado' => 'error', 'message' => 'Unauthorized.'], 402);
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

        $data_atual = Carbon::now('America/Sao_Paulo')->format('Y-m-d');

        // Checar existência de feriado.
        $feriado = Registro::where('tipo', 'feriado')
        ->whereDate('data_hora', $data_atual)
        ->first();

        if($feriado !== null) {
          return response()->json([
            'resultado' => 'feriado',
        ], 200);
        }

        // Checar existência de registros.
        $ultimo_registro = Registro::where('cpf', $cpf)
        ->whereDate('data_hora', $data_atual)
          ->orderBy('data_hora', 'desc')
          ->first();

        if($ultimo_registro?->tipo === 'ferias') {
          return response()->json([
              'resultado' => 'ferias',
          ], 200);
        }

        if($ultimo_registro?->tipo === 'falta') {
          return response()->json([
              'resultado' => 'falta',
          ], 200);
        }

        if($ultimo_registro?->tipo === 'atestado') {
          return response()->json([
              'resultado' => 'atestado',
          ], 200);
        }
        
        // Registrar Entrada
        if ($ultimo_registro === null || $ultimo_registro?->tipo === "facultativo" ) {
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

      $user = User::with('setor')->where('cpf', $cpf)->first();

      return response()->json([
        'registros' => $registros,
        'user' => $user
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

        foreach ($dates as $date) {
          $registro = Registro::firstOrCreate([
            'cpf' => $cpf,
            'data_hora' => Carbon::parse($date)->startOfDay()->toDateTimeString(),
            'tipo' => 'ferias',
            'img' => "ferias"
          ], [
            'cpf' => $cpf,
            'data_hora' => Carbon::parse($date)->startOfDay()->toDateTimeString(),
            'tipo' => 'ferias',
            'img' => "ferias"
          ]);
        }

        return response()->json([
            'resultado' => 'ok',
            'tipo' => 'ferias'
        ], 200);
    }

    public function setorUsersWithRegistros(Request $request) {
      $setorUsers = User::with('setor')
        ->where('setor_id', $request->setor_id)
        ->get();

      $CPFArray = User::where('setor_id', $request->setor_id)
        ->pluck('cpf')
        ->toArray();

      $from = Carbon::parse($request->from)->startOfDay();
      $to = Carbon::parse($request->to)->endOfDay();

      $feriados = Registro::where('cpf', 'sistema')
        ->whereBetween('data_hora', [$from, $to])
        ->orderBy('data_hora', 'asc')
        ->get();

      $setorRegistros = Registro::whereIn('cpf', $CPFArray)
        ->whereBetween('data_hora', [$from, $to])
        ->orderBy('cpf', 'asc')
        ->orderBy('data_hora', 'asc')
        ->get();

      return response()->json([
        'users' => $setorUsers,
        'setorRegistros' => $setorRegistros,
        'feriados' => $feriados,
      ], 200);
    }
}
