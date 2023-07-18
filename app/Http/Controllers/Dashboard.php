<?php

namespace App\Http\Controllers;

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

        return Inertia::render('Dashboard/Dashboard', [

            'services_exp' => $services_exp,
            'today' => date('Y-m-d H:i:s'),
            'months_array' => $months_array,
            'months_incoming' => $months_incoming

        ]);
    }
}
