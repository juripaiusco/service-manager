<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class Service extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $request_search_array = [
            'fic_cod',
            'name',
            'name_customer_view',
        ];

        $request_validate_array = array_merge($request_search_array, [
            'is_share',
            'total_service_sell',
            'price_buy',
            'total_service_profit',
        ]);

        // Query data
        $data = \App\Models\Service::query();

        // Request validate
        request()->validate([
            'orderby' => ['in:' . implode(',', $request_validate_array)],
            'ordertype' => ['in:asc,desc']
        ]);

        // Filtro RICERCA
        if (request('s')) {
            $data->where(function ($q) use ($request_search_array) {

                foreach ($request_search_array as $field) {
                    $q->orWhere($field, 'like', '%' . request('s') . '%');
                }

            });
        }

        // Filtro ORDINAMENTO
        if (request('orderby') && request('ordertype')) {
            $data->orderby(request('orderby'), strtoupper(request('ordertype')));
        } else {
            $data->orderby('total_service_profit', 'DESC');
        }

        $data = $data->select();
        $data = $data->with('customersServicesDetails');
        $data->withCount('customersServicesDetails AS customers_count');
        $data->addSelect(DB::raw(
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
        $data->addSelect(DB::raw(
            '(
                    SELECT
                      sum(`sm_customers_services_details`.`price_sell`)
                    FROM
                      `sm_customers_services_details`
                    WHERE
                      `sm_services`.`id` = `sm_customers_services_details`.`service_id`
                    ) AS total_service_sell'
        ));
        $data = $data->addSelect(DB::raw(
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
//        $data = $data->orderBy('total_service_profit', 'DESC');

        $data = $data->paginate(env('VIEWS_PAGINATE'))->withQueryString();

        return Inertia::render('Services/List', [
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
