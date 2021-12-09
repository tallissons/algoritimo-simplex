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

        $M = 0;
        $ultima_var = $data['variavel'];
        $qnt_variavel = $data['variavel'];
        $w = [];

        for ($i=0; $i < $data['restricao']; $i++) {
            if($data['tipo'.$i] == 0){// =
                $qnt_variavel += 1;
            }elseif($data['tipo'.$i] == 1){ // <=
                $qnt_variavel += 1;
            }elseif($data['tipo'.$i] == 2){ // >=
                $qnt_variavel += 2;
            }
        }

        for ($i=0; $i < $data['variavel']; $i++) {
            $simplex['funcao']['X'.($i+1)] = $data['funcao'][$i] * -1;

            if(($i + 1) == $data['variavel']){
                for ($j= ($i+1); $j < $qnt_variavel; $j++) {
                    $simplex['funcao']['X'.($j+1)] = 0;
                }

                $simplex['funcao']['rz'] = 0;
            }
        }

        for ($i=1; $i < $data['restricao'] + 1; $i++) {

            if($data['tipo'.($i-1)] == 0){// =
                $resticao = $this->igual($M, $simplex['funcao'], $data['restricao'.($i-1)],  $qnt_variavel, $ultima_var, $i, $w);
                $simplex['funcao'] = $resticao['funcao'];
                $w = $resticao['w'];

            }elseif($data['tipo'.($i-1)] == 1){ // <=
                $resticao = $this->menor_igual($data['restricao'.($i-1)],  $qnt_variavel, $ultima_var, $i);

            }elseif($data['tipo'.($i-1)] == 2){ // >=
                $resticao = $this->maior_igual($M, $simplex['funcao'], $data['restricao'.($i-1)],  $qnt_variavel, $ultima_var, $i, $w);
                $simplex['funcao'] = $resticao['funcao'];
                $w = $resticao['w'];

            }

            $simplex['restricao'.$i] = $resticao['simplex'];

            $ultima_var = $resticao['ultima_var'];
            /*dd([$resticao, $simplex]);

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

            $simplex['restricao'.$i]['r'.$i] = $data['restricao'.($i-1)][$j];*/
        }

        $simplex['objetivo'] = $data['objetivo'];
        $simplex['variavel'] = $data['variavel'];
        $simplex['restricao'] = $data['restricao'];
        $simplex['interacao'] = 0;
        $simplex['w'] = $w;

        if($simplex['objetivo'] == "min"){
            foreach ($simplex['funcao'] as $key => $value) {
                //$simplex['funcao'][$key] *= -1;
            }
        }

        $request->session()->put('simplex', $simplex);

        return view('parte3', compact('simplex'));
    }

    public function menor_igual($resticao, $qnt_variavel, $ultima_var, $n_restricao)
    {
        for ($i=1; $i < count($resticao); $i++) {
            $simplex['X'.$i] = $resticao[$i-1];
        }

        for ($j=count($resticao); $j < $qnt_variavel+1; $j++) { // + folga
            if($j == $ultima_var + 1){
                $simplex['X'.$j] = 1;
            }else{
                $simplex['X'.$j] = 0;
            }
        }

        $simplex['r'.$n_restricao] = $resticao[$i-1];

        $ultima_var += 1;

        return compact('ultima_var', 'simplex');
    }

    public function maior_igual($M, $funcao, $resticao, $qnt_variavel, $ultima_var, $n_restricao, $w)
    {
        for ($i=1; $i < count($resticao); $i++) {
            $simplex['X'.$i] = $resticao[$i-1];
        }

        for ($j=count($resticao); $j < $qnt_variavel+1; $j++) { // - excesso + artificial
            if($j == $ultima_var + 1){
                $simplex['X'.$j] = -1;

                $simplex['X'.($j + 1)] = 1;
                $funcao['X'.($j + 1)] = $M;
            }else{
                if($j != $ultima_var + 2){
                    $simplex['X'.$j] = 0;
                }
            }
        }

        $simplex['r'.$n_restricao] = $resticao[$i-1];

        $ultima_var += 2;

        if(empty($w)){
            foreach ($simplex as $key => $value) {
                if($key != "X".$ultima_var){
                    if($key == "r".$n_restricao)
                        $key = "rw";
                    $w[$key] = $value *= -1;
                }else{
                    $w[$key] = 0;
                }
            }
        }else{
            foreach ($simplex as $key => $value) {
                if($key != "X".$ultima_var){
                    if($key == "r".$n_restricao)
                        $key = "rw";
                    $w[$key] += ($value *= -1);
                }else{
                    $w[$key] = 0;
                }
            }
        }

        return compact('ultima_var', 'simplex', 'funcao', 'w');
    }

    public function igual($M, $funcao, $resticao, $qnt_variavel, $ultima_var, $n_restricao, $w)
    {
        for ($i=1; $i < count($resticao); $i++) {
            $simplex['X'.$i] = $resticao[$i-1];
        }

        for ($j=count($resticao); $j < $qnt_variavel+1; $j++) { // + artificial
            if($j == $ultima_var + 1){
                $simplex['X'.$j] = 1;
                $funcao['X'.$j] = $M;
            }else{
                $simplex['X'.$j] = 0;
            }
        }

        $simplex['r'.$n_restricao] = $resticao[$i-1];

        $ultima_var += 1;

        if(empty($w)){
            foreach ($simplex as $key => $value) {
                if($key != "X".$ultima_var){
                    if($key == "r".$n_restricao)
                        $key = "rw";
                    $w[$key] = $value *= -1;
                }else{
                    $w[$key] = 0;
                }
            }
        }else{
            foreach ($simplex as $key => $value) {
                if($key != "X".$ultima_var){
                    $w[$key] += ($value *= -1);
                }else{
                    $w[$key] = 0;
                }
            }
        }

        return compact('ultima_var', 'simplex', 'funcao', 'w');
    }

    public function tabela(Request $request)
    {
        $simplex = $request->session()->get('simplex');

        if(empty($simplex['w'])){
            $var_funcao = array_slice($simplex['funcao'], 0, count($simplex['funcao']) - 1);

            $simplex['min'] = min($var_funcao);

            $simplex['var'] = array_search($simplex['min'], $var_funcao);

            $simplex['linha_pivo'] = 'funcao';

            if($simplex['min'] < 0){
                $simplex = $this->interacoes($simplex);
            }

            $min = $simplex['min'];
            $var = $simplex['var'];
            $linha_pivo = $simplex['linha_pivo'];

            return view('tabela', compact('simplex', 'min', 'var', 'linha_pivo'));
        }else{
            if(min(array_slice($simplex['w'], 0, count($simplex['w']) - 1)) >= 0){// Verifica se não tem nenhum valor negativo em w
                if(round($simplex['w']['rw'], 10) == 0){// Verifica se o problema tem solução
                    $simplex['w'] = [];
                    $var_funcao = array_slice($simplex['funcao'], 0, count($simplex['funcao']) - 1);

                    $simplex['min'] = min($var_funcao);

                    $simplex['var'] = array_search($simplex['min'], $var_funcao);

                    $simplex['linha_pivo'] = 'funcao';

                    if($simplex['min'] < 0){
                        $simplex = $this->interacoes($simplex);
                    }

                    $min = $simplex['min'];
                    $var = $simplex['var'];
                    $linha_pivo = $simplex['linha_pivo'];

                    return view('tabela', compact('simplex', 'min', 'var', 'linha_pivo'));
                }
            }else{
                $simplex = $this->duas_fases($simplex);
                $min = $simplex['min'];
                $var = $simplex['var'];
                $linha_pivo = $simplex['linha_pivo'];

                return view('tabela', compact('simplex', 'min', 'var', 'linha_pivo'));
            }

            dd($simplex);//round($simplex['w']['rw'], 10) == 0


            return view('tabela', compact('simplex', 'min', 'var', 'linha_pivo'));
        }

        dd($simplex);

        $var_funcao = array_slice($simplex['funcao'], 0, count($simplex['funcao']) - 1);

        $simplex['min'] = min($var_funcao);

        $simplex['var'] = array_search($simplex['min'], $var_funcao);

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
            if($old_simplex['restricao'.$i][$var]){
                $dm = $old_simplex['restricao'.$i]['r'.$i] / $old_simplex['restricao'.$i][$var];
                if($dm > 0){
                    $d['restricao'.$i] = $dm;
                }
            }
        }

        if(isset($d)){
            $linha_pivo = $new_simplex['linha_pivo'] = array_search(min($d), $d);
        }else{
            return $new_simplex ;
        }

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

        $var_funcao = array_slice($new_simplex['funcao'], 0, count($new_simplex['funcao']) - 1);

        $new_simplex['min'] = min($var_funcao);

        $new_simplex['var'] = array_search($new_simplex['min'], $var_funcao);

        for ($i=1; $i < $new_simplex['restricao'] + 1; $i++) {
            if($new_simplex['restricao'.$i][$new_simplex['var']] != 0)
                $dm = $new_simplex['restricao'.$i]['r'.$i] / $new_simplex['restricao'.$i][$new_simplex['var']];
                if($dm > 0){
                    $d['restricao'.$i] = $dm;
                }
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

    public function duas_fases($old_simplex)
    {
        $new_simplex = $old_simplex;

        // 1º Pegar o menor valor negativo de w
        $var_funcao = array_slice($old_simplex['w'], 0, count($old_simplex['w']) - 1);

        $new_simplex['min'] = min($var_funcao);

        $new_simplex['var'] = array_search($new_simplex['min'], $var_funcao);

        // 2º Pegar o Pivo
        for ($i=1; $i < $old_simplex['restricao'] + 1; $i++) {
            if($old_simplex['restricao'.$i][$new_simplex['var']]){
                $dm = $old_simplex['restricao'.$i]['r'.$i] / $old_simplex['restricao'.$i][$new_simplex['var']];
                if($dm > 0){
                    $d['restricao'.$i] = $dm;
                }
            }
        }

        if(isset($d)){
            $linha_pivo = $new_simplex['linha_pivo'] = array_search(min($d), $d);
        }else{
            return $new_simplex ;
        }

        if($old_simplex['interacao'] == 0){
            $new_simplex['interacao'] += 1;

            session()->put('simplex', $new_simplex);

            return $new_simplex;
        }

        $new_simplex['interacao'] += 1;

        // 3º Nova linha Pivo
        $pivo = $old_simplex[$linha_pivo][$new_simplex['var']];

        foreach ($old_simplex[$linha_pivo] as $key => $value) {
            $new_simplex[$linha_pivo][$key] = $value / $pivo;
        }

        // 4º Novas linhas da restrição
        for ($i=1; $i < $old_simplex['restricao'] + 1; $i++) {
            if("restricao".$i != $linha_pivo){
                foreach ($old_simplex["restricao".$i] as $key => $value) {
                    if($key == "r".$i){
                        $r = "r".Str::after($linha_pivo, 'restricao');
                        $new_simplex["restricao".$i][$key] = $new_simplex[$linha_pivo][$r] * ($old_simplex["restricao".$i][$new_simplex['var']] * -1) + $value;
                    }else{
                        $new_simplex["restricao".$i][$key] = $new_simplex[$linha_pivo][$key] * ($old_simplex["restricao".$i][$new_simplex['var']]  * -1) + $value;
                    }

                }
            }
        }

        // 5º Nova linha da função z
        foreach ($old_simplex['funcao'] as $key => $value) {
            if($key == "rz"){
                $r = "r".Str::after($linha_pivo, 'restricao');
                $new_simplex["funcao"][$key] = $new_simplex[$linha_pivo][$r] * ($old_simplex["funcao"][$new_simplex['var']] * -1) + $value;
            }else{
                $new_simplex["funcao"][$key] = $new_simplex[$linha_pivo][$key] * ($old_simplex["funcao"][$new_simplex['var']] * -1) + $value;
            }

        }

        // 6º Nova linha da função w
        foreach ($old_simplex['w'] as $key => $value) {
            if($key == "rw"){
                $r = "r".Str::after($linha_pivo, 'restricao');
                $new_simplex["w"][$key] = $new_simplex[$linha_pivo][$r] * ($old_simplex["w"][$new_simplex['var']] * -1) + $value;
            }else{
                $new_simplex["w"][$key] = $new_simplex[$linha_pivo][$key] * ($old_simplex["w"][$new_simplex['var']] * -1) + $value;
            }

        }

        $var_funcao = array_slice($new_simplex['w'], 0, count($new_simplex['w']) - 1);

        $new_simplex['min'] = min($var_funcao);

        $new_simplex['var'] = array_search($new_simplex['min'], $var_funcao);

        for ($i=1; $i < $old_simplex['restricao'] + 1; $i++) {
            if($old_simplex['restricao'.$i][$new_simplex['var']]){
                $dm = $old_simplex['restricao'.$i]['r'.$i] / $old_simplex['restricao'.$i][$new_simplex['var']];
                if($dm > 0){
                    $d['restricao'.$i] = $dm;
                }
            }
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
