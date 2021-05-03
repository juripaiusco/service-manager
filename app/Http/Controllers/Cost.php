<?php

namespace App\Http\Controllers;

use App\Model\FicDoc;
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

        $costs = FicDoc::where('tipo', 'passiva')
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

        if (isset($costs[0]->anno)) {

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

    public function detail($categoria)
    {
        $costs = FicDoc::where('tipo', 'passiva')
                        ->where('categoria', $categoria)
                        ->select([
                            'anno',
                            'data',
                            'categoria',
                            DB::raw('SUM(importo_netto) AS importo_netto')
                        ])
                        ->orderby('data', 'desc')
                        ->groupby(DB::raw('DATE_FORMAT(data, \'%Y-%m\')'))
                        ->get();

        $fatture = FicDoc::where('tipo', 'passiva')
                        ->where('categoria', $categoria)
                        ->orderby('data', 'desc')
                        ->get();

        foreach ($costs as $cost) {

            $data_i = substr(str_replace('-', '', $cost->data), 0, 6);
            $array_costs[$cost->categoria . ' ' . $cost->anno][$cost->anno][$data_i] = $cost;

        }

        $array_months = array(
            'gennaio',
            'febbraio',
            'marzo',
            'aprile',
            'maggio',
            'giugno',
            'luglio',
            'agosto',
            'settembre',
            'ottobre',
            'novembre',
            'dicembre'
        );

        return view('cost.detail', [
            'category' => $categoria,
            'costs' => $array_costs,
            'months' => $array_months,
            'fatture' => $fatture
        ]);
    }
}
