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
          // Feriados e Pontos Facultativos
          if(!in_array(auth()->user()?->nivel, ['Super-Admin', 'Admin'])) {
            return response()->json(['resultado' => 'unauthorized.'], 402);
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
          $registro->creator_id = auth()->user()?->id;

          $imgPath = null;
          $image = request()->file('img');
          if ($image) {
            $upload = $image->store('public/uploadImg');
            $imgPath = "storage/".substr($upload, 7);
          }
          $registro->img = $imgPath;

          $registro->save();

          return response()->json([
              'resultado' => 'ok',
              'tipo' => $tipo,
          ], 200);
        }

        if ($request->tipo === "abono") {
          if(!in_array(auth()->user()->nivel, ['Super-Admin', 'Admin'])) {
            return response()->json(['resultado' => 'unauthorized.'], 402);
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
          $registro->creator_id = auth()->user()?->id;

          $imgPath = null;
          $image = request()->file('img');
          if ($image) {
            $upload = $image->store('public/uploadImg');
            $imgPath = "storage/".substr($upload, 7);
          }
          $registro->img = $imgPath;

          $registro->save();

          return response()->json([
              'resultado' => 'ok',
              'tipo' => $tipo,
          ], 200);
        }

        if ($request->tipo === "falta") {
          if(!in_array(auth()->user()->nivel, ['Super-Admin', 'Admin'])) {
            return response()->json(['resultado' => 'unauthorized.'], 402);
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
          $registro->creator_id = auth()->user()?->id;

          $imgPath = null;
          $image = request()->file('img');
          if ($image) {
            $upload = $image->store('public/uploadImg');
            $imgPath = "storage/".substr($upload, 7);
          }
          $registro->img = $imgPath;

          $registro->save();

          return response()->json([
              'resultado' => 'ok',
              'tipo' => $tipo,
          ], 200);
        }

        // entrada, inicio/fim-intervalo, saida.
        if(auth()->user()->setor->nome !== "TERMINAL") {
          return response()->json(['resultado' => 'unauthorized.'], 402);
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

        // Restringir marcação em feriado/facultativo.
        $feriado_facultativo = Registro::whereIn('tipo', ['feriado', 'facultativo'])
        ->whereDate('data_hora', $data_atual)
        ->first();

        if($feriado_facultativo !== null) {
          return response()->json([
            'resultado' => $feriado_facultativo->tipo,
        ], 200);
        }

        // Checar existência de férias/falta/abono.
        $ultimo_registro = Registro::where('cpf', $cpf)
        ->whereDate('data_hora', $data_atual)
          ->orderBy('data_hora', 'desc')
          ->first();

        // Restringir marcação em férias/falta/abono.
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

        if($ultimo_registro?->tipo === 'abono') {
          return response()->json([
              'resultado' => 'abono',
          ], 200);
        }
        
        // Registrar Entrada
        if ($ultimo_registro === null) {
          $registro = new Registro;
          
          $registro->cpf = $cpf;
          $registro->data_hora = Carbon::now('America/Sao_Paulo')->toDateTimeString();
          $registro->tipo = 'entrada';
          $registro->creator_id = auth()->user()?->id;

          $imgPath = null;
          $image = request()->file('img');
          if ($image === null) {
            return response()->json([
              "resultado" => "missing-img"
            ], 400);
          }

          $upload = $image->store('public/uploadImg');
          $imgPath = "storage/".substr($upload, 7);
          $registro->img = $imgPath;

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
          $registro->creator_id = auth()->user()?->id;

          $imgPath = null;
          $image = request()->file('img');
          if ($image === null) {
            return response()->json([
              "resultado" => "missing-img"
            ], 400);
          }

          $upload = $image->store('public/uploadImg');
          $imgPath = "storage/".substr($upload, 7);
          $registro->img = $imgPath;

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
          $registro->creator_id = auth()->user()?->id;

          $imgPath = null;
          $image = request()->file('img');
          if ($image === null) {
            return response()->json([
              "resultado" => "missing-img"
            ], 400);
          }

          $upload = $image->store('public/uploadImg');
          $imgPath = "storage/".substr($upload, 7);
          $registro->img = $imgPath;

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
          $registro->creator_id = auth()->user()?->id;

          $imgPath = null;
          $image = request()->file('img');
          if ($image === null) {
            return response()->json([
              "resultado" => "missing-img"
            ], 400);
          }

          $upload = $image->store('public/uploadImg');
          $imgPath = "storage/".substr($upload, 7);
          $registro->img = $imgPath;

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
      $dates = json_decode($request->dates);
      $user = User::where('cpf', $cpf)->first();

      $imgPath = null;
      $image = request()->file('img');
      
      if ($image) {
        $upload = $image->store('public/uploadImg');
        $imgPath = "storage/".substr($upload, 7);
      }

      foreach ($dates as $date) {
        Registro::updateOrCreate([
          'cpf' => $cpf,
          'data_hora' => $date,
          'tipo' => 'ferias',
        ], [
          'cpf' => $cpf,
          'data_hora' => $date,
          'tipo' => 'ferias',
          'img' => $imgPath,
          'creator_id' => auth()->user()?->id,
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

    public function confirmRegistroCreate (Request $request)
    {
      $cpf = $request->cpf;
      $user = User::with('setor')->where('cpf', $cpf)->first();

      if ($user->timeout !== null) {
        // Checar timeout
        $atual = Carbon::now('America/Sao_Paulo');
        
        $timeout = Carbon::createFromFormat('d-m-Y H:i:s',
        Carbon::parse($user->timeout)->format('d-m-Y H:i:s'),
        'America/Sao_Paulo');

        if ($timeout->greaterThan($atual)) {
          return response()->json([
            'resultado' => 'timeout',
          ], 400);
        }
      }

      $data_atual = Carbon::now('America/Sao_Paulo')->format('Y-m-d');

      // Restringir marcação em feriado.
      $feriado_facultativo = Registro::whereIn('tipo', ['feriado', 'facultativo'])
      ->whereDate('data_hora', $data_atual)
      ->first();

      if($feriado_facultativo !== null) {
        return response()->json([
          'resultado' => $feriado_facultativo->tipo,
      ], 200);
      }

      // Checar existência de férias/falta/abono.
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

      if($ultimo_registro?->tipo === 'abono') {
        return response()->json([
            'resultado' => 'abono',
        ], 200);
      }
      
      // Entrada
      if ($ultimo_registro === null) {
        return response()->json([
            'resultado' => 'ok',
            'user' => $user,
            'tipo' => 'entrada'
        ], 200);
      }
      
      // Início do Intervalo
      if($ultimo_registro->tipo === 'entrada') {
        return response()->json([
            'resultado' => 'ok',
            'user' => $user,
            'tipo' => 'inicio-intervalo'
        ], 200);

      }
      
      // Fim do Intervalo
      if ($ultimo_registro->tipo === 'inicio-intervalo') {
        return response()->json([
          'resultado' => 'ok',
          'user' => $user,
          'tipo' => 'fim-intervalo'
        ], 200);
      }

      // Saída
      if ($ultimo_registro->tipo === 'fim-intervalo') {
        return response()->json([
          'resultado' => 'ok',
          'user' => $user,
          'tipo' => 'saida'
        ], 200);
      }

      // Todos os pontos foram preenchidos.
      return response()->json([
        'resultado' => 'complete',
      ], 400);
    }
}
