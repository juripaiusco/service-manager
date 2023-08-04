<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class Finance extends Controller
{
    public function incoming()
    {
        $finances = \App\Models\Finance::query();
        $finances = $finances->where('anno', '>=', date('Y') - 1);
        $finances = $finances->where('tipo', '=', 'attiva');
        $finances = $finances->get();

        $today_month = date('n');
        $months_list = array();
        $months_incoming = array();
        $years_diff = array();

        foreach ($finances as $finance) {

            for ($y = date('Y') - 1; $y <= date('Y'); $y++) {

                // Calcolo differenza tra anno precedente -------------------------
                if (!isset($years_diff[$y])) {
                    $finances_invoice = \App\Models\Finance::query()
                        ->where('data', '<=', $y . '-' . date('m'). '-' . date('d'))
                        ->where('data', '>', $y . '-01-01')
                        ->where('tipo_doc', '=', 'fatture')
                        ->where('tipo', '=', 'attiva')
                        ->sum('importo_netto');

                    $finances_ndc = \App\Models\Finance::query()
                        ->where('data', '<=', $y . '-' . date('m'). '-' . date('d'))
                        ->where('data', '>', $y . '-01-01')
                        ->where('tipo_doc', '=', 'ndc')
                        ->where('tipo', '=', 'attiva')
                        ->sum('importo_netto');

                    $years_diff[$y] = $finances_invoice - $finances_ndc;
                }
                // END - Calcolo differenza tra anno precedente -------------------------

                // Creo record per inserire label anno
                if (!isset($months_incoming[$y]['y']))
                    $months_incoming[$y]['y'] = $y;

                if (!isset($months_incoming[$y]['total']))
                    $months_incoming[$y]['total'] = 0;

                for ($m = 1; $m <= 12; $m++) {

                    // Creo la lista dei mesi
                    $months_list[$m] = date('F', mktime(0,0,0, $m));

                    // Creo record per inserire importo del mese
                    if (!isset($months_incoming[$y]['m'][$m]))
                        $months_incoming[$y]['m'][$m] = 0;

                    if (date('n', strtotime($finance->data)) == $m &&
                        date('Y', strtotime($finance->data)) == $y &&
                        $finance->tipo_doc == 'fatture') {

                        $months_incoming[$y]['m'][$m] += $finance->importo_netto;
                        $months_incoming[$y]['total'] += $finance->importo_netto;

                    }

                    if (date('n', strtotime($finance->data)) == $m &&
                        date('Y', strtotime($finance->data)) == $y &&
                        $finance->tipo_doc == 'ndc') {

                        $months_incoming[$y]['m'][$m] -= $finance->importo_netto;
                        $months_incoming[$y]['total'] -= $finance->importo_netto;

                    }

                }
            }
        }

        rsort($months_incoming);

        return Inertia::render('Finance/Incoming', [
            'data' => array(
                'today_month' => $today_month,
                'months_list' => $months_list,
                'months_incoming' => $months_incoming,
                'years_diff' => $years_diff[date('Y')] - $years_diff[date('Y') - 1],
            )
        ]);
    }

    public function documentsGet()
    {
        $invoices = new FattureInCloudAPI();
        $invoices = $invoices->api('get.documents');

        foreach ($invoices->getData() as $invoice) {

//            dd($invoice);
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
    }

    public function outcoming()
    {
        return Inertia::render('Finance/Outcoming');
    }
}
