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
        $services = \App\Models\CustomerService::query();

        $services = $services->with('customer');
        $services = $services->with('details');
        $services = $services->with('details.service');
        $services = $services->withSum('details AS total_notax', 'price_sell');

        $services = $services->addSelect(DB::raw(
            '(
                SELECT
                  sum(`sm_customers_services_details`.`price_sell`) * 1.22
                FROM
                  `sm_customers_services_details`
                WHERE
                  `sm_customers_services`.`id` = `sm_customers_services_details`.`customer_service_id`
            ) AS `total_tax`'
        ));

        $services = $services->orderBy('expiration');
        $services = $services->get();

        return Inertia::render('Dashboard/Dashboard', [

            'services' => $services,
            'today' => date('Y-m-d H:i:s')

        ]);
    }
}
