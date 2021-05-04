<?php

namespace App\Http\Controllers;

use App\Model\CustomersServices;
use App\Model\CustomersServicesDetails;
use App\Model\FicDoc;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class FattureInCloudAPI extends Controller
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

    /**
     * Creazione fattura e invio tramite email.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $customer_service_id = $request->input('customer_service_id');
        $pagamento_saldato = $request->input('pagamento_saldato');
        $date_doc = $request->input('date_doc');
        $send_email = $request->input('send_email');

        /*$info_account = $this->api(
            'info/account',
            array(
                'api_uid' => env('FIC_API_UID'),
                'api_key' => env('FIC_API_KEY'),
                'campi' => [
                    "lista_metodi_pagamento"
                ]
            )
        );

        dd($info_account);*/

        /**
         * Fatture in Cloud
         * Recupero dati dei proditti
         */
        $prodotti_lista = $this->api('prodotti/lista');

        /**
         * Recupero dati per la fattura
         */
        $customer_service = CustomersServices::with('customer')
                                             ->with('details')
                                             ->find($customer_service_id);

        $customers_services_details = CustomersServicesDetails::with('service')
                                                              ->where('customer_service_id', $customer_service_id)
                                                              ->orderBy('price_sell', 'DESC')
                                                              ->orderBy('reference', 'ASC')
                                                              ->get();

        foreach ($customers_services_details as $customer_service_detail) {

            $index = $customer_service_detail->service_id . $customer_service_detail->price_sell;

            if (!isset($array_rows[$index])) {

                /**
                 * Ricerca corrispondenza con l'ID del prodotto di Fatture in Cloud
                 */
                $fic_prodotto_id = 0;

                foreach ($prodotti_lista['lista_prodotti'] as $fic_prodotto) {

                    if ($fic_prodotto['cod'] == $customer_service_detail->service->fic_cod) {

                        $fic_prodotto_id = $fic_prodotto['id'];
                        $fic_prodotto_cod = $fic_prodotto['cod'];
                        $fic_prodotto_categoria = $fic_prodotto['categoria'];
                        break;

                    }

                }

                /**
                 * Array con i valori da inserire nella riga prodotto
                 */
                $array_rows[$index] = array(
                    'fic_id' => $fic_prodotto_id,
                    'fic_cod' => $fic_prodotto_cod,
                    'fic_categoria' => $fic_prodotto_categoria,
                    'fic_nome' => $customer_service_detail->service->name_customer_view,
                    'price_sell' => $customer_service_detail->price_sell,
                    'reference' => array()
                );

            }

            $array_rows[$index]['reference'][] = $customer_service_detail->reference;
        }

        /**
         * Fatture in Cloud
         * Creazione prodotti per la nuova fattura
         */
        $lista_articoli = array();
        foreach ($array_rows as $k => $a) {

            $a_unique = array_unique($a['reference']);
            sort($a_unique);
            $desc = implode("\n", $a_unique);

            $lista_articoli[] = array(
                'id' => $a['fic_id'],
                'codice' => $a['fic_cod'],
                'categoria' => $a['fic_categoria'],
                'nome' => $a['fic_nome'],
                'descrizione' => $desc,
                'quantita' => count($a['reference']),
                'prezzo_netto' => $a['price_sell'],
                'cod_iva' => 0,
            );

        }

        /**
         * Fatture in Cloud
         * Creazione nuova fattura
         */
        $fattura_nuova = $this->api(
            'fatture/nuovo',
            array(
                'nome' => $customer_service->customer_name ? $customer_service->customer_name : $customer_service->customer->name,
                'piva' => $customer_service->piva ? $customer_service->piva : $customer_service->customer->piva,
                'data' => $date_doc,
                'autocompila_anagrafica' => true,
                'mostra_info_pagamento' => true,
                'metodo_id' => env('FIC_metodo_id'),
                'prezzi_ivati' => false,
                'PA' => true,
                'PA_tipo_cliente' => 'B2B',
                'lista_articoli' => $lista_articoli,
                'lista_pagamenti' => array(
                    array(
                        'data_scadenza' => $date_doc,
                        'importo' => 'auto',
                        'metodo' => $pagamento_saldato == 1 ? env('FIC_metodo_nome') : 'not',
                        'data_saldo' => $date_doc,
                    )
                )
            )
        );

        $fattura_inviamail = 0;

        if ($fattura_nuova['success'] == 1 && $send_email == 1) {

            /**
             * Fatture in Cloud
             * Recupero dati documento appena creato
             */
            $infomail = $this->api(
                'fatture/infomail',
                array('id' => $fattura_nuova['new_id'])
            );

            /**
             * Fatture in Cloud
             * Invio il documento via email al cliente
             */
            $fattura_inviamail = $this->api(
                'fatture/inviamail',
                array(
                    'id' => $fattura_nuova['new_id'],
                    'mail_mittente' => $infomail['mail_mittente'][0]['mail'],
                    'mail_destinatario' => $customer_service->email ? $customer_service->email : $customer_service->customer->email,
                    'oggetto' => $infomail['oggetto_default'],
                    'messaggio' => $infomail['messaggio_default']
                )
            );
        }

        if ($fattura_inviamail['success'] == true || $send_email == 0) {

            /**
             * Rinnova servizio
             */
            $customer = new Customer();
            $customer->renew_service($customer_service_id);

            /**
             * Aggiorna GSheets
             */
            $gSheets = new GoogleSheetsAPI();
            $gSheets->update();

            return redirect()->route('home');
        }
    }

    /**
     * Recupera le fatture attive e passive da FattureInCloud
     */
    public function getDocToday()
    {
        // Impostazione data
        $y = env('GOOGLE_SHEETS_YEAR');
        $m = date('m');
        $d = date('d');

        $timestamp_start = mktime(0, 0, 0, $m, $d - 15, $y);
        $timestamp_end = mktime(0, 0, 0, $m, $d, $y);

        // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
        /*
        $docs_search = FicDoc::orderBy('data', 'desc')
                             ->first();

        if (!isset($docs_search)) {

            $y = 2016;
            $m = 12;

        } else {

            $y = $docs_search->anno;
            $m = date('m', strtotime($docs_search->data)) + 1;
        }

        $timestamp_start = mktime(0, 0, 0, $m, 1, $y);
        $timestamp_end = mktime(0, 0, 0, $m, date('t', $timestamp_start), $y);
        */
        // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

        $array_data_search = array(
            'anno' => date('Y', $timestamp_start),
            'data_inizio' => date('d/m/Y', $timestamp_start),
            'data_fine' => date('d/m/Y', $timestamp_end)
        );

        // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

        // Recupero fatture Attive
        $fatture_attive = $this->get(
            'fatture',
            'lista',
            $array_data_search
        );

        foreach ($fatture_attive as $fattura_attiva) {

            $fattura = FicDoc::where('fic_id', $fattura_attiva['id'])
                              ->first();

            if (!$fattura) {
                $fattura = new FicDoc();
            }

            $fattura->fic_id = $fattura_attiva['id'];
            $fattura->tipo_doc = $fattura_attiva['tipo'];
            $fattura->tipo = 'attiva';
            $fattura->numero = $fattura_attiva['numero'];
            $fattura->nome = $fattura_attiva['nome'];
            $fattura->anno = substr(str_replace('/', '', $fattura_attiva['data']), 4, 4);
            $fattura->data = Carbon::parse(str_replace('/', '-', $fattura_attiva['data']) . ' 00:00:00');
            $fattura->importo_netto = $fattura_attiva['importo_netto'];
            $fattura->importo_iva = $fattura_attiva['importo_totale'] - $fattura_attiva['importo_netto'];
            $fattura->importo_totale = $fattura_attiva['importo_totale'];

            $fattura->save();
        }

        // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

        // Recupero Note di Credito Attive
        $ndc_attive = $this->get(
            'ndc',
            'lista',
            $array_data_search
        );

        foreach ($ndc_attive as $ndc_attiva) {

            $ndc = FicDoc::where('fic_id', $ndc_attiva['id'])
                         ->first();

            if (!$ndc) {
                $ndc = new FicDoc();
            }

            $ndc->fic_id = $ndc_attiva['id'];
            $ndc->tipo_doc = $ndc_attiva['tipo'];
            $ndc->tipo = 'attiva';
            $ndc->numero = $ndc_attiva['numero'];
            $ndc->nome = $ndc_attiva['nome'];
            $ndc->anno = substr(str_replace('/', '', $fattura_attiva['data']), 4, 4);
            $ndc->data = Carbon::parse(str_replace('/', '-', $ndc_attiva['data']) . ' 00:00:00');
            $ndc->importo_netto = $ndc_attiva['importo_netto'];
            $ndc->importo_iva = $ndc_attiva['importo_totale'] - $ndc_attiva['importo_netto'];
            $ndc->importo_totale = $ndc_attiva['importo_totale'];

            $ndc->save();
        }

        // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

        // Recupero fatture Passive
        $fatture_passive = $this->get(
            'acquisti',
            'lista',
            $array_data_search
        );

        foreach ($fatture_passive as $fattura_passiva) {

            $fattura_passiva_details = $this->get(
                'acquisti',
                'dettagli',
                array(
                    'id' => $fattura_passiva['id']
                )
            );

            $fattura = FicDoc::where('fic_id', $fattura_passiva_details['id'])
                              ->first();

            if (!$fattura) {
                $fattura = new FicDoc();
            }

            $fattura->fic_id = $fattura_passiva_details['id'];
            $fattura->tipo_doc = $fattura_passiva_details['tipo'];
            $fattura->tipo = 'passiva';
            $fattura->numero = $fattura_passiva_details['numero_fattura'];
            $fattura->nome = $fattura_passiva_details['nome'];
            $fattura->anno = $fattura_passiva_details['anno_competenza'];
            $fattura->data = Carbon::parse(str_replace('/', '-', $fattura_passiva_details['data']) . ' 00:00:00');
            $fattura->categoria = $fattura_passiva_details['categoria'];
            $fattura->importo_netto = $fattura_passiva_details['importo_netto'];
            $fattura->importo_iva = $fattura_passiva_details['importo_iva'];
            $fattura->importo_totale = $fattura_passiva_details['importo_totale'];

            $fattura->save();
        }
    }

    /**
     * Recupero i dati e li inserisco in un Array.
     *
     * @param $resource
     * @param $action
     * @param array $filter
     *
     * @return mixed
     */
    public function get($resource, $action, $filter = array())
    {
        $fic_result = $this->api(
            $resource . '/' . $action,
            $filter
        );

        if ($resource == 'preventivi' ||
            $resource == 'fatture' ||
            $resource == 'acquisti' ||
            $resource == 'ndc') {
            $resource = 'documenti';
        }

        if ($action == 'dettagli') {
            $resource = 'documento';
        }

        return $fic_result[$action . '_' . $resource];
    }

    /**
     * Mi collegato a Fatture in Cloud tramite API
     * e recupero le risorse richieste.
     *
     * @param $resource
     * @param $filter
     *
     * @return mixed
     */
    public function api($resource, $filter = array())
    {
        $url = 'https://api.fattureincloud.it/v1/' . $resource;

        $array_auth = array(
            'api_uid' => env('FIC_API_UID'),
            'api_key' => env('FIC_API_KEY')
        );

        $request = array_merge($filter, $array_auth);

        $options = array(
            "http" => array(
                "header"  => "Content-type: text/json\r\n",
                "method"  => "POST",
                "content" => json_encode($request)
            ),
        );

        $context  = stream_context_create($options);

        $result = json_decode(file_get_contents($url, false, $context), true);

        return $result;
    }
}
