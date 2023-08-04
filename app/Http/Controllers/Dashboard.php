<?php

namespace App\Http\Controllers;

use App\Models\CustomerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class Dashboard extends Controller
{
    /**
     * Servizi
     *
     * @return \App\Models\Service|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    public function getServices()
    {
        $services = \App\Models\Service::query();
        $services = $services->with('customersServicesDetails');
        $services->withCount('customersServicesDetails AS customers_count');
        $services->addSelect(DB::raw(
            'IF(
                    is_share = 1,

                    (price_buy),

                    (price_buy * (
                    SELECT
                      count(*)
                    FROM
                      `sm_customers_services_details`
                    WHERE
                      `sm_services`.`id` = `sm_customers_services_details`.`service_id`
                    ))

                    ) AS total_service_buy'
        ));
        $services->addSelect(DB::raw(
            '(
                    SELECT
                      sum(`sm_customers_services_details`.`price_sell`)
                    FROM
                      `sm_customers_services_details`
                    WHERE
                      `sm_services`.`id` = `sm_customers_services_details`.`service_id`
                    ) AS total_service_sell'
        ));
        $services = $services->addSelect(DB::raw(
            '((
                    SELECT
                      sum(`sm_customers_services_details`.`price_sell`)
                    FROM
                      `sm_customers_services_details`
                    WHERE
                      `sm_services`.`id` = `sm_customers_services_details`.`service_id`
                    )
                    -
                    (IF(
                    is_share = 1,

                    (price_buy),

                    (price_buy * (
                    SELECT
                      count(*)
                    FROM
                      `sm_customers_services_details`
                    WHERE
                      `sm_services`.`id` = `sm_customers_services_details`.`service_id`
                    ))

                    ))) AS total_service_profit'
        ));
        $services = $services->orderBy('total_service_profit', 'DESC');

        return $services;
    }

    public function getData($search = true)
    {
        $request_validate_array = [
            'piva',
            'company',
            'email',
            'customer_name',
            'name',
            'reference',
        ];

        // Data TAB Services Expiration
        // ====================================================

        $services_exp = \App\Models\CustomerService::query();

        // Filtro RICERCA
        if (request('s') && $search) {
            $services_exp->where(function ($q) use ($request_validate_array) {

                foreach ($request_validate_array as $field) {
                    $q->orWhere('customers_services.' . $field, 'like', '%' . request('s') . '%');
                }

                $q->orWhere('customers.name', 'like', '%' . request('s') . '%');
                $q->orWhere('customers.company', 'like', '%' . request('s') . '%');
                $q->orWhere('customers.piva', 'like', '%' . request('s') . '%');
                $q->orWhere('customers.email', 'like', '%' . request('s') . '%');

            });
        }

        $services_exp = $services_exp->join(
            'customers',
            'customers.id',
            '=',
            'customers_services.customer_id'
        );

        $services_exp = $services_exp->leftJoin('payments', function($join) {
            $join->on('payments.customer_service_id', '=', 'customers_services.id');
            $join->on('payments.customer_service_expiration', '=', 'customers_services.expiration');
        });

        $services_exp = $services_exp->with('customer');
        $services_exp = $services_exp->with('details');
        $services_exp = $services_exp->with('details.service');
        $services_exp = $services_exp->with('detailsService');

        $services_exp = $services_exp->select([
            'customers_services.id AS id',
            'customers_services.customer_id AS customer_id',
            'customers_services.piva AS piva',
            'customers_services.company AS company',
            'customers_services.email AS email',
            'customers_services.customer_name AS customer_name',
            'customers_services.name AS name',
            'customers_services.reference AS reference',
            'customers_services.expiration AS expiration',
            'customers_services.expiration_monthly AS expiration_monthly',
            'customers_services.autorenew AS autorenew',
            'customers_services.no_email_alert AS no_email_alert',
            'payments.type AS payment_type',
        ]);
        $services_exp = $services_exp->withSum('details AS total_sell_notax', 'price_sell');
        $services_exp = $services_exp->addSelect(DB::raw(
            '(
                SELECT
                  sum(`sm_customers_services_details`.`price_sell`) * 1.22
                FROM
                  `sm_customers_services_details`
                WHERE
                  `sm_customers_services`.`id` = `sm_customers_services_details`.`customer_service_id`
            ) AS `total_sell_tax`'
        ));
        $services_exp->addSelect(DB::raw('false AS isExpanded'));

        $services_exp = $services_exp->orderBy('expiration');
        $services_exp = $services_exp->get();

        // Data TAB Services Incoming
        // ====================================================

        $months_array = array(
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

        $months_incoming = array();

        foreach ($months_array as $m => $month) {

            $months_incoming[$m + 1] = array(
                'incoming' => 0,
                'outcoming' => 0,
                'profit' => 0,
            );

        }

        // Suddivizione entrate per mese e tipo servizio
        foreach ($services_exp as $service) {

            $m = date('n', strtotime($service->expiration));

            $months_incoming[$m]['incoming'] += $service->total_sell_notax;

            foreach ($service->detailsService as $detailService) {

                if ($detailService->is_share != 1) {

                    $months_incoming[$m]['outcoming'] += $detailService->price_buy;

                } else {

                    $services_q = $this->getServices();
                    $services_q = $services_q->where('id', $detailService->id);
                    $services_q = $services_q->first();

                    $months_incoming[$m]['outcoming'] += $detailService->price_buy / $services_q->customers_count;

                }
            }

            $months_incoming[$m]['profit'] = $months_incoming[$m]['incoming'] - $months_incoming[$m]['outcoming'];

        }

        ksort($months_incoming);

        // Suddivizione profitto per trimestre
        $trim_incoming = array();

        foreach ($months_incoming as $k => $month_incoming) {

            if (!isset($trim_incoming[$k]))
                $trim_incoming[$k] = 0;

            if ($k % 3 == 1) {

                if (isset($months_incoming[$k]['profit']))
                    $trim_incoming[$k] += $months_incoming[$k]['profit'];

                if (isset($months_incoming[$k + 1]['profit']))
                    $trim_incoming[$k] += $months_incoming[$k + 1]['profit'];

                if (isset($months_incoming[$k + 2]['profit']))
                    $trim_incoming[$k] += $months_incoming[$k + 2]['profit'];

            }

        }

        // Data TAB Services Profit
        // ====================================================

        $array_customer_id = array();   // Serve per conteggio cliente con contratto attivo
        $services_buy = array();        // Totale spese per servizio
        $services_total_buy = 0;        // Totale spese
        $services_sell = array();       // Totale entrate per servizio
        $services_total_sell = 0;       // Totale entrate

        foreach ($services_exp as $service_exp) {

            // Count Price BUY and SELL
            foreach ($service_exp->details as $detail) {

                if (!isset($services_buy[$detail->service->id]))
                    $services_buy[$detail->service->id] = 0;

                if (!isset($services_sell[$detail->service->id]))
                    $services_sell[$detail->service->id] = 0;

                // BUY
                if ($detail->service->is_share != 1) {

                    $services_buy[$detail->service->id] += $detail->service->price_buy;
                    $services_total_buy += $detail->service->price_buy;

                } else {

                    $services = $this->getServices();
                    $services->find($detail->service->id);
                    $services = $services->first();

                    $services_buy[$detail->service->id] += $detail->service->price_buy / $services->customers_count;
                    $services_total_buy += $detail->service->price_buy / $services->customers_count;
                }

                // SELL
                $services_sell[$detail->service->id] += $detail->price_sell;
                $services_total_sell += $detail->price_sell;

                // Count Active Customer
                if ($detail->price_sell > 0) {
                    $array_customer_id[$service_exp->customer->id] = 1;
                }

            }
        }

        $customers_count = count($array_customer_id);

        // Media spesa per ogni cliente
        $customers_avg = 0;
        if ($customers_count > 0) {
            $customers_avg = $services_total_sell / $customers_count;
        }

        // Tramite la lista servizio conto: entrate / uscite / profitto / numero di clienti
        $services = $this->getServices()->get();
        $services_list = array();

        foreach ($services as $service) {

            $services_list[$service->id] = array(
                'name' => $service->name,
                'customers_count' => 0,
                'incoming' => 0,
                'outcoming' => 0,
                'profit' => 0,
            );

            foreach ($services_exp as $service_exp) {

                foreach ($service_exp->details as $detail) {

                    if ($service->id == $detail->service->id) {

                        if (!isset($service_count[$service->id]))
                            $service_count[$service->id] = 0;

                        $service_count[$service->id]++;

                        $services_list[$service->id] = array(
                            'name' => $service->name,
                            'customers_count' => $service_count[$service->id],
                            'incoming' => $services_sell[$service->id],
                            'outcoming' => $services_buy[$service->id],
                            'profit' => $services_sell[$service->id] - $services_buy[$service->id],
                        );

                    }

                }

            }

        }

        $services_list = array_values($services_list);

        // -------------------------------------------------

        return array(
            'services_exp' => $services_exp,
            'today' => date('Y-m-d H:i:s'),
            'months_array' => $months_array,
            'months_incoming' => $months_incoming,
            'trim_incoming' => $trim_incoming,
            'customers_count' => $customers_count,
            'customers_avg' => $customers_avg,
            'services' => $services_list,
            'services_total_sell' => $services_total_sell,
            'services_total_buy' => $services_total_buy,
            'services_total_profit' => $services_total_sell - $services_total_buy,
        );
    }

    public function index()
    {
        $data = $this->getData();

        return Inertia::render('Dashboard/Dashboard', [

            'data' => $data,
            'filters' => request()->all(['s', 'orderby', 'ordertype'])

        ]);
    }

    public function service_exp_renew(Request $request, $id)
    {
        $customer = new Customer();
        $customer->service_exp_renew($id);

        return to_route('dashboard');
    }

    public function service_exp_alert(Request $request, $id)
    {
        $email = new Email();
        $email->sendExpiration($id);

        return to_route('dashboard');
    }

    public function service_exp_invoice(Request $request, $id)
    {
        $service_exp = CustomerService::query();
        $service_exp = $service_exp->with('customer');
        $service_exp = $service_exp->with('details');
        $service_exp = $service_exp->with('details.service');
        $service_exp = $service_exp->where('id', $id);
        $service_exp = $service_exp->first();

        // -----------------------------

        $fic = new FattureInCloudAPI();

        $fic_clients = $fic->api('get.clients', array(
            'vat_number' => $service_exp->piva ? $service_exp->piva : $service_exp->customer->piva
        ));

        if (!$fic_clients->getData()) {
            $fic_clients = $fic->api('get.clients', array(
                'tax_code' => $service_exp->piva ? $service_exp->piva : $service_exp->customer->piva
            ));
        }

        if (!$fic_clients->getData()) {
            dd('Cliente non trovato in Fatture in Cloud');
        }

        // -----------------------------

        $fic_products = $fic->api('get.products');
        $items_list = array();
        $invoice_items_rows = array();
        $items_net_price_total = 0;
        $items_gross_price_total = 0;

        foreach ($service_exp->details as $details) {

            $index = $details->service_id . $details->price_sell;

            if (!isset($invoice_items_rows[$index])) {

                foreach ($fic_products->getData() as $product) {

                    if ($details->service->fic_cod == $product->getCode()) {

                        $invoice_items_rows[$index] = array(
                            'id' => $product->getId(),
                            'code' => $product->getCode(),
                            'name' => $details->service->name_customer_view,
                            'category' => $product->getCategory(),
                            /*'description' => $details->reference,
                            'qty' => 1,*/
                            'net_price' => $details->price_sell,
                            'gross_price' => $details->price_sell * 1.22,
                        );

                        break;

                    }
                }
            }

            $invoice_items_rows[$index]['reference'][] = $details->reference;

        }

        foreach ($invoice_items_rows as $k => $item_row) {

            $item_row_unique = array_unique($item_row['reference']);
            sort($item_row_unique);
            $description = implode("\n", $item_row_unique);

            $item_row['qty'] = count($item_row['reference']);
            $item_row['description'] = $description;

            $items_net_price_total += $item_row['net_price'] * $item_row['qty'];
            $items_gross_price_total += $item_row['gross_price'] * $item_row['qty'];

            $items_list[$k] = $item_row;

            unset($items_list[$k]['reference']);

        }

        sort($items_list);

        // -----------------------------

        $invoice_args = array(
            'entity' => array(
                'id' => $fic_clients->getData()[0]->getId(),
                'name' => $fic_clients->getData()[0]->getName(),
                'vat_number' => $fic_clients->getData()[0]->getVatNumber(),
                'tax_code' => $fic_clients->getData()[0]->getTaxCode(),
                'address_street' => $fic_clients->getData()[0]->getAddressStreet(),
                'address_postal_code' => $fic_clients->getData()[0]->getAddressPostalCode(),
                'address_city' => $fic_clients->getData()[0]->getAddressCity(),
                'address_province' => $fic_clients->getData()[0]->getAddressProvince(),
                'address_extra' => $fic_clients->getData()[0]->getAddressExtra(),
                'country' => $fic_clients->getData()[0]->getCountry(),
                'certified_email' => $fic_clients->getData()[0]->getCertifiedEmail(),
                'ei_code' => $fic_clients->getData()[0]->getEiCode(),
            ),
            'date' => $request['date'],
            'items_list' => $items_list,
            'payments_list' => array(
                array(
                    'due_date' => $request['date'],
                    'amount' => $items_gross_price_total,
                    'status' => $request['payment_received'] == 1 ? 'paid' : 'not_paid',
                    'paid_date' => $request['payment_received'] == 1 ? $request['date'] : null,
                    'payment_terms' => array(
                        'days' => 0,
                        'type' => 'standard'
                    ),
                    'payment_account' => array(
                        'id' => env('FIC_pay_account_id'),
                        'name' => env('FIC_pay_account_name'),
                    )
                )
            ),
            'payment_method' => array(
                'id' => env('FIC_pay_method_id'),
                'name' => env('FIC_pay_method_name'),
            ),
            'show_payment_method' => true,
            'e_invoice' => true,
            'ei_data' => array(
                'payment_method' => env('FIC_EI_method'),
                'bank_iban' => env('FIC_EI_bank_iban'),
                'bank_beneficiary' => env('FIC_EI_bank_beneficiary'),
            )
        );

        $invoice = $fic->api('create.invoice', $invoice_args);

        

        dd($invoice);

        return to_route('dashboard');
    }
}
