<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\RegistroController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\SetoresController;
use App\Http\Controllers\Api\IpController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::controller(AuthController::class)->group(function () {
  Route::post('login', 'login');
  Route::post('logout', 'logout');
  Route::post('refresh', 'refresh');
});

// Authenticated, >= User:
Route::middleware(['api-auth'])->group(function () {
  Route::get('registro',   [RegistroController::class, 'getRegistros'])->middleware('verificar_cpf');
  Route::post('changepassword', [AuthController::class, 'changePassword']);

  // >= Admin
  Route::middleware(['admin'])->group(function () {
    Route::post('registro',   [RegistroController::class, 'createRegistro'])->middleware('verificar_cpf');
    Route::post('registro/ferias',   [RegistroController::class, 'createFerias'])->middleware('verificar_cpf');
    Route::post('registro/delete',   [RegistroController::class, 'deleteRegistro'])->middleware('verificar_cpf');
    
    Route::resources(['setores' => SetoresController::class]);
    Route::get('users/{setor}', [AuthController::class, 'getUsersBySetor']);
    Route::post('register', [AuthController::class, 'register']);
    Route::post('resetpassword', [AuthController::class, 'resetPassword']);
  });

  // Super-Admin
  // Route::middleware(['super-admin'])->group(function () {
  //   
  // });
});