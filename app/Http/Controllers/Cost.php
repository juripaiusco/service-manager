<?php

namespace App\Http\Controllers;

use App\Model\FicDoc;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Cost extends Controller
{
    var $array_months = array(
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

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Recupero i documenti passivi degli ultimi due anni
     * in base al tipo di documento: spese o ndc
     *
     * @param $tipo_doc
     *
     * @return mixed
     */
    private function getDataByCat($tipo_doc, $from_year = '')
    {
        if ($from_year == '') {
            $from_year = 0;
        }

        $dataByCategory = FicDoc::where('tipo', 'passiva')
                                ->where('tipo_doc', $tipo_doc)
                                ->where('anno', '>=', $from_year)
                                ->select([
                                    'anno',
                                    'data',
                                    'categoria',
                                    DB::raw('SUM(importo_netto) AS importo_netto')
                                ])
                                ->orderby('categoria')
                                ->orderby('data', 'desc')
                                ->groupby(DB::raw('DATE_FORMAT(data, \'%Y-%m\')'))
                                ->groupby('categoria')
                                ->get();

        return $dataByCategory;
    }

    /**
     * Creo un array delle spese degli ultimi due anni
     * suddivise per categoria.
     *
     * @return array
     */
    private function catCostsByMonths($from_year = '', $category = '')
    {
        // Recupero le spese
        $costs_months_by_category = $this->getDataByCat('spesa', $from_year);

        foreach ($costs_months_by_category as $cost) {

            $data_i = substr(str_replace('-', '', $cost->data), 0, 6);
            $array_costs_months_by_category[$cost->categoria][$cost->anno][$data_i] = $cost->importo_netto;

        }

        // Recupero le note di credito
        $credits_months_by_category = $this->getDataByCat('ndc', $from_year);

        foreach ($credits_months_by_category as $credit) {

            $data_i = substr(str_replace('-', '', $credit->data), 0, 6);
            $array_costs_months_by_category[$credit->categoria][$credit->anno][$data_i] -= $credit->importo_netto;

        }

        if ($category != '') {

            $return = $array_costs_months_by_category[$category];

        } else {

            $return = $array_costs_months_by_category;
        }

        return $return;
    }

    private function costsByMonth($from_year = '')
    {
        $array_catCostsByMonths = $this->catCostsByMonths($from_year);

        foreach ($array_catCostsByMonths as $cat => $array_costs) {

            foreach ($array_costs as $y => $array_costs_by_months) {

                for ($m = 1; $m <= 12; $m++) {

                    $m = sprintf('%02d', $m);

                    if (isset($array_costs_by_months[$y . $m])) {

                        if (!isset($arary_costs_by_years[$y][$y . $m])) {
                            $arary_costs_by_years[$y][$y . $m] = 0;
                        }

                        $arary_costs_by_years[$y][$y . $m] += $array_costs_by_months[$y . $m];

                    }

                }

                ksort($arary_costs_by_years[$y]);
            }
        }

        return $arary_costs_by_years;
    }

    /**
     * Comparazione dei costi con l'anno precedente
     * suddivisa per categoria, in questo modo è più
     * semplice individuare le uscite non "regolari".
     *
     * @param $array_costs_months_by_category
     *
     * @return array
     */
    private function catCostsComparison($array_costs_months_by_category)
    {
        // Confronto spese ad oggi con l'anno precedente
        foreach ($array_costs_months_by_category as $cat => $array_cost) {

            foreach ($array_cost as $cost) {

                for ($y = date('Y'); $y >= date('Y') - 1; $y--) {

                    for ($m = 1; $m <= intval(date('m')); $m++) {

                        $m = sprintf('%02d', $m);

                        if (!isset($cost[$y . $m])) {
                            $cost[$y . $m] = 0;
                        }

                        if (!isset($array_comparison[$cat][$y])) {
                            $array_comparison[$cat][$y] = 0;
                        }

                        $array_comparison[$cat][$y] += $cost[$y . $m];

                    }

                }

            }

            $array_comparison[$cat]['comparison'] = $array_comparison[$cat][date('Y')] - $array_comparison[$cat][date('Y') - 1];

        }

        uasort($array_comparison, function ($a, $b) {

            if ($a['comparison'] == $b['comparison']) {
                return 0;
            }
            return ($a['comparison'] > $b['comparison']) ? -1 : 1;

        });

        return $array_comparison;
    }

    /**
     * Comparazione con l'anno precedente.
     *
     * @param $array_costs_by_months
     *
     * @return array
     */
    private function yearCostsComparison($array_costs_by_months)
    {
        foreach ($array_costs_by_months as $y => $array_costs) {

            for ($m = 1; $m <= intval(date('m')); $m++) {

                $m = sprintf('%02d', $m);

                if (!isset($array_comparison[$y])) {
                    $array_comparison[$y] = 0;
                }

                if (isset($array_costs[$y . $m])) {
                    $array_comparison[$y] += $array_costs[$y . $m];
                }

            }
        }

        $array_comparison['comparison'] = $array_comparison[date('Y')] - $array_comparison[date('Y') - 1];

        return $array_comparison;
    }

    public function general()
    {
        $from_year = date('Y') - 1;

        $array_costs_months_by_category = $this->catCostsByMonths($from_year);
        $array_comparison_by_category = $this->catCostsComparison($array_costs_months_by_category);

        $array_costs_by_months = $this->costsByMonth($from_year);
        $array_comparison_by_year = $this->yearCostsComparison($array_costs_by_months);

        return view('cost.general', [
            'array_months' => $this->array_months,
            'array_costs_months_by_category' => $array_costs_months_by_category,
            'array_comparison_by_category' => $array_comparison_by_category,
            'array_costs_by_months' => $array_costs_by_months,
            'array_comparison_by_year' => $array_comparison_by_year
        ]);
    }

    public function detail($categoria)
    {
        $array_costs_by_months = $this->catCostsByMonths(0, $categoria);
        $array_comparison = $this->catCostsComparison(array($categoria => $array_costs_by_months));

        $fatture = FicDoc::where('tipo', 'passiva')
                         ->where('categoria', $categoria)
                         ->orderby('data', 'desc')
                         ->get();

        return view('cost.detail', [
            'category' => $categoria,
            'array_months' => $this->array_months,
            'array_costs_by_months' => $array_costs_by_months,
            'array_comparison' => $array_comparison,
            'fatture' => $fatture
        ]);
    }
}
