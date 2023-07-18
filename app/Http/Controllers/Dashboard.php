<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class Dashboard extends Controller
{
    public function index()
    {
        $services_exp = \App\Models\CustomerService::query();

        $services_exp = $services_exp->with('customer');
        $services_exp = $services_exp->with('details');
        $services_exp = $services_exp->with('details.service');
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
        $services_exp = $services_exp->with('detailsService');
//        $services_exp = $services_exp->withSum('detailsService AS total_buy_notax', 'price_buy');
        $services_exp->addSelect(DB::raw('false AS isExpanded'));

        $services_exp = $services_exp->orderBy('expiration');
        $services_exp = $services_exp->get();

        // -------------------------------------------------

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

        foreach ($services_exp as $service) {

            $m = date('n', strtotime($service->expiration));

            if (!isset($months_incoming[$m]))
                $months_incoming[$m] = array(
                    'incoming' => 0,
//                    'outcoming' => 0,
                );

            $months_incoming[$m]['incoming'] += $service->total_sell_notax;
//            $months_incoming[$m]['outcoming'] += $service->total_buy_notax;

        }

        ksort($months_incoming);

        // -------------------------------------------------

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
        $services = $services->get();

//        dd($services);

        $services_total_buy = 0;
        foreach ($services as $service) {
            $services_total_buy += $service->total_service_buy;
        }

        $services_details = \App\Models\CustomerServiceDetail::query();
        $services_total_sell = $services_details->sum('price_sell');

        // Conto i clienti che hanno abbonamenti attivi, cioè producono entrate
        $customers_count = Customer::query();
        $customers_count = $customers_count->with('servicesDetails');
        $customers_count = $customers_count->withSum('servicesDetails AS customer_incoming', 'price_sell');
        $customers_count = $customers_count->having('customer_incoming', '>', 0);
        $customers_count = $customers_count->count();

        // -------------------------------------------------

        return Inertia::render('Dashboard/Dashboard', [

            'services_exp' => $services_exp,
            'today' => date('Y-m-d H:i:s'),
            'months_array' => $months_array,
            'months_incoming' => $months_incoming,
            'customers_count' => $customers_count,
            'customers_avg' => $services_total_sell / $customers_count,
            'services' => $services,
            'services_total_sell' => $services_total_sell,
            'services_total_buy' => $services_total_buy,
            'services_total_profit' => $services_total_sell - $services_total_buy,

        ]);
    }
}
