<?php

namespace App\Http\Controllers;

use App\Models\CustomerServiceDetail;
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
        $services_exp = $services_exp->withSum('details AS total_notax', 'price_sell');

        $services_exp = $services_exp->addSelect(DB::raw(
            '(
                SELECT
                  sum(`sm_customers_services_details`.`price_sell`) * 1.22
                FROM
                  `sm_customers_services_details`
                WHERE
                  `sm_customers_services`.`id` = `sm_customers_services_details`.`customer_service_id`
            ) AS `total_tax`'
        ));

        $services_exp->addSelect(DB::raw('false AS isExpanded'));

        $services_exp = $services_exp->orderBy('expiration');
        $services_exp = $services_exp->get();

        return Inertia::render('Dashboard/Dashboard', [

            'services_exp' => $services_exp,
            'today' => date('Y-m-d H:i:s')

        ]);
    }
}
