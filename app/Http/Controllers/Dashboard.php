<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class Dashboard extends Controller
{
    public function services()
    {
        $services = \App\Models\Service::query();
        $services = $services->with('customersServicesDetails');
        /*$services = $services->whereHas('customersServicesDetails', function ($q) {
            $q->where('reference', 'LIKE', '%blue%');
        });*/
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

//        dd($services->get()[0]);

        return $services;
    }

    public function index()
    {
        $request_validate_array = [
            'piva',
            'company',
            'email',
            'customer_name',
            'name',
            'reference',
        ];

        // -------------------------------------------------

        $services_exp = \App\Models\CustomerService::query();

        // Filtro RICERCA
        if (request('s')) {
            $services_exp->where(function ($q) use ($request_validate_array) {

                foreach ($request_validate_array as $field) {
                    $q->orWhere($field, 'like', '%' . request('s') . '%');
                }

            });
        }

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

        $services = $this->services();
        $services = $services->get();

//        dd($services[0]);

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

        foreach ($services_exp as $service) {

            $m = date('n', strtotime($service->expiration));

            $months_incoming[$m]['incoming'] += $service->total_sell_notax;

            foreach ($service->detailsService as $detailService) {

                if ($detailService->is_share != 1) {

                    $months_incoming[$m]['outcoming'] += $detailService->price_buy;

                } else {

                    $services_q = $this->services();
                    $services_q = $services_q->where('id', $detailService->id);
                    $services_q = $services_q->first();

                    $months_incoming[$m]['outcoming'] += $detailService->price_buy / $services_q->customers_count;

                }
            }

            $months_incoming[$m]['profit'] = $months_incoming[$m]['incoming'] - $months_incoming[$m]['outcoming'];

        }

        ksort($months_incoming);

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

        // -------------------------------------------------

        return Inertia::render('Dashboard/Dashboard', [

            'data' => array(
                'services_exp' => $services_exp,
                'today' => date('Y-m-d H:i:s'),
                'months_array' => $months_array,
                'months_incoming' => $months_incoming,
                'trim_incoming' => $trim_incoming,
                'customers_count' => $customers_count,
                'customers_avg' => $services_total_sell / $customers_count,
                'services' => $services,
                'services_total_sell' => $services_total_sell,
                'services_total_buy' => $services_total_buy,
                'services_total_profit' => $services_total_sell - $services_total_buy,
            ),
            'filters' => request()->all(['s', 'orderby', 'ordertype'])

        ]);
    }
}
