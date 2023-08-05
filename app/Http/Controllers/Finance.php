<?php

namespace App\Http\Controllers;

use FattureInCloud\Filter\Condition;
use FattureInCloud\Filter\Filter;
use FattureInCloud\Filter\Operator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class Finance extends Controller
{
    public function incoming()
    {
        return Inertia::render('Finance/Incoming', [
            'data' => $this->months_calc('attiva')
        ]);
    }

    public function outcoming()
    {


        $invoice_last_year = $this->invoice_get()->where('anno', '=', date('Y') - 1);
        $invoice_last_year = $invoice_last_year->get();

        $invoice_this_year = $this->invoice_get()->where('anno', '=', date('Y'));
        $invoice_this_year = $invoice_this_year->get();

        return Inertia::render('Finance/Outcoming', [
            'data' => array_merge(
                $this->months_calc('passiva'),
                $this->category_calca_array(),
                array('invoice_last_year' => $invoice_last_year),
                array('invoice_this_year' => $invoice_this_year)
            ),
            'filters' => request()->all(['s', 'orderby', 'ordertype'])
        ]);
    }

    private function invoice_get()
    {
        $request_search_array = [
            'numero',
            'nome',
            'tipo_doc',
            'data',
            'importo_netto',
            'importo_iva',
            'importo_totale',
        ];

        $data = \App\Models\Finance::query();

        // Request validate
        request()->validate([
            'orderby' => ['in:' . implode(',', $request_search_array)],
            'ordertype' => ['in:asc,desc']
        ]);

        // Filtro RICERCA
        if (request('s')) {
            $data->where(function ($q) use ($request_search_array) {

                foreach ($request_search_array as $field) {
                    $q->orWhere($field, 'like', '%' . request('s') . '%');
                }

            });
        }

        // Filtro ORDINAMENTO
        if (request('orderby') && request('ordertype')) {
            $data->orderby(request('orderby'), strtoupper(request('ordertype')));
        }

        $data = $data->where('tipo', '=', 'passiva');
        $data = $data->orderBy('data', 'DESC');

        return $data;
    }

    private function category_calca_array()
    {
        $finances_last_year = $this->category_calc((date('Y') - 1) . '-01-01', (date('Y') - 1) . date('-m-d'));
        $finances_this_year = $this->category_calc(date('Y') . '-01-01', date('Y-m-d'));
        $finances_all_category = $this->category_calc((date('Y') - 1) . '-01-01', date('Y-m-d'));

        $category_array = array();
        foreach ($finances_all_category as $finance_category) {
            $category_array[$finance_category->categoria] = array(
                'categoria' => $finance_category->categoria,
                'diff' => 0,
                'profit' => true
            );
        }

        foreach ($finances_last_year as $finance_last_year) {

            foreach ($finances_this_year as $finance_this_year) {

                if ($finance_last_year->categoria == $finance_this_year->categoria) {

                    $diff = $finance_this_year->importo_netto - $finance_last_year->importo_netto;

                    if ($diff <= 0) {
                        $category_array[$finance_last_year->categoria]['profit'] = true;
                    } else if ($diff > 0) {
                        $category_array[$finance_last_year->categoria]['profit'] = false;
                    }

                    $category_array[$finance_last_year->categoria]['diff'] = $diff;

                    break;

                }
            }
        }

        usort($category_array, function ($a, $b) {
            return $a['diff'] <=> $b['diff'];
        });

        return array('categories_profit' => $category_array);
    }

    private function category_calc(string $date_from, string $date_to)
    {
        $finances = \App\Models\Finance::query();
        $finances = $finances->where('data', '>=', $date_from);
        $finances = $finances->where('data', '<=', $date_to);
        $finances = $finances->where('tipo', '=', 'passiva');
        $finances = $finances->groupBy('categoria');

        $finances = $finances->select(DB::raw('
            anno,
            categoria,
            SUM(importo_netto) as importo_netto
        '));

        $finances = $finances->orderBy('importo_netto', 'DESC');

        return $finances->get();
    }

    private function months_calc(string $tipo = 'attiva')
    {
        $finances = \App\Models\Finance::query();
        $finances = $finances->where('anno', '>=', date('Y') - 1);
        $finances = $finances->where('tipo', '=', $tipo);
        $finances = $finances->get();

        $today_month = date('n');
        $months_list = array();
        $months_calc = array();
        $years_diff = array();

        foreach ($finances as $finance) {

            for ($y = date('Y') - 1; $y <= date('Y'); $y++) {

                // Calcolo differenza tra anno precedente -------------------------
                if (!isset($years_diff[$y])) {
                    $finances_invoice = \App\Models\Finance::query()
                        ->where('data', '<=', $y . '-' . date('m-d'))
                        ->where('data', '>=', $y . '-01-01')
                        ->where('tipo_doc', '=', $tipo == 'attiva' ? 'fatture' : 'spesa')
                        ->where('tipo', '=', $tipo)
                        ->sum('importo_netto');

                    $finances_ndc = \App\Models\Finance::query()
                        ->where('data', '<=', $y . '-' . date('m'). '-' . date('d'))
                        ->where('data', '>', $y . '-01-01')
                        ->where('tipo_doc', '=', 'ndc')
                        ->where('tipo', '=', $tipo)
                        ->sum('importo_netto');

                    $years_diff[$y] = $finances_invoice - $finances_ndc;
                }
                // END - Calcolo differenza tra anno precedente -------------------------

                // Creo record per inserire label anno
                if (!isset($months_calc[$y]['y']))
                    $months_calc[$y]['y'] = $y;

                if (!isset($months_calc[$y]['total']))
                    $months_calc[$y]['total'] = 0;

                for ($m = 1; $m <= 12; $m++) {

                    // Creo la lista dei mesi
                    $months_list[$m] = date('F', mktime(0,0,0, $m));

                    // Creo record per inserire importo del mese
                    if (!isset($months_calc[$y]['m'][$m]))
                        $months_calc[$y]['m'][$m] = 0;

                    if (date('n', strtotime($finance->data)) == $m &&
                        date('Y', strtotime($finance->data)) == $y &&
                        $finance->tipo_doc == ($tipo == 'attiva' ? 'fatture' : 'spesa')) {

                        $months_calc[$y]['m'][$m] += $finance->importo_netto;
                        $months_calc[$y]['total'] += $finance->importo_netto;

                    }

                    if (date('n', strtotime($finance->data)) == $m &&
                        date('Y', strtotime($finance->data)) == $y &&
                        $finance->tipo_doc == 'ndc') {

                        $months_calc[$y]['m'][$m] -= $finance->importo_netto;
                        $months_calc[$y]['total'] -= $finance->importo_netto;

                    }

                }
            }
        }

        rsort($months_calc);

        return array(
            'today_month' => $today_month,
            'months_list' => $months_list,
            'months_calc' => $months_calc,
            'years_diff' => $years_diff[date('Y')] - $years_diff[date('Y') - 1],
        );
    }

    public function documentsGet()
    {
        $filter = new Filter();
        $filter->where('date', Operator::GTE, '2023-01-01');
//        $filter->where('date', Operator::LTE, '2023-03-01');
        $q = $filter->buildQuery();

        $fic = new FattureInCloudAPI();

        // ---------------------------------------

        $invoices = $fic->api('get.invoice', array('q' => $q));

        foreach ($invoices->getData() as $invoice) {

            $finance = \App\Models\Finance::query();
            $finance = $finance->where('fic_id', $invoice->getId())->first();

            if (!$finance)
                $finance = new \App\Models\Finance();

            $finance->fic_id = $invoice->getId();
            $finance->tipo_doc = 'fatture';
            $finance->tipo = 'attiva';
            $finance->numero = $invoice->getNumber();
            $finance->nome = $invoice->getEntity()->getName();
            $finance->anno = $invoice->getYear();
            $finance->data = $invoice->getDate();
            $finance->importo_netto = $invoice->getAmountNet();
            $finance->importo_iva = $invoice->getAmountVat();
            $finance->importo_totale = $invoice->getAmountGross();

            $finance->save();

        }

        // ---------------------------------------

        $invoices_received = $fic->api('get.invoice.received', array('q' => $q));

        foreach ($invoices_received->getData() as $invoice) {

            $finance = \App\Models\Finance::query();
            $finance = $finance->where('fic_id', $invoice->getId())->first();

            if (!$finance)
                $finance = new \App\Models\Finance();

            $finance->fic_id = $invoice->getId();
            $finance->tipo_doc = 'spesa';
            $finance->tipo = 'passiva';
            $finance->numero = $invoice->getInvoiceNumber();
            $finance->nome = $invoice->getEntity()->getName();
            $finance->anno = date('Y', $invoice->getDate()->getTimestamp());
            $finance->data = $invoice->getDate();
            $finance->categoria = $invoice->getCategory();
            $finance->importo_netto = $invoice->getAmountNet();
            $finance->importo_iva = $invoice->getAmountVat();
            $finance->importo_totale = $invoice->getAmountGross();

            $finance->save();

        }
    }
}
