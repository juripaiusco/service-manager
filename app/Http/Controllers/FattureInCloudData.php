<?php

namespace App\Http\Controllers;

use App\Model\FicDoc;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FattureInCloudData extends Controller
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
     * Recupero i documenti passivi degli ultimi due anni
     * in base al tipo di documento: fatture, spese o ndc
     *
     * @param $tipo_doc
     *
     * @return mixed
     */
    public function getDataByCat($args = array(
        'from_year' => 0,
        'tipo' => '',
        'tipo_doc' => '',
    ))
    {
        $dataByCategory = FicDoc::where('tipo', $args['tipo'])
                                ->where('tipo_doc', $args['tipo_doc'])
                                ->where('anno', '>=', $args['from_year'])
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

    public function catDataByMonths($args = array(
        'from_year' => '',
        'tipo' => '',
        'tipo_doc' => '',
        'categoria' => ''
    ))
    {
        // Recupero i documenti richiesti
        $docs_months_by_category = $this->getDataByCat($args);

        foreach ($docs_months_by_category as $doc) {

            $data_i = substr(str_replace('-', '', $doc->data), 0, 6);
            $array_docs_months_by_category[$doc->categoria][$doc->anno][$data_i] = $doc->importo_netto;

        }

        // Recupero le note di credito
        $ndc_months_by_category = $this->getDataByCat(array(
            'tipo' => $args['tipo'],
            'tipo_doc' => 'ndc',
            'from_year' => $args['from_year']
        ));

        foreach ($ndc_months_by_category as $ndc) {

            $data_i = substr(str_replace('-', '', $ndc->data), 0, 6);
            $array_docs_months_by_category[$ndc->categoria][$ndc->anno][$data_i] -= $ndc->importo_netto;

        }

        if (isset($args['categoria'])) {

            $return = $array_docs_months_by_category[$args['categoria']];

        } else {

            $return = $array_docs_months_by_category;
        }

        return $return;
    }

    public function dataByMonth($args = array())
    {
        $array_catDataByMonths = $this->catDataByMonths($args);

        foreach ($array_catDataByMonths as $cat => $array) {

            foreach ($array as $y => $array_data_by_months) {

                for ($m = 1; $m <= 12; $m++) {

                    $m = sprintf('%02d', $m);

                    if (isset($array_data_by_months[$y . $m])) {

                        if (!isset($arary_data_by_years[$y][$y . $m])) {
                            $arary_data_by_years[$y][$y . $m] = 0;
                        }

                        $arary_data_by_years[$y][$y . $m] += $array_data_by_months[$y . $m];

                    }

                }

                ksort($arary_data_by_years[$y]);
            }
        }

        return $arary_data_by_years;
    }

    /**
     * Comparazione dei costi con l'anno precedente
     * suddivisa per categoria, in questo modo è più
     * semplice individuare le uscite non "regolari".
     *
     * @param $array_data_months_by_category
     *
     * @return array
     */
    public function catComparison($array_data_months_by_category, $year_comparison = '', $month_end_comparison = '')
    {
        if (!$year_comparison) {
            $year_comparison = date('Y');
        }

        if (!$month_end_comparison) {
            $month_end_comparison = date('m');
        }

        // Confronto i documenti ad oggi con l'anno precedente
        foreach ($array_data_months_by_category as $cat => $array_cost) {

            foreach ($array_cost as $cost) {

                for ($y = $year_comparison; $y >= $year_comparison - 1; $y--) {

                    for ($m = 1; $m <= $month_end_comparison; $m++) {

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

            $array_comparison[$cat]['comparison'] = $array_comparison[$cat][$year_comparison] - $array_comparison[$cat][$year_comparison - 1];

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
     * @param $array_data_by_months
     *
     * @return array
     */
    public function yearComparison($array_data_by_months, $year_comparison = '', $month_end_comparison = '')
    {
        if (!$year_comparison) {
            $year_comparison = date('Y');
        }

        if (!$month_end_comparison) {
            $month_end_comparison = date('m');
        }

        foreach ($array_data_by_months as $y => $array) {

            for ($m = 1; $m <= $month_end_comparison; $m++) {

                $m = sprintf('%02d', $m);

                if (!isset($array_comparison[$y])) {
                    $array_comparison[$y] = 0;
                }

                if (isset($array[$y . $m])) {
                    $array_comparison[$y] += $array[$y . $m];
                }

            }
        }

        $array_comparison['comparison'] = $array_comparison[$year_comparison] - $array_comparison[$year_comparison - 1];

        return $array_comparison;
    }
}
