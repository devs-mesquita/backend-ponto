<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;

class VerificarCPF
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        $cpf = $request->cpf;
       
        $user = User::where('cpf', $cpf)->first();

        if (!$user) {
            return response()->json(['resultado' => 'invalid-cpf'], 401);
        }
    
        return $next($request);

    }
}
