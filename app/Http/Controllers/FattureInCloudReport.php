<?php

namespace App\Http\Controllers;

use App\Model\FicDoc;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FattureInCloudReport extends Controller
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

    public function incomeGeneral()
    {
        $from_year = date('Y') - 1;

        $FicData = new FattureInCloudData();

        /*$array_income_months_by_category = $FicData->catDataByMonths(array(
            'from_year' => $from_year,
            'tipo' => 'attiva',
            'tipo_doc' => 'fatture'
        ));
        $array_comparison_by_category = $FicData->catComparison($array_income_months_by_category);*/

        $array_income_by_months = $FicData->dataByMonth(array(
            'from_year' => $from_year,
            'tipo' => 'attiva',
            'tipo_doc' => 'fatture'
        ));
        $array_comparison_by_year = $FicData->yearComparison($array_income_by_months);

        return view('analysis.income.general', [
            'array_months' => $FicData->array_months,
            /*'array_income_months_by_category' => $array_income_months_by_category,
            'array_comparison_by_category' => $array_comparison_by_category,*/
            'array_income_by_months' => $array_income_by_months,
            'array_comparison_by_year' => $array_comparison_by_year
        ]);
    }

    public function incomeDetail($categoria, Request $request)
    {
        echo 'ciao';
    }

    public function costGeneral(Request $request)
    {
        $from_year = date('Y') - 1;

        $FicData = new FattureInCloudData();

        $array_costs_months_by_category = $FicData->catDataByMonths(array(
            'from_year' => $from_year,
            'tipo' => 'passiva',
            'tipo_doc' => 'spesa'
        ));
        $array_comparison_by_category = $FicData->catComparison($array_costs_months_by_category);

        $array_costs_by_months = $FicData->dataByMonth(array(
            'from_year' => $from_year,
            'tipo' => 'passiva',
            'tipo_doc' => 'spesa'
        ));
        $array_comparison_by_year = $FicData->yearComparison($array_costs_by_months);

        // - - -
        // Recupero le fatture dall'anno precedente all'anno attuale
        $fatture = FicDoc::where('tipo', 'passiva')
                         ->where('anno', '>=', $from_year)
                         ->orderby('data', 'desc')
                         ->get();

        foreach ($fatture as $fattura) {

            $data_i = substr(str_replace('-', '', $fattura->data), 0, 6);
            $array_fatture[$fattura->anno][$data_i][] = $fattura;

        }

        // - - -
        // Recupero le fatture raggruppate per mese e fornitore
        $fatture_group = FicDoc::where('tipo', 'passiva')
                         ->where('anno', '>=', $from_year)
                         ->select([
                             'nome',
                             'anno',
                             'data',
                             DB::raw('SUM(importo_netto) AS importo_netto'),
                             DB::raw('SUM(importo_iva) AS importo_iva'),
                             DB::raw('SUM(importo_totale) AS importo_totale')
                         ])
                         ->orderby('anno', 'desc')
                         ->orderby('importo_netto', 'desc')
                         ->groupby(DB::raw('DATE_FORMAT(data, \'%Y-%m\')'))
                         ->groupby('nome')
                         ->get();

        foreach ($fatture_group as $fattura) {

            $data_i = substr(str_replace('-', '', $fattura->data), 0, 6);
            $array_fatture_group[$fattura->anno][$data_i][] = $fattura;

        }

        // - - -
        // Recupero nomi fornitori per ogni mese
        // per confrontarli negli anni
        foreach ($fatture_group as $fattura) {

            $data_m = substr(str_replace('-', '', $fattura->data), 4, 2);

            if (!isset($array_name_by_month[$data_m])) {
                $array_name_by_month[$data_m][] = $fattura->nome;
            }

            if (!in_array($fattura->nome, $array_name_by_month[$data_m])) {
                $array_name_by_month[$data_m][] = $fattura->nome;
            }

        }

        ksort($array_name_by_month);

        // - - -
        // Creo un array per confrontare lo stesso l'anno precedente rispetto lo stesso mese
        foreach ($array_name_by_month as $month => $array_nomi) {

            foreach ($array_nomi as $nome) {

                foreach ($array_fatture_group as $y => $array_ym) {

                    foreach ($array_ym as $ym => $array_fatture_ym) {

                        if ($month == substr($ym, 4, 2)) {

                            $importo_netto = 0;
                            $importo_iva = 0;
                            $importo_totale = 0;

                            foreach ($array_fatture_ym as $fattura) {

                                if ($nome == $fattura->nome) {

                                    $importo_netto = $fattura->importo_netto;
                                    $importo_iva = $fattura->importo_iva;
                                    $importo_totale = $fattura->importo_totale;
                                    break;

                                }

                            }

                            $array_fatture_group_new[$y][$ym][$nome] = array(
                                'nome' => $nome,
                                'importo_netto' => $importo_netto,
                                'importo_iva' => $importo_iva,
                                'importo_totale' => $importo_totale
                            );

                        }

                    }

                }

            }

        }

        $array_fatture_group = $array_fatture_group_new;

        return view('analysis.cost.general', [
            'array_months' => $FicData->array_months,
            'array_costs_months_by_category' => $array_costs_months_by_category,
            'array_comparison_by_category' => $array_comparison_by_category,
            'array_costs_by_months' => $array_costs_by_months,
            'array_comparison_by_year' => $array_comparison_by_year,

            'array_fatture' => $array_fatture,

            'array_fatture_group' => $array_fatture_group,
            'array_name_by_month' => $array_name_by_month,

            'vs_select' => $request->input('vs'),
            'ym_select' => $request->input('ym')
        ]);
    }

    public function costDetail($categoria, Request $request)
    {
        $FicData = new FattureInCloudData();

        $array_costs_by_months = $FicData->catDataByMonths(array(
            'from_year' => 0,
            'tipo' => 'passiva',
            'tipo_doc' => 'spesa',
            'categoria' => $categoria
        ));
        $array_comparison = $FicData->catComparison(array($categoria => $array_costs_by_months));

        $fatture = FicDoc::where('tipo', 'passiva')
                         ->where('categoria', $categoria)
                         ->orderby('data', 'desc')
                         ->get();

        foreach ($fatture as $fattura) {

            $data_i = substr(str_replace('-', '', $fattura->data), 0, 6);

            $array_fatture[$fattura->anno][$data_i][] = $fattura;

        }

        return view('analysis.cost.detail', [
            'category' => $categoria,
            'array_months' => $FicData->array_months,
            'array_costs_by_months' => $array_costs_by_months,
            'array_comparison' => $array_comparison,
            'array_fatture' => $array_fatture,
            'ym_select' => $request->input('ym')
        ]);
    }
}
