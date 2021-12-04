<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Str;

class SimplexController extends Controller
{
    public function simplex(Request $request)
    {
        $data = $request->all();

        // $data['variavel'] = 3;
        // $data['restricao'] = 4;

        // $data['funcao'] = [10, 8, 1];
        // $data['restricao1'] = [3, 3, 2, 30];
        // $data['restricao2'] = [6, 3, 0, 48];
        // $data['restricao3'] = [6, 3, 0, 48];
        // $data['restricao4'] = [6, 3, 0, 48];

        for ($i=0; $i < $data['variavel']; $i++) {
            $simplex['funcao']['X'.($i+1)] = $data['funcao'][$i] * -1;

            if(($i + 1) == $data['variavel']){
                for ($j= ($i+1); $j < ($data['variavel'] + $data['restricao']); $j++) {
                    $simplex['funcao']['X'.($j+1)] = 0;
                }

                $simplex['funcao']['rz'] = 0;
            }
        }

        for ($i=1; $i < $data['restricao'] + 1; $i++) {
            for ($j=0; $j < $data['variavel']; $j++) {
                $simplex['restricao'.$i]['X'.($j + 1)] = $data['restricao'.($i-1)][$j];

                if(($j+1) == $data['variavel']){
                    for ($k= ($j+1); $k < ($data['variavel'] + $data['restricao']); $k++) {
                        if(($data['variavel'] + ($i-1)) == $k){
                            $simplex['restricao'.$i]['X'.($k + 1)] = 1;
                        }else{
                            $simplex['restricao'.$i]['X'.($k + 1)] = 0;
                        }
                    }
                }
            }

            $simplex['restricao'.$i]['r'.$i] = $data['restricao'.($i-1)][$j];
        }

        $simplex['variavel'] = $data['variavel'];
        $simplex['restricao'] = $data['restricao'];
        $simplex['interacao'] = 0;

        $request->session()->put('simplex', $simplex);

        return view('parte3', compact('simplex'));
    }

    public function tabela(Request $request)
    {
        $simplex = $request->session()->get('simplex');

        $simplex['min'] = min($simplex['funcao']);

        $simplex['var'] = array_search($simplex['min'], $simplex['funcao']);

        $simplex['linha_pivo'] = 'funcao';

        if($simplex['min'] < 0){
            $simplex = $this->interacoes($simplex);
        }

        $min = $simplex['min'];
        $var = $simplex['var'];
        $linha_pivo = $simplex['linha_pivo'];

        return view('tabela', compact('simplex', 'min', 'var', 'linha_pivo'));
    }

    public function interacoes($old_simplex)
    {
        $new_simplex = $old_simplex;

        $var = $new_simplex['var'];

        for ($i=1; $i < $old_simplex['restricao'] + 1; $i++) {
            $d['restricao'.$i] = $old_simplex['restricao'.$i]['r'.$i] / $old_simplex['restricao'.$i][$var];
        }

        $linha_pivo = $new_simplex['linha_pivo'] = array_search(min($d), $d);

        if($old_simplex['interacao'] == 0){
            $new_simplex['interacao'] += 1;

            session()->put('simplex', $new_simplex);

            return $new_simplex;
        }

        $new_simplex['interacao'] += 1;

        $pivo = $old_simplex[$linha_pivo][$var];

        foreach ($old_simplex[$linha_pivo] as $key => $value) {
            $new_simplex[$linha_pivo][$key] = $value / $pivo;
        }

        for ($i=1; $i < $old_simplex['restricao'] + 1; $i++) {
            if("restricao".$i != $linha_pivo){
                foreach ($old_simplex["restricao".$i] as $key => $value) {
                    if($key == "r".$i){
                        $r = "r".Str::after($linha_pivo, 'restricao');
                        $new_simplex["restricao".$i][$key] = $new_simplex[$linha_pivo][$r] * ($old_simplex["restricao".$i][$var] * -1) + $value;
                    }else{
                        $new_simplex["restricao".$i][$key] = $new_simplex[$linha_pivo][$key] * ($old_simplex["restricao".$i][$var]  * -1) + $value;
                    }

                }
            }
        }

        foreach ($old_simplex['funcao'] as $key => $value) {
            if($key == "rz"){
                $r = "r".Str::after($linha_pivo, 'restricao');
                $new_simplex["funcao"][$key] = $new_simplex[$linha_pivo][$r] * ($old_simplex["funcao"][$var] * -1) + $value;
            }else{
                $new_simplex["funcao"][$key] = $new_simplex[$linha_pivo][$key] * ($old_simplex["funcao"][$var] * -1) + $value;
            }

        }

        $new_simplex['min'] = min($new_simplex['funcao']);

        $new_simplex['var'] = array_search($new_simplex['min'], $new_simplex['funcao']);

        for ($i=1; $i < $new_simplex['restricao'] + 1; $i++) {
            if($new_simplex['restricao'.$i][$new_simplex['var']] != 0)
                $d['restricao'.$i] = $new_simplex['restricao'.$i]['r'.$i] / $new_simplex['restricao'.$i][$new_simplex['var']];
        }

        if( $new_simplex['min'] < 0){
            $new_simplex['linha_pivo'] = array_search(min($d), $d);
        }
        else{
            $new_simplex['linha_pivo'] = "funcao";
            $new_simplex['z'] = $new_simplex["funcao"]['rz'];
        }

        session()->put('simplex', $new_simplex);

        return session()->get('simplex');
    }
}
