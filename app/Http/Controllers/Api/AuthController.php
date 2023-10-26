<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // public function __construct()
    // {
    //     $this->middleware('auth:api', ['except' => ['login', 'register', 'getUsersBySetor']]);
    // }

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
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make(config('app.user_default_password', '')),
            'cpf'      => $request->cpf,
            'nivel'    => $request->nivel,
            'setor_id' => $request->setor_id,
        ]);

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
}