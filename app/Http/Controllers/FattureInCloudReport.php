<?php

namespace App\Http\Controllers;

use App\Model\FicDoc;
use Illuminate\Http\Request;

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

        $fatture = FicDoc::where('tipo', 'passiva')
                         ->where('anno', '>=', $from_year)
                         ->orderby('data', 'desc')
                         ->get();

        foreach ($fatture as $fattura) {

            $data_i = substr(str_replace('-', '', $fattura->data), 0, 6);

            $array_fatture[$fattura->anno][$data_i][] = $fattura;

        }

        return view('analysis.cost.general', [
            'array_months' => $FicData->array_months,
            'array_costs_months_by_category' => $array_costs_months_by_category,
            'array_comparison_by_category' => $array_comparison_by_category,
            'array_costs_by_months' => $array_costs_by_months,
            'array_comparison_by_year' => $array_comparison_by_year,
            'array_fatture' => $array_fatture,
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
