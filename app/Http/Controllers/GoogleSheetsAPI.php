<?php

namespace App\Http\Controllers;

use App\Model\Fattura;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class GoogleSheetsAPI extends Controller
{
    /**
     * Returns an authorized API client.
     * @return Google_Client the authorized client object
     */
    public function getClient()
    {
        $credentials_path = Storage::disk('public')->path('google_sheets_api/credentials.json');

        $client = new \Google_Client();
        $client->setApplicationName('serviceM');
        $client->setScopes(\Google_Service_Sheets::SPREADSHEETS);
        $client->setAuthConfig($credentials_path);
        $client->setAccessType('offline');
        $client->setPrompt('select_account consent');

        return $client;
    }

    public function update()
    {
        /**
         * Creo un array con la definizione delle celle per mese
         */
        $alpha = array('B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M');

        $row_in = 3;
        $row_out = 4;

        for ($m = 0; $m < 12; $m++) {

            $range_array[$m + 1] = array(
                'in' => $alpha[$m] . $row_in,
                'out' => $alpha[$m] . $row_out
            );

        }

        /**
         * Prendo i dati da fattureincloud.it
         */
        $fic = new FattureInCloudAPI();
        $fatture_attive = $fic->get(
            'fatture',
            'lista',
            array(
                'anno' => env('GOOGLE_SHEETS_YEAR'),
                'data_inizio' => '01/01/' . env('GOOGLE_SHEETS_YEAR'),
                'data_fine' => '31/12/' . env('GOOGLE_SHEETS_YEAR')
            )
        );

        $fic = new FattureInCloudAPI();
        $ndc_attive = $fic->get(
            'ndc',
            'lista',
            array(
                'anno' => env('GOOGLE_SHEETS_YEAR'),
                'data_inizio' => '01/01/' . env('GOOGLE_SHEETS_YEAR'),
                'data_fine' => '31/12/' . env('GOOGLE_SHEETS_YEAR')
            )
        );

        // Fattura in cloud unice Fatture e Note di credito solo per i documenti passivi,
        // per i documenti attivi li divice, quindi utilizzo array_merge.
        $fatture_attive = array_merge($fatture_attive, $ndc_attive);

        $fic = new FattureInCloudAPI();
        $fatture_passive = $fic->get(
            'acquisti',
            'lista',
            array(
                'anno' => env('GOOGLE_SHEETS_YEAR'),
                'data_inizio' => '01/01/' . env('GOOGLE_SHEETS_YEAR'),
                'data_fine' => '31/12/' . env('GOOGLE_SHEETS_YEAR')
            )
        );

        /**
         * Calcolo i totali per mese delle fatture attive e passive
         * in più calcolo le tasse a debito e a credito
         */
        $tot_month_attivo = array();
        $iva_debito = array();

        foreach ($fatture_attive as $fattura) {

            // Calcolo importo netto in attivo per mese
            $month_n = date('n', strtotime(str_replace('/', '-', $fattura['data'])));

            if (!isset($tot_month_attivo[$range_array[$month_n]['in']])) {
                $tot_month_attivo[$range_array[$month_n]['in']] = 0;
            }

            // Calcolo tasse (IVA) a debito per mese
            if (!isset($iva_debito[$month_n])) {
                $iva_debito[$month_n] = 0;
            }

            // Correggo l'importo attivo in base alle note di credito attive
            switch ($fattura['tipo']) {
                case 'fatture':
                    $tot_month_attivo[$range_array[$month_n]['in']] += $fattura['importo_netto'];

                    // Verifica se PA per Split Payment
                    if ($fattura['PA_tipo_cliente'] != 'PA') {
                        $iva_debito[$month_n] += $fattura['importo_totale'] - $fattura['importo_netto'];
                    }
                    break;

                case 'ndc':
                    $tot_month_attivo[$range_array[$month_n]['in']] -= $fattura['importo_netto'];

                    // Verifica se PA per Split Payment
                    if ($fattura['PA_tipo_cliente'] != 'PA') {
                        $iva_debito[$month_n] -= $fattura['importo_totale'] - $fattura['importo_netto'];
                    }
                    break;
            }

        }

        // Sistemo l'array dei totali per importarli correttamente in Google Sheets
        $tot_month_attivo = array_reverse($tot_month_attivo, true);

        // - - -

        $tot_month_passivo = array();
        $iva_credito = array();

        foreach ($fatture_passive as $fattura) {

            // Calcolo importo netto in passivo per mese
            $month_n = date('n', strtotime(str_replace('/', '-', $fattura['data'])));

            if (!isset($tot_month_passivo[$range_array[$month_n]['out']])) {
                $tot_month_passivo[$range_array[$month_n]['out']] = 0;
            }

            // Calcolo tasse (IVA) a credito per mese
            if (!isset($iva_credito[$month_n])) {
                $iva_credito[$month_n] = 0;
            }

            // Correggo l'importo passivo in base alle note di credito passive
            switch ($fattura['tipo']) {
                case 'spesa':
                    $tot_month_passivo[$range_array[$month_n]['out']] -= $fattura['importo_netto'];
                    $iva_credito[$month_n] += $fattura['importo_iva'];
                    break;

                case 'ndc':
                    $tot_month_passivo[$range_array[$month_n]['out']] += $fattura['importo_netto'];
                    $iva_credito[$month_n] -= $fattura['importo_iva'];
                    break;
            }

        }

        // Sistemo l'array dei totali per importarli correttamente in Google Sheets
        $tot_month_passivo = array_reverse($tot_month_passivo, true);

        // - - -

        /**
         * Allineamento Keys dei totali attivi e passivi per mese
         */
        if (count($tot_month_attivo) > count($tot_month_passivo)) {

            $count_m = count($tot_month_attivo);

        } else {

            $count_m = count($tot_month_passivo);
        }

        for ($i = 0; $i < $count_m; $i++) {

            if (!isset($tot_month_attivo[$range_array[$i + 1]['in']])) {
                $tot_month_attivo[$range_array[$i + 1]['in']] = 0;
            }

            if (!isset($tot_month_passivo[$range_array[$i + 1]['out']])) {
                $tot_month_passivo[$range_array[$i + 1]['out']] = 0;
            }

        }

        // Sistemo l'array Keys dei totali per importarli correttamente in Google Sheets
        $k_tot_month_attivo = array_keys($tot_month_attivo);
        $k_tot_month_passivo = array_keys($tot_month_passivo);

        // - - -

        // Calcolo l'iva per mese
        $iva_count = count($iva_credito) > count($iva_debito) ? count($iva_credito) : count($iva_debito);
        $iva_month = array();

        for ($i = 1; $i <= $iva_count; $i++) {

            if (!isset($iva_debito[$i])) {
                $iva_debito[$i] = 0;
            }

            if (!isset($iva_credito[$i])) {
                $iva_credito[$i] = 0;
            }

            $iva_month[$i] = $iva_credito[$i] - $iva_debito[$i];

        }

        // Calcolo l'iva per singolo trimestre
        $iva_trimestre = array();

        foreach (array_chunk($iva_month, 3) as $v) {

            $iva_trimestre[] = array_reduce($v, function ($sum, $item){

                $sum += $item;

                return $sum;

            });

        }

        // Verifico la data finale del trimestre e la confronto con la data attuale
        // se la data attuale supera il trimestre di 10 giorni, viene azzerato il
        // valore IVA del trimestre, in questo modo non viene inserito il valore.
        //
        // Questo viene fatto perché se il commercialista comunica un importo
        // diverso di IVA da versare, quest'ultimo non verrà sovrascritto.
        foreach (array_chunk($iva_month, 3, true) as $k => $v) {

            $keys = array_keys($v);

            $data_fine_trimestre = date(
                'Y-m-t',
                mktime(0, 0, 0, $keys[count($keys) - 1], 1, env('GOOGLE_SHEETS_YEAR'))
            );

            $date_trimestre = new Carbon($data_fine_trimestre);
            $date_now = Carbon::now();

            if ($date_trimestre->diff($date_now)->days > 10 &&
                $date_trimestre->timestamp < $date_now->timestamp) {
                $iva_trimestre[$k] = 0;
            }

        }

        /**
         * Inserisco i dati in Google Sheets
         */
        $data = array();

        // Range delle fatture attive e passive
        $range = env('GOOGLE_SHEETS_YEAR') . '!' . $k_tot_month_attivo[0] . ':' . $k_tot_month_passivo[count($k_tot_month_passivo) - 1];
        $values = [
            array_values($tot_month_attivo),
            array_values($tot_month_passivo)
        ];
        $data[] = new \Google_Service_Sheets_ValueRange([
            'range' => $range,
            'values' => $values
        ]);

        // Range dei trimestri IVA da versare
        foreach (array_chunk($alpha, 3) as $k => $v) {

            // Verifico che il valore del trimestre esista e che sia maggiore di zero,
            // perché calcolo l'IVA da versare fino ad una settimana dopo la fine del
            // trimestre, in questo modo se il commercialista comunica un importo
            // diverso di IVA da versare, quest'ultimo non verrà sovrascritto.
            if (isset($iva_trimestre[$k]) && abs($iva_trimestre[$k]) > 0) {

                $range = env('GOOGLE_SHEETS_YEAR') . '!' . $v[0] . '7';
                $values = [
                    [$iva_trimestre[$k]]
                ];
                $data[] = new \Google_Service_Sheets_ValueRange([
                    'range' => $range,
                    'values' => $values
                ]);
            }
        }

        $client = $this->getClient();
        $service = new \Google_Service_Sheets($client);
        $spreadsheetId = env('GOOGLE_SHEETS_ID');

        $body = new \Google_Service_Sheets_BatchUpdateValuesRequest([
            'valueInputOption' => 'RAW',
            'data' => $data
        ]);

        $result = $service->spreadsheets_values->batchUpdate($spreadsheetId, $body);

        /*$body = new \Google_Service_Sheets_ValueRange([
            'values' => $values
        ]);

        $params = [
            'valueInputOption' => 'RAW'
        ];

        $result = $service->spreadsheets_values->update(
            $spreadsheetId,
            $range,
            $body,
            $params
        );*/

        $this->scriptableWriteJSON();
    }

    /**
     * Scrivo il file JSON per essere letto da Scriptable
     * così da poter usare la widget sul iSO e MacOS
     */
    public function scriptableWriteJSON()
    {
        // Mostro i dati del trimestre per almeno 10 giorni passato il trimestre,
        // così da poter modificare eventuali contabilità e poterle avere sempre sotto controllo.
        $timeJSON = mktime(0, 0, 0, date('m'), (date('d') - 10), env('GOOGLE_SHEETS_YEAR'));
        $alpha = array('B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M');

        $row_in = 3;
        $row_out = 4;
        $row_profit = 17;

        $client = $this->getClient();
        $service = new \Google_Service_Sheets($client);
        $spreadsheetId = env('GOOGLE_SHEETS_ID');

        $c = 0;

        foreach (array_chunk($alpha, 3) as $k => $v) {

            $c += 3;

            if ($c > date('m', $timeJSON) - 1) {
                $periodo = $k + 1;
                $alphaUtile = $v[0];
                break;
            }
        }

        $params = array(
            'ranges' => [
                env('GOOGLE_SHEETS_YEAR') . '!' . $alpha[date('m', $timeJSON) - 1] . $row_in,
                env('GOOGLE_SHEETS_YEAR') . '!' . $alpha[date('m', $timeJSON) - 1] . $row_out,
                env('GOOGLE_SHEETS_YEAR') . '!' . $alphaUtile . $row_profit,
            ]
        );
        $result = $service->spreadsheets_values->batchGet($spreadsheetId, $params);


        $FicData = new FattureInCloudData();
        $from_year = date('Y') - 1;

        $array_income_by_months = $FicData->dataByMonth(array(
            'from_year' => $from_year,
            'tipo' => 'attiva',
            'tipo_doc' => 'fatture'
        ));
        $array_income_comparison_by_year = $FicData->yearComparison($array_income_by_months);

        // - - -

        $array_costs_months_by_category = $FicData->catDataByMonths(array(
            'from_year' => $from_year,
            'tipo' => 'passiva',
            'tipo_doc' => 'spesa'
        ));
        $array_costs_comparison_by_category = $FicData->catComparison($array_costs_months_by_category);

        $array_costs_by_months = $FicData->dataByMonth(array(
            'from_year' => $from_year,
            'tipo' => 'passiva',
            'tipo_doc' => 'spesa'
        ));
        $array_costs_comparison_by_year = $FicData->yearComparison($array_costs_by_months);

        // - - -

        $dataArray = array(
            'entrate_anno_vs' => array(
                'value' => number_format(abs($array_income_comparison_by_year['comparison']), 2, ',', '.'),
                'sign' => $array_income_comparison_by_year['comparison'] < 0 ? '-' : '+'
            ),
            'uscite_anno_vs' => array(
                'value' => number_format(abs($array_costs_comparison_by_year['comparison']), 2, ',', '.'),
                'sign' => $array_costs_comparison_by_year['comparison'] < 0 ? '-' : '+'
            ),

            'trimestre' => array(
                'periodo' => $periodo,
                'value' => $result->valueRanges[2]->values[0][0],
            ),

            'entrate_mese_corrente' => array(
                'value' => $result->valueRanges[0]->values[0][0],
                'vs' => 'n/d',
                'vs_sign' => '-'
            ),
            'uscite_mese_corrente' => array(
                'value' => $result->valueRanges[1]->values[0][0],
                'vs' => 'n/d',
                'vs_sign' => '-'
            ),

            'entrate' => $result->valueRanges[0]->values[0][0],
            'uscite' => $result->valueRanges[1]->values[0][0],
        );

        foreach ($array_costs_comparison_by_category as $cat => $comparison) {

            $dataArray['category'][] = array(
                'name' => $cat,
                'value' => number_format(abs($comparison['comparison']), 2, ',', '.'),
                'sign' => $comparison['comparison'] <= 0 ? '-' : '+'
            );

            if ($comparison['comparison'] > 0) {

                $dataArray['category_negative'][] = array(
                    'name' => $cat,
                    'value' => number_format($comparison['comparison'], 2, ',', '.')
                );

            }

            if ($comparison['comparison'] <= 0) {

                $dataArray['category_positive'][] = array(
                    'name' => $cat,
                    'value' => number_format($comparison['comparison'], 2, ',', '.')
                );

            }
        }

        /**
         * Per esempio se siamo nell'ultimo trimestre e inizia il nuovo anno, per 10 giorni il JSON
         * deve mostrare i dati dell'ultimo trimestre dell'anno precedente.
         */
        if (preg_replace('/\D/', '', $dataArray['entrate']) != ''
            || preg_replace('/\D/', '', $dataArray['uscite']) != '') {

            Storage::disk('public')->put('scriptable.json', json_encode($dataArray));

        }
    }

    public function scriptableGetJSON()
    {
        return Storage::disk('public')->get('scriptable.json');
    }

    public function pushOuts_2()
    {
        $fatture = Fattura::where('tipo', 'passiva')
                          ->where('data', '>=', '2020-01-01')
                          ->orderby('categoria')
                          ->orderby('data')
                          ->get();

        $y_start = substr($fatture[0]->data, 0, 4);
        $y_end = substr($fatture[count($fatture) - 1]->data, 0, 4);

        foreach ($fatture as $fattura) {

            /*$array_fatture[$fattura->categoria][substr($fattura->data, 0, 4)][] = array(
                'nome' => $fattura->nome,
                'data' => $fattura->data,
                'importo_netto' => $fattura->importo_netto,
                'importo_iva' => $fattura->importo_iva,
                'importo_totale' => $fattura->importo_totale
            );*/

            for ($m = 1; $m <= 12; $m++) {

                $data_m_i = intval($m);

                if ($data_m_i < 10) {
                    $data_m_i = '0' . $data_m_i;
                }

                for ($y = 2020; $y <= 2021; $y++) {

                    if (!isset($array_fatture[$fattura->categoria][$y . $data_m_i])) {
                        $array_fatture[$fattura->categoria][$y . $data_m_i] = 0;
                    }

                    if (substr(
                            str_replace('-', '', $fattura->data)
                            , 0, 6) == ($y . $data_m_i)) {

                        $array_fatture[$fattura->categoria][$y . $data_m_i] += $fattura->importo_netto;

                    }

                }
            }

        }

//        dd($array_fatture);

        $gSheets_values = array();

        $gSheets_values[0][0]= '';

        $c = 1;
        foreach ($array_fatture as $k => $v) {

            $gSheets_values[$c] = array($k);

            foreach ($v as $k_m => $month) {

                $gSheets_values[$c][$k_m] = $month;

            }

            $gSheets_values[$c] = array_values($gSheets_values[$c]);

            $c++;
        }

//        dd(count($gSheets_values[1]));

        /*$c = 1;
        for ($i = $y_start; $i <= $y_end; $i++) {
            $gSheets_values[0][$c] = intval($i);
            $c++;
        }*/

        // - - -

        $alpha = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
        $data = array();

        // Range categorie
        $range = 'Foglio2!A1:' . $alpha[count($gSheets_values[1])] . (count($gSheets_values) + 1);
        $values = $gSheets_values;
        $data[] = new \Google_Service_Sheets_ValueRange([
            'range' => $range,
            'values' => $values
        ]);

        $client = $this->getClient();
        $service = new \Google_Service_Sheets($client);
        $spreadsheetId = env('GOOGLE_SHEETS_TEST_ID');

        $body = new \Google_Service_Sheets_BatchUpdateValuesRequest([
            'valueInputOption' => 'RAW',
            'data' => $data
        ]);

        $result = $service->spreadsheets_values->batchUpdate($spreadsheetId, $body);
    }

    public function pushOuts_1()
    {
        $fatture = Fattura::where('tipo', 'passiva')
                          ->orderby('categoria')
                          ->orderby('data')
                          ->get();

        $y_start = substr($fatture[0]->data, 0, 4);
        $y_end = substr($fatture[count($fatture) - 1]->data, 0, 4);

        foreach ($fatture as $fattura) {

            /*$array_fatture[$fattura->categoria][substr($fattura->data, 0, 4)][] = array(
                'nome' => $fattura->nome,
                'data' => $fattura->data,
                'importo_netto' => $fattura->importo_netto,
                'importo_iva' => $fattura->importo_iva,
                'importo_totale' => $fattura->importo_totale
            );*/

            for ($i = $y_start; $i <= $y_end; $i++) {

                $y = intval($i);

                if (!isset($array_fatture[$fattura->categoria][$y])) {
                    $array_fatture[$fattura->categoria][$y] = 0;
                }

                if (substr($fattura->data, 0, 4) == $y) {

                    $array_fatture[$fattura->categoria][$y] += $fattura->importo_netto;

                }
            }

        }

        $gSheets_values = array();

        $gSheets_values[0][0]= '';

        $c = 1;
        foreach ($array_fatture as $k => $v) {

            $gSheets_values[$c] = array($k);

            for ($i = $y_start; $i <= $y_end; $i++) {

                $gSheets_values[$c][$i] = $v[$i];

            }

            $gSheets_values[$c] = array_values($gSheets_values[$c]);

            $c++;
        }

        $c = 1;
        for ($i = $y_start; $i <= $y_end; $i++) {
            $gSheets_values[0][$c] = intval($i);
            $c++;
        }

//        dd($gSheets_values);

        // - - -

        $alpha = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O');
        $data = array();

        // Range categorie
        $range = 'Foglio1!A1:' . $alpha[$y_end - $y_start + 1] . (count($gSheets_values) + 1);
        $values = $gSheets_values;
        $data[] = new \Google_Service_Sheets_ValueRange([
            'range' => $range,
            'values' => $values
        ]);

        $client = $this->getClient();
        $service = new \Google_Service_Sheets($client);
        $spreadsheetId = env('GOOGLE_SHEETS_TEST_ID');

        $body = new \Google_Service_Sheets_BatchUpdateValuesRequest([
            'valueInputOption' => 'RAW',
            'data' => $data
        ]);

        $result = $service->spreadsheets_values->batchUpdate($spreadsheetId, $body);
    }
}
