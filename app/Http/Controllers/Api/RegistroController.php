<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Registro;
use Illuminate\Support\Carbon;


class RegistroController extends Controller
{
    public function createRegistro(Request $request, $cpf)
    {
        // dd($request->all());
        $data_atual = Carbon::now('America/Sao_Paulo')->format('Y-m-d');
        $hora_atual = Carbon::now('America/Sao_Paulo')->format('H:i');

        // dd($request->all());
        $checa_registro = Registro::where('cpf',$cpf)->where('data',$data_atual)->first();
        //   dd($checa_registro);

        if($checa_registro == null)
        {
            $registro = new Registro;
            
            $registro->cpf             = $cpf;
            $registro->entrada         = $hora_atual;
            $registro->data            = $data_atual;

            $image = request()->file('img');
            $upload = $image->store('uploadImg');

            $registro->entrada_img = $upload;
            
            $registro->save();
            
            return response()->json([
                'resultado' => 'ok',
            ],200);

        }else{

            if($checa_registro->entrada_alm == null){

                $checa_registro->entrada_alm        = $hora_atual;
                
                $image = request()->file('img');
                $upload = $image->store('uploadImg');

                $checa_registro->entrada_alm_img = $upload;

                $checa_registro->save();

                return response()->json([
                    'resultado' => 'ok',
                ],200);

            }elseif ($checa_registro->volta_alm == null) {

                $checa_registro->volta_alm        = $hora_atual;

                $image = request()->file('img');
                $upload = $image->store('uploadImg');
                
                $checa_registro->volta_alm_img = $upload;

                $checa_registro->save();
                
                return response()->json([
                    'resultado' => 'ok',
                ],200);

            }elseif ($checa_registro->saida == null){

                $checa_registro->saida        = $hora_atual;

                $image = request()->file('img');
                $upload = $image->store('uploadImg');
                
                $checa_registro->saida_img = $upload;

                $checa_registro->save();
                
                return response()->json([
                    'resultado' => 'ok',
                ],200);

            }else{
                return response()->json([
                    'resultado' => 'complete',
                ]);
            }

            
        }

        // dd($checa_registro);

        // dd('dale');
        // vai receber o cpf - OK
        // verificar se ja existe algum registro naquele dia - OK
        // se não existir - OK
        // criar um novo e preecher nome, cpf, *entrada*, entrada_img - OK
        // se existir ai verifica se *entrada_alm* é nullo, se for salva - OK
        // se não for nullo verifica *volta_alm*, se for salva - OK
        // se não for nullo, verifica *saida* e salva - OK
        // se tiver alguma checagem posterior a todos os dados preenchidos, retornar um erro - OK
    }
}
