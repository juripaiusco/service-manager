<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class Customer extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $request_search_array = [
            'company',
            'name',
            'email',
            'piva',
        ];

        $request_validate_array = array_merge($request_search_array, [
            'incoming',
            'outcoming',
            'profit',
            'total_profit',
        ]);

        // Query data
        $data = \App\Models\Customer::query();
        $data = $data->with('customerServiceDetail');
        $data = $data->with('customerServiceDetail.service');

        $data = $data->join(
            'customers_services_details',
            'customers_services_details.customer_id',
            '=',
            'customers.id'
        );
        $data = $data->join(
            'services',
            'services.id',
            '=',
            'customers_services_details.service_id'
        );

        // Request validate
        request()->validate([
            'orderby' => ['in:' . implode(',', $request_validate_array)],
            'ordertype' => ['in:asc,desc']
        ]);

        // Filtro RICERCA
        if (request('s')) {
            $data->where(function ($q) use ($request_search_array) {

                foreach ($request_search_array as $field) {
                    $q->orWhere('customers.' . $field, 'like', '%' . request('s') . '%');
                }

            });
        }

        // Filtro ORDINAMENTO
        if (request('orderby') && request('ordertype')) {
            $data->orderby(request('orderby'), strtoupper(request('ordertype')));
        } else {
            $data->orderby('profit', 'DESC');
        }

        $data = $data->select([
            'customers.id',
            'customers.company',
            'customers.name',
            'customers.email',
        ]);
        $data = $data->withSum('customerServiceDetail AS incoming', 'price_sell');

        $data = $data->addSelect(
            DB::raw('SUM(IF(
                ' . env('DB_PREFIX') . 'services.is_share = 1,
                ' . env('DB_PREFIX') . 'services.price_buy / (
                    SELECT COUNT(id) AS count FROM ' . env('DB_PREFIX') . 'customers_services_details
                        WHERE service_id = ' . env('DB_PREFIX') . 'services.id
                        GROUP BY ' . env('DB_PREFIX') . 'services.id
                ),
                ' . env('DB_PREFIX') . 'services.price_buy
            )) AS outcoming')
        );

        $data = $data->addSelect(
            DB::raw('
            (
                SUM(' . env('DB_PREFIX') . 'customers_services_details.price_sell)
                -
                SUM(IF(
                    ' . env('DB_PREFIX') . 'services.is_share = 1,
                    ' . env('DB_PREFIX') . 'services.price_buy / (
                        SELECT COUNT(id) AS count FROM ' . env('DB_PREFIX') . 'customers_services_details
                            WHERE service_id = ' . env('DB_PREFIX') . 'services.id
                            GROUP BY ' . env('DB_PREFIX') . 'services.id
                    ),
                    ' . env('DB_PREFIX') . 'services.price_buy
                ))
            ) AS profit')
        );

        $data = $data->groupBy('customers.id');

        $dashboard = new Dashboard();
        $data_services_exp = $dashboard->getData(false);
        $data = $data->addSelect(DB::raw($data_services_exp['services_total_sell'] . ' AS services_total_sell'));
        $data = $data->addSelect(DB::raw($data_services_exp['services_total_buy'] . ' AS services_total_buy'));
        $data = $data->addSelect(DB::raw($data_services_exp['services_total_profit'] . ' AS services_total_profit'));

        $data = $data->paginate(env('VIEWS_PAGINATE'))->withQueryString();

        return Inertia::render('Customers/List', [
            'data' => $data,
            'filters' => request()->all(['s', 'orderby', 'ordertype'])
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
