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

    public function outcoming(Request $request)
    {
        return Inertia::render('Finance/Outcoming', [
            'data' => array_merge(
                $this->months_calc('passiva'),
                $this->category_calc_array(),
                array(
                    'invoice_last_year' => $this->invoice_get()
                        ->where('anno', '=', date('Y') - 1)
                        ->get()
                ),
                array(
                    'invoice_this_year' => $this->invoice_get()
                        ->where('anno', '=', date('Y'))
                        ->get()
                ),
                array( 'month_details' => $this->month_details($request['month_selected']) ),
                array( 'this_year' => date('Y') ),
                array( 'last_year' => date('Y') - 1 ),
            ),
            'filters' => request()->all(['s', 'orderby', 'ordertype'])
        ]);
    }

    public function outcoming_category($category)
    {
        $finances = \App\Models\Finance::query();
        $finances = $finances->where('categoria', $category);
        $finances = $finances->orderBy('data', 'DESC');
        $finances = $finances->get();

        $category_count = array();

        foreach ($finances as $finance) {

            $invoices[$finance->anno . ' '][] = $finance;

            for ($y = date('Y'); $y >= $finances[count($finances) - 1]->anno; $y--) {

                if (!isset($category_count[$y . ' ']['total']))
                    $category_count[$y . ' ']['total'] = 0;

                for ($m = 1; $m <= 12; $m++) {

                    if (!isset($months_list[$m]))
                        $months_list[$m] = date('F', mktime(0,0,0, $m));

                    if (!isset($category_count[$y . ' ']['m'][$m]))
                        $category_count[$y . ' ']['m'][$m] = 0;

                    if (substr($finance->data, 0, 7) ==
                        date('Y-m', mktime(0, 0, 0, $m, 1, $y))) {

                        if ($finance->tipo_doc == 'spesa') {

                            $category_count[$y . ' ']['m'][$m] += $finance->importo_netto;
                            $category_count[$y . ' ']['total'] += $finance->importo_netto;

                        } else if ($finance->tipo_doc == 'ndc') {

                            $category_count[$y . ' ']['m'][$m] -= $finance->importo_netto;
                            $category_count[$y . ' ']['total'] -= $finance->importo_netto;
                        }

                    }

                }
            }
        }

        foreach ($this->category_calc_array()['categories_profit'] as $category_calc) {

            if ($category_calc['categoria'] == $category) {

                $category_diff = $category_calc['diff'];
                break;
            }
        }

        return Inertia::render('Finance/Outcoming/Category', [
            'data' => array(
                'today_month' => date('n'),
                'months_list' => $months_list,
                'category' => $category,
                'category_count' => $category_count,
                'category_diff' => $category_diff,
                'invoices' => $invoices,
            ),
            'filters' => request()->all(['s', 'orderby', 'ordertype'])
        ]);
    }

    private function month_details($month_selected)
    {
        $month_details = array(
            'month_details' => null,
            'month_details_by_name' => null,
            'month_details_diff' => null,
            'month_selected' => null,
        );

        if ($month_selected) {

            $month_details = \App\Models\Finance::query();
            $month_details = $month_details->whereMonth('data', '=', $month_selected);
            $month_details = $month_details->where(function ($q) {
                $q->where('anno', '=', date('Y'));
                $q->orWhere('anno', '=', date('Y') - 1);
            });
            $month_details = $month_details->where('tipo', '=', 'passiva');
            $month_details = $month_details->orderBy('nome');
            $month_details = $month_details->get();

            $month_details_by_name = array();

            for ($y = date('Y') - 1; $y <= date('Y'); $y++) {

                if (!isset($month_details_diff_array[$y]))
                    $month_details_diff_array[$y] = 0;

                foreach ($month_details as $month_detail) {

                    if (!isset($month_details_by_name[$month_detail->nome][$y])) {

                        $month_details_by_name[$month_detail->nome][$y] = array(
                            'nome' => $month_detail->nome,
                            'anno' => $y,
                            'importo_netto' => 0,
                            'importo_iva' => 0,
                            'importo_totale' => 0,
                        );

                    }
                }
            }

            foreach ($month_details as $month_detail) {

                $n = $month_detail->nome;
                $y = $month_detail->anno;

                $month_details_by_name[$n][$y]['importo_netto'] += $month_detail->importo_netto;
                $month_details_by_name[$n][$y]['importo_iva'] += $month_detail->importo_iva;
                $month_details_by_name[$n][$y]['importo_totale'] += $month_detail->importo_totale;

                $month_details_diff_array[$y] += $month_detail->importo_netto;
            }

            $month_details_diff = $month_details_diff_array[date('Y')] - $month_details_diff_array[date('Y') - 1];

            sort($month_details_by_name);

            $month_details = array(
                'month_details' => $month_details,
                'month_details_by_name' => $month_details_by_name,
                'month_details_diff' => $month_details_diff,
                'month_selected' => $month_selected,
            );

        }

        return $month_details;
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

    private function category_calc_array()
    {
        $finances_last_year = $this->category_calc((date('Y') - 1) . '-01-01', (date('Y') - 1) . date('-m-t'));
        $finances_this_year = $this->category_calc(date('Y') . '-01-01', date('Y-m-t'));
        $finances_all_category = $this->category_calc((date('Y') - 1) . '-01-01', date('Y') . '-12-31');

        $category_array = array();

        foreach ($finances_all_category as $finance_category) {
            $category_array[$finance_category->categoria] = array(
                'categoria' => $finance_category->categoria,
                'diff' => 0,
                'profit' => true
            );
        }

        foreach ($category_array as $category) {

            foreach ($finances_last_year as $finance_last_year) {

                if ($finance_last_year->categoria == $category['categoria']) {

                    $finance_last_year_category_cost = $finance_last_year->importo_netto;
                    break;

                } else {

                    $finance_last_year_category_cost = 0;
                }

            }

            foreach ($finances_this_year as $finance_this_year) {

                if ($finance_this_year->categoria == $category['categoria']) {

                    $finance_this_year_category_cost = $finance_this_year->importo_netto;
                    break;

                } else {

                    $finance_this_year_category_cost = 0;
                }

            }

            $diff = $finance_this_year_category_cost - $finance_last_year_category_cost;

            if ($diff <= 0) {
                $category_array[$category['categoria']]['profit'] = true;
            } else if ($diff > 0) {
                $category_array[$category['categoria']]['profit'] = false;
            }

            $category_array[$category['categoria']]['diff'] = $diff;

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
            SUM(IF (tipo_doc = \'ndc\', importo_netto * -1, importo_netto)) as importo_netto
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
                        ->where('data', '<=', $y . '-' . date('m-t'))
                        ->where('data', '>=', $y . '-01-01')
                        ->where('tipo_doc', '=', $tipo == 'attiva' ? 'fatture' : 'spesa')
                        ->where('tipo', '=', $tipo)
                        ->sum('importo_netto');

                    $finances_ndc = \App\Models\Finance::query()
                        ->where('data', '<=', $y . '-' . date('m'). '-' . date('t'))
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

    /**
     *  Recupero i dati da FIC e li importo nel DB
     *
     * @return void
     */
    public function documentsGet()
    {
        $filter = new Filter();
        $filter->where('date', Operator::GTE, env('GOOGLE_SHEETS_YEAR') . '-01-01');
//        $filter->where('date', Operator::LTE, '2023-03-01');
        $q = $filter->buildQuery();

        $fic = new FattureInCloudAPI();

        // ---------------------------------------

        $invoices = $fic->api('get.documents', array(
            'type' => 'invoice',
            'q' => $q
        ));

        $credit_note = $fic->api('get.documents', array(
            'type' => 'credit_note',
            'q' => $q
        ));

        $expense = $fic->api('get.documents.received', array(
            'type' => 'expense',
            'q' => $q
        ));

        $passive_credit_note = $fic->api('get.documents.received', array(
            'type' => 'passive_credit_note',
            'q' => $q
        ));

        $documents = array_merge(
            $invoices->getData(),
            $credit_note->getData(),
            $expense->getData(),
            $passive_credit_note->getData()
        );

        /**
         * Elimino le fatture presenti nel database,
         * ma non presenti in Fatture in Cloud.
         */
        $this->documentsClear(array(
            $invoices->getData(),
            $credit_note->getData(),
            $expense->getData(),
            $passive_credit_note->getData()
        ));

        /**
         * Inserisco i dati fattura
         * SE la fattura esiste verrà aggiornata
         * SE la fattura non esiste verrà creata
         */
        foreach ($documents as $document) {

            $finance = \App\Models\Finance::query();
            $finance = $finance->where('fic_id', $document->getId())->first();

            if (!$finance)
                $finance = new \App\Models\Finance();

            $finance->fic_id = $document->getId();
            $finance->nome = $document->getEntity()->getName();
            $finance->data = $document->getDate();
            $finance->importo_netto = $document->getAmountNet();
            $finance->importo_iva = $document->getAmountVat();
            $finance->importo_totale = $document->getAmountGross();

            if ($document->getType() == 'expense' ||
                $document->getType() == 'passive_credit_note') {

                $finance->numero = $document->getInvoiceNumber();
                $finance->tipo_doc = $document->getType() == 'expense' ? 'spesa' : 'ndc';
                $finance->tipo = 'passiva';
                $finance->anno = date('Y', $document->getDate()->getTimestamp());
                $finance->categoria = $document->getCategory();

            } else {

                $finance->numero = $document->getNumber();
                $finance->tipo_doc = $document->getType() == 'invoice' ? 'fatture' : 'ndc';
                $finance->tipo = 'attiva';
                $finance->anno = $document->getYear();
            }

            $finance->save();

        }
    }

    /**
     * Elimino le fatture presenti nel database,
     * ma non presenti in Fatture in Cloud.
     *
     * Prendo la data più lontana in base al tipo di documento preso da Fatture in Cloud,
     * da quella data controllo se i docucmenti in DB sono prensenti o meno. In caso di
     * documenti non prensenti in FIC, questi vengono eliminati dal database.
     *
     * $documents_array = Inserire i dati di FIC all'interno di un array
     *
     * @return void
     */
    private function documentsClear($documents_array)
    {
        // Inizio la ricerca
        foreach ($documents_array as $documents) {

            $index = count($documents) - 1;

            if (isset($documents[$index])) {

                // Definisco la data di partenza per la ricerca
                $date = $documents[$index]->getDate();

                // Definisco il tipo di documento per la ricerca
                if ($documents[$index]->getType() == 'expense' ||
                    $documents[$index]->getType() == 'passive_credit_note') {

                    $tipo_doc = $documents[$index]->getType() == 'expense' ? 'spesa' : 'ndc';
                    $tipo = 'passiva';

                } else {

                    $tipo_doc = $documents[$index]->getType() == 'invoice' ? 'fatture' : 'ndc';
                    $tipo = 'attiva';
                }

                // Recupero i documenti salvati nel DB
                $finances = \App\Models\Finance::query();
                $finances->where('data', '>=', $date);
                $finances->where('tipo_doc', $tipo_doc);
                $finances->where('tipo', $tipo);
                $finances->orderBy('data', 'ASC');
                $finances_get = $finances->get();

                // Confronto l'array dati di Fatture in Cloud con i dati salvati in DB
                foreach ($finances_get as $finance) {

                    $finance_exists = false;

                    foreach ($documents as $document) {

                        if ($finance->fic_id == $document->getId()) {

                            $finance_exists = true;
                            break;
                        }

                    }

                    // Se il documento non esiste in Fatture in Cloud viene eliminato
                    if (!$finance_exists) {
                        \App\Models\Finance::destroy($finance->id);
                    }
                }

            }
        }
    }

    public function dataByMonth($args = array())
    {
        $array_getDataByMonths = $this->getDataByMonths($args);

        foreach ($array_getDataByMonths as $cat => $array) {

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

    public function getDataByMonths($args = array(
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

            if (!isset($array_docs_months_by_category[$ndc->categoria][$ndc->anno][$data_i])) {
                $array_docs_months_by_category[$ndc->categoria][$ndc->anno][$data_i] = 0;
            }

            $array_docs_months_by_category[$ndc->categoria][$ndc->anno][$data_i] -= $ndc->importo_netto;

        }

        if (isset($args['categoria'])) {

            $return = $array_docs_months_by_category[$args['categoria']];

        } else {

            $return = $array_docs_months_by_category;
        }

        return $return;
    }

    public function getDataByCat($args = array(
        'from_year' => 0,
        'tipo' => '',
        'tipo_doc' => '',
    ))
    {
        $dataByCategory = \App\Models\Finance::query()
            ->where('tipo', $args['tipo'])
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

        if (!isset($array_comparison[$year_comparison])) {
            $array_comparison[$year_comparison] = 0;
        }

        if (!isset($array_comparison[$year_comparison - 1])) {
            $array_comparison[$year_comparison - 1] = 0;
        }

        $array_comparison['comparison'] = $array_comparison[$year_comparison] - $array_comparison[$year_comparison - 1];

        return $array_comparison;
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
}
