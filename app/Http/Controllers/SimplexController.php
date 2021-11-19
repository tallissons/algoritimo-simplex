<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SimplexController extends Controller
{
    public function simplex(Request $request)
    {
        $data = $request->all();

        $data['variavel'] = 3;
        $data['restricao'] = 4;

        $data['funcao'] = [10, 8, 1];
        $data['restricao1'] = [3, 3, 2, 30];
        $data['restricao2'] = [6, 3, 0, 48];
        $data['restricao3'] = [6, 3, 0, 48];
        $data['restricao4'] = [6, 3, 0, 48];

        for ($i=0; $i < $data['variavel']; $i++) {
            $simplex['sfuncao'][$i] = $data['funcao'][$i] * -1;
        }

        for ($i=1; $i < $data['restricao'] + 1; $i++) {
            for ($j=0; $j < $data['variavel']; $j++) {
                $simplex['restricao'.$i]['x'.($j + 1)] = $data['restricao'.$i][$j];

                if(($j+1) == $data['variavel']){
                    for ($k= ($j+1); $k < ($data['variavel'] + $data['restricao']); $k++) {
                        if(($data['variavel'] + ($i-1)) == $k){
                            $simplex['restricao'.$i]['x'.($k + 1)] = 1;
                        }else{
                            $simplex['restricao'.$i]['x'.($k + 1)] = 0;
                        }
                    }
                }
            }

            $simplex['restricao'.$i]['r'.$i] = $data['restricao'.$i][$j];
        }

        dd($simplex);
    }
}
