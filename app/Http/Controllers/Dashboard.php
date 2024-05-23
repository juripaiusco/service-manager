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

    public function getServicesExp($search = true)
    {
        $request_validate_array = [
            'customer_name',
            'customer_company',
            'customer_piva',
            'customer_email',
            'name',
            'reference',
        ];

        $services_exp = \App\Models\CustomerService::query();

        // Filtro RICERCA
        if (request('s') && $search) {
            $services_exp->where(function ($q) use ($request_validate_array) {

                foreach ($request_validate_array as $field) {
                    $q->orWhere('customers_services.' . $field, 'like', '%' . request('s') . '%');
                }

                $q->orWhere('customers.customer_name', 'like', '%' . request('s') . '%');
                $q->orWhere('customers.customer_company', 'like', '%' . request('s') . '%');
                $q->orWhere('customers.customer_piva', 'like', '%' . request('s') . '%');
                $q->orWhere('customers.customer_email', 'like', '%' . request('s') . '%');

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
            'customers_services.customer_piva AS piva',
            'customers_services.customer_company AS company',
            'customers_services.customer_email AS email',
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
//        $services_exp = $services_exp->get();

        return $services_exp;
    }

    public function getData($search = true)
    {
        // Data TAB Services Expiration
        // ====================================================
        $services_exp = $this->getServicesExp($search)->get();

        // Data TAB Services Incoming
        // ====================================================

        // Definisco i mesi in stringhe
        $months_array = array();
        for ($m = 1; $m <= 12; $m++) {
            $months_array[$m] = date('F', mktime(0, 0, 0, $m));
        }

        // Inizializzo l'array degli importi: entrate / uscite / profitti
        $months_incoming = array();
        foreach ($months_array as $m => $month) {

            $months_incoming[$m] = array(
                'incoming' => 0,
                'outcoming' => 0,
                'profit' => 0,
            );

        }

        /*// Divisione della spesa condivisa per i mesi dell'anno
        foreach ($services_exp as $service_exp) {

            foreach ($service_exp->details as $detail) {

                if ($detail->service->is_monthly_cost == 1) {

                    $amount = 1;
                    $price_buy = $detail->service->price_buy / 12;

                    if ($detail->service->is_share != 1) {

                        $services_q = $this->getServices();
                        $services_q = $services_q->where('id', $detail->service->id);
                        $services_q = $services_q->first();

                        $amount = $services_q->customers_count;
                        $price_buy = $price_buy / $amount;
                    }

                    if (!isset($ctrl_service_array[$detail->service->id]) || $detail->service->is_share == 1) {
                        $ctrl_service_array[$detail->service->id] = $detail->service->id;

                        $services_q = $this->getServices();
                        $services_q = $services_q->where('id', $detail->service->id);
                        $services_q = $services_q->first();

                        if ($detail->service->is_share == 1) {
                            $amount = $services_q->customers_count;
                            $price_buy = $price_buy / $services_q->customers_count;
                        }

                        foreach ($months_incoming as $m => $month_incoming) {

                            $months_incoming[$m]['outcoming'] += $price_buy;
                            $months_incoming[$m]['profit'] = $months_incoming[$m]['incoming'] - $months_incoming[$m]['outcoming'];
                            $months_incoming[$m]['details'][$detail->service->id] = array(
                                'name' => $detail->service->name,
                                'references' => 'Rata mensile',
                                'amount' => $amount,
                                'price_buy' => $price_buy,
                                'price_buy_total' => $price_buy * $amount,
                            );
                        }
                    }
                }
            }
        }

        // Suddivizione entrate per mese e tipo servizio
        foreach ($services_exp as $service_exp) {

            $m = date('n', strtotime($service_exp->expiration));
            $m_detail = 0;

            $months_incoming[$m]['incoming'] += $service_exp->total_sell_notax;

            foreach ($service_exp->details as $detail) {

                if ($detail->service->is_monthly_cost != 1 && $detail->service->price_buy > 0) {

                    $m_detail = 0;
                    if (isset($detail->expiration)) {
                        $m_detail = date('n', strtotime($detail->expiration));
                    }

                    if ($detail->service->is_share != 1) {

                        $price_buy = $detail->service->price_buy;
                        $months_incoming[$m_detail == 0 ? $m : $m_detail]['outcoming'] += $price_buy;
                    }

                    // Se il servizio è condiviso su più clienti, ma non ha costo mensile
                    // (il costo mensile comanda per importanza di calcolo)
                    if ($detail->service->is_share == 1) {

                        $services_q = $this->getServices();
                        $services_q = $services_q->where('id', $detail->service->id);
                        $services_q = $services_q->first();

                        $price_buy = $detail->service->price_buy / $services_q->customers_count;
                        $months_incoming[$m_detail == 0 ? $m : $m_detail]['outcoming'] += $price_buy;
                    }

                    if (!isset($months_incoming[$m_detail == 0 ? $m : $m_detail]['details'][$detail->service->id])) {
                        $references = $detail->reference;
                    } else {
                        $references  = $months_incoming[$m_detail == 0 ? $m : $m_detail]['details'][$detail->service->id]['references'] . ' / ';
                        $references .= $detail->reference;
                    }

                    // Conto il numero di servizi acquistati
                    $servicesExpCost_count[$m_detail == 0 ? $m : $m_detail][$detail->service->id][] = $detail->service->id;
                    $amount = count($servicesExpCost_count[$m_detail == 0 ? $m : $m_detail][$detail->service->id]);

                    $months_incoming[$m_detail == 0 ? $m : $m_detail]['details'][$detail->service->id] = array(
                        'name' => $detail->service->name,
                        'references' => $references,
                        'amount' => $amount,
                        'price_buy' => $price_buy,
                        'price_buy_total' => $price_buy * $amount,
                    );
                }
            }

            $months_incoming[$m]['profit'] = $months_incoming[$m]['incoming'] - $months_incoming[$m]['outcoming'];

            if ($m_detail != 0) {
                $months_incoming[$m_detail]['profit'] = $months_incoming[$m_detail]['incoming'] - $months_incoming[$m_detail]['outcoming'];
            }
        }*/

        // -------------------------------------------------------------------------------------------------------------
        // Popolo l'array degli importi in base alla tipologia e data di scadenza del servizio
        foreach ($services_exp as $service_exp) {

            // Mese di scadenza del gruppo di servizi
            $m = date('n', strtotime($service_exp->expiration));

            // Mese di scadenza del singolo servizio
            $ms = 0;

            $months_incoming[$m]['incoming'] += $service_exp->total_sell_notax;

            foreach ($service_exp->details as $detail) {

                if ($detail->service->price_buy > 0) {

                    $ms = 0;
                    $pref = '';

                    // Prezzo di acquisto del singolo servizio
                    $price_buy = $detail->service->price_buy;

                    // Se esiste la scadenza specifica per singolo servizio del cliente
                    if (isset($detail->expiration) && $detail->service->is_monthly_cost != 1) {
                        $ms = date('n', strtotime($detail->expiration));
                    }

                    // Definisco la quantità di servizi presenti nel mese
                    if (!isset($services_month_amount[$ms == 0 ? $m : $ms][$detail->service->id]))
                        $services_month_amount[$ms == 0 ? $m : $ms][$detail->service->id] = 0;

                    $services_month_amount[$ms == 0 ? $m : $ms][$detail->service->id] += 1;
                    $amount = $services_month_amount[$ms == 0 ? $m : $ms][$detail->service->id];

                    // Definisco le referenze
                    if (!isset($months_incoming[$ms == 0 ? $m : $ms]['details'][$detail->service->id])) {
                        $references = $detail->reference;
                    } else {
                        $references  = $months_incoming[$ms == 0 ? $m : $ms]['details'][$detail->service->id]['references'] . ' / ';
                        $references .= $detail->reference;
                    }

                    // Se il servizio è condiviso definisco il prezzo
                    if ($detail->service->is_share == 1) {

                        $pref = 'Servizio condiviso';
                        $getService = $this->getServices();
                        $getService = $getService->where('id', $detail->service->id);
                        $getService = $getService->first();

                        $price_buy = $price_buy / $getService->customers_count;
                    }

                    // Se il servizio è a pagamento rateale annuale
                    if ($detail->service->is_monthly_cost == 1) {

                        $pref = 'Rata mensile' . ($pref != '' ? ' su ' . strtolower($pref) : '');

                        // Definisco la quantità di servizi a pagamento mensili
                        if (!isset($amount_monthly[$detail->service->id]))
                            $amount_monthly[$detail->service->id] = 0;

                        $amount_monthly[$detail->service->id]++;
                        $amount = $amount_monthly[$detail->service->id];

                        /*if ($detail->service->is_share == 1) {

                            $getService = $this->getServices();
                            $getService = $getService->where('id', $detail->service->id);
                            $getService = $getService->first();

                            $amount = $getService->customers_count;
                        }*/

                        $price_buy = $price_buy / 12;

                        foreach ($months_incoming as $m_monthly => $month_incoming) {

                            $months_incoming[$m_monthly]['outcoming'] += $price_buy;
                            $months_incoming[$m_monthly]['profit'] = $months_incoming[$m_monthly]['incoming'] - $months_incoming[$m_monthly]['outcoming'];
                            $months_incoming[$m_monthly]['details'][$detail->service->id] = array(
                                'name' => $detail->service->name,
                                'pref' => $pref,
                                'references' => $references,
                                'amount' => $amount,
                                'price_buy' => $price_buy,
                                'price_buy_total' => $price_buy * $amount,
                            );
                        }
                    }

                    // Scrivo le referenze collegate al servizio in scadenza
                    if ($detail->service->is_monthly_cost != 1) {

                        $months_incoming[$ms == 0 ? $m : $ms]['outcoming'] += $price_buy;
                        $months_incoming[$ms == 0 ? $m : $ms]['details'][$detail->service->id] = array(
                            'name' => $detail->service->name,
                            'pref' => $pref,
                            'references' => $references,
                            'amount' => $amount,
                            'price_buy' => $price_buy,
                            'price_buy_total' => $price_buy * $amount,
                        );

                        $months_incoming[$m]['profit'] = $months_incoming[$m]['incoming'] - $months_incoming[$m]['outcoming'];

                        if ($ms != 0)
                            $months_incoming[$ms]['profit'] = $months_incoming[$ms]['incoming'] - $months_incoming[$ms]['outcoming'];
                    }
                }
            }
        }

        ksort($months_incoming);

        if (isset($month_incoming['details'])) {

            foreach ($months_incoming as $k => $month_incoming) {
                usort($month_incoming['details'], function ($a, $b) {
                    return ($a['price_buy_total'] >= $b['price_buy_total']) ? -1 : 1;
                });

                $months_incoming[$k]['details'] = $month_incoming['details'];
            }
        }

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
            'services_exp' => $this->getServicesExp($search)->paginate(env('VIEWS_PAGINATE'))->withQueryString(),
            'services_exp_count' => $services_exp->count(),
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
        return Inertia::render('Dashboard/Dashboard', [

            'data' => $this->getData(),
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
        $expiration = new Expiration();
        $expiration->sendExpiration($id);

        return to_route('dashboard');
    }

    public function service_exp_invoice(Request $request, $id)
    {
        $service = new Service();
        $service->service_exp_invoice($request, $id);

        return to_route('dashboard');
    }
}
