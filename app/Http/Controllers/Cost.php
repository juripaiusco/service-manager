<?php

namespace App\Http\Controllers;

use App\Model\Fattura;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Cost extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function general()
    {
        /*$fatture = Fattura::get();

        foreach ($fatture as $fattura) {

            $fattura_up = Fattura::where('id', $fattura->id)
                   ->update([
                       'data' => $fattura->data,
                       'anno' => substr($fattura->data, 0, 4)
                   ]);

        }*/

        $costs = Fattura::where('tipo', 'passiva')
                          ->select([
                              'anno',
                              'categoria',
                              DB::raw('SUM(importo_netto) AS importo_netto')
                          ])
                          ->orderby('categoria')
                          ->orderby('anno')
                          ->groupby('anno')
                          ->groupby('categoria')
                          ->get();

        $y_start = $costs[0]->anno;
        $y_end = $costs[count($costs) - 1]->anno;

        foreach ($costs as $cost) {

            $array_costs[$cost->categoria][$cost->anno] = $cost;

        }

        return view('cost.general', [
            'costs' => $array_costs,
            'y_start' => $y_start,
            'y_end' => $y_end
        ]);
    }
}
