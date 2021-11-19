<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class algebricoController extends Controller
{
    function index(Request $request){
        
        $regras = [
            'variavel' => 'required|lte:100000000000|gte:2',
            'restricao' => 'required|lte:10|gte:1'
         ];

        $feedback = [

            'required' => 'O campo :attribute é obrigatório',
            'variavel.gte' => 'O campo :attribute deve ter ao menos 1 variavel!',
            'variavel.lte' => 'O campo :attribute deve ter no máximo 10 variaveis!',
            'restricao.gte' => 'O campo :attribute deve ter ao menos 1 restrição!',
            'restricao.lte' => 'O campo :attribute deve ter no máximo 10 restrições!'

        ];

        $request->validate($regras, $feedback);

        $variaveis = $request->input('variavel');
        $restricoes = $request->input('restricao');

        
        return view('parte2', ['variaveis' => $variaveis, 'restricoes' => $restricoes]);
    }

    function calcular(Request $request){

        dd($request);
        //return view('parte3');

    }
}
