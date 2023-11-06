<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Carbon;

class AuthController extends Controller
{
  public function login(Request $request)
  {
      $request->validate([
          'email' => 'required|string|email',
          'password' => 'required|string',
      ]);
      $credentials = $request->only('email', 'password');
      $token = Auth::attempt($credentials);
      
      if (!$token) {
          return response()->json([
              'resultado' => 'unauthorized',
          ], 401);
      }

      //$user = Auth::user();
      $user = User::with('setor')->find(Auth::id());
      return response()->json([
          'user' => $user,
          'default_password' => Hash::check(config('app.user_default_password', ''), $user->password),
          'authorization' => [
              'token' => $token,
              'type' => 'bearer',
              'expires_in' => 60 * 24 * 365.25
          ]
      ]);
  }

  public function register(Request $request)
  {
      if (User::where('cpf', $request->cpf)->count() > 0) {
        return response()->json([
          'resultado' => 'cpf-existente',
        ], 400);
      }

      if (User::where('email', $request->email)->count() > 0) {
        return response()->json([
          'resultado' => 'email-existente',
        ], 400);
      }

      $user = User::create([
          'name' => $request->name,
          'email' => $request->email,
          'password' => Hash::make(config('app.user_default_password', '')),
          'cpf' => $request->cpf,
          'nivel' => $request->nivel,

          'matricula' => $request->matricula,
          'pispasep' => $request->pispasep,
          'ctps' => $request->ctps,
          'data_admissao' => $request->data_admissao ? Carbon::parse($request->data_admissao)->toDateTimeString() : null,
          'cargo' => mb_strtoupper($request->cargo),
          'lotacao' => mb_strtoupper($request->lotacao),
          'repouso' => json_encode($request->repouso),

          'setor_id' => $request->setor_id,
      ]);

      return response()->json([
          'resultado' => 'created',
          'user' => $user
      ]);
  }

  public function showUser($id)
  {
    $user = User::with('setor')->find($id);

    if ($user === null) {
      return response()->json([
        "resultado" => "not-found"
      ], 404);
    }

    return response()->json([
      "user" => $user,
    ]);
  }

  public function updateUser(Request $request)
  {
    $user = User::find($request->user_id);

    if ($user === null) {
      return response()->json([
        'resultado' => 'not-found',
      ], 404);
    }

    if (User::where('cpf', $request->cpf)->where('id', '!=', $request->user_id)->count() > 0) {
      return response()->json([
        'resultado' => 'cpf-existente',
      ], 400);
    }

    if (User::where('email', $request->email)->where('id', '!=', $request->user_id)->count() > 0) {
      return response()->json([
        'resultado' => 'email-existente',
      ], 400);
    }

    $user->name = $request->name;
    $user->email = $request->email;
    $user->cpf = $request->cpf;

    $user->matricula = $request->matricula;
    $user->pispasep = $request->pispasep;
    $user->ctps = $request->ctps;
    $user->data_admissao = $request->data_admissao ? Carbon::parse($request->data_admissao)->toDateTimeString() : null;
    $user->cargo = mb_strtoupper($request->cargo);
    $user->lotacao = mb_strtoupper($request->lotacao);
    $user->repouso = json_encode($request->repouso);

    $user->save();

    return response()->json([
        'resultado' => 'created',
        'user' => $user
    ]);
  }

  public function logout()
  {
      Auth::logout();
      return response()->json([
          'resultado' => 'ok',
      ]);
  }

  public function refresh()
  {
      return response()->json([
          'user' => User::with('setor')->find(Auth::id()),
          'authorization' => [
              'token' => auth()->refresh(),
              'type' => 'bearer',
              'expires_in' => 60 * 24 * 365.25
          ]
      ]);
  }

  public function getUsersBySetor($setor)
  {
    $users = User::with('setor')
    ->where('setor_id', $setor)
    ->orderBy('name', 'asc')
    ->get();

    return response()->json([
      'users' => $users
    ]);
  }

  public function resetPassword(Request $request) {
    $user = User::with('setor')->find($request?->user_id);

    if ($user->nivel === "Super-Admin" && in_array(auth()->user()->nivel(), ["Admin", "User"])) {
      return response()->json([
        'resultado' => 'unauthorized',
      ], 403);
    }

    if ($user === null) {
      return response()->json([
        'resultado' => 'not-found',
      ], 400);
    }
    
    $user->password = Hash::make(config('app.user_default_password', ''));
    
    if(auth()->user()->setor_id !== $user->setor_id && auth()->user()->nivel !== 'Super-Admin') {
      return response()->json([
        'resultado' => 'unauthorized'
      ], 403);
    }

    $user->save();

    return response()->json([
      'resultado' => 'ok',
    ], 200);
  }

  public function changeNivel(Request $request) {
    $user = User::with('setor')->find($request?->user_id);

    if ($user === null) {
      return response()->json([
        'resultado' => 'not-found',
      ], 400);
    }

    $user->nivel = $request->nivel;
    $user->save();

    return response()->json([
      'resultado' => 'ok',
    ], 200);
  }

  public function changeSetor(Request $request) {
    $user = User::find($request?->user_id);

    if ($user === null) {
      return response()->json([
        'resultado' => 'not-found',
      ], 400);
    }

    $user->setor_id = $request->setor_id;
    $user->save();

    return response()->json([
      'resultado' => 'ok',
    ], 200);
  }

  public function changePassword(Request $request) {
    if ($request->newPassword !== $request->confirmPassword) {
      return response()->json([
        'resultado' => 'wrong-confirm-password',
      ], 400);
    }
    
    $user = User::with('setor')->find(auth()->user()->id);

    if ($user === null) {
      return response()->json([
        'resultado' => 'not-found',
      ], 400);
    }

    if (!Hash::check($request->currentPassword, auth()->user()->password)) {
      return response()->json([
        'resultado' => 'wrong-current-password',
      ], 403);
    }

    $user->password = Hash::make($request->newPassword);
    $user->save();

    return response()->json([
      'resultado' => 'ok',
    ], 200);
  }

  public function checkDefaultPassword(Request $request)
  {
    $user = User::find(auth()?->user()?->id);
      
    if ($user === null) {
      return response()->json([
        'resultado' => 'not-found',
      ]);
    }

    if (Hash::check(config('app.user_default_password', ''), $user->password)) {
      return response()->json([
        'resultado' => 'default-password',
      ]);
    }

    return response()->json(['resultado' => 'ok']);
  }
}