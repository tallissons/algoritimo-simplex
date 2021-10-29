<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class algebricoController extends Controller
{
    function index(Request $request){
        
        $variaveis = $request->input('variavel');
        $restricoes = $request->input('restricao');

        
        return view('parte2', ['variaveis' => $variaveis, 'restricoes' => $restricoes]);
    }
}
