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
    Route::post('register', 'register');
    Route::post('logout', 'logout');
    Route::post('refresh', 'refresh');
});

Route::resources([
    'setores' => SetoresController::class,
]);

Route::post('registro',   [RegistroController::class, 'createRegistro'])->middleware('verificar_cpf');
Route::get('registro',   [RegistroController::class, 'getRegistros'])->middleware('verificar_cpf');

