<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Schema;
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
            'price_sell',
            'price_buy',
            'profit',
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
            $data->orderby('profit', 'DESC');
        }

        $data = $data->select();
        $data = $data->with('customersServicesDetails');
//        $data = $data->withSum('customersServicesDetails AS price_sell', 'price_sell');
        $data->withCount('customersServicesDetails AS customers_count');
        $data->addSelect(DB::raw(
            'IF(
                is_share = 1,

                (
                    SELECT
                      sum(`' . env('DB_PREFIX') . 'customers_services_details`.`price_sell`)
                    FROM
                      `' . env('DB_PREFIX') . 'customers_services_details`
                    WHERE
                      `' . env('DB_PREFIX') . 'services`.`id` = `' . env('DB_PREFIX') . 'customers_services_details`.`service_id`
                ),

                `price_sell`

                ) AS `price_sell`'
        ));
        $data->addSelect(DB::raw(
            'IF(
                is_share = 1,

                ((
                    SELECT
                      sum(`' . env('DB_PREFIX') . 'customers_services_details`.`price_sell`)
                    FROM
                      `' . env('DB_PREFIX') . 'customers_services_details`
                    WHERE
                      `' . env('DB_PREFIX') . 'services`.`id` = `' . env('DB_PREFIX') . 'customers_services_details`.`service_id`
                    )
                    -
                    (IF(
                    is_share = 1,

                    (price_buy),

                    (price_buy * (
                    SELECT
                      count(*)
                    FROM
                      `' . env('DB_PREFIX') . 'customers_services_details`
                    WHERE
                      `' . env('DB_PREFIX') . 'services`.`id` = `' . env('DB_PREFIX') . 'customers_services_details`.`service_id`
                    ))

                ))),

                (price_sell - price_buy)

            ) AS profit'
        ));
        $data->addSelect(DB::raw(
            'IF(
                    is_share = 1,

                    (price_buy),

                    (price_buy * (
                    SELECT
                      count(*)
                    FROM
                      `' . env('DB_PREFIX') . 'customers_services_details`
                    WHERE
                      `' . env('DB_PREFIX') . 'services`.`id` = `' . env('DB_PREFIX') . 'customers_services_details`.`service_id`
                    ))

                    ) AS total_service_buy'
        ));
        $data->addSelect(DB::raw(
            '(
                    SELECT
                      sum(`' . env('DB_PREFIX') . 'customers_services_details`.`price_sell`)
                    FROM
                      `' . env('DB_PREFIX') . 'customers_services_details`
                    WHERE
                      `' . env('DB_PREFIX') . 'services`.`id` = `' . env('DB_PREFIX') . 'customers_services_details`.`service_id`
                    ) AS total_service_sell'
        ));
        $data = $data->addSelect(DB::raw(
            '((
                    SELECT
                      sum(`' . env('DB_PREFIX') . 'customers_services_details`.`price_sell`)
                    FROM
                      `' . env('DB_PREFIX') . 'customers_services_details`
                    WHERE
                      `' . env('DB_PREFIX') . 'services`.`id` = `' . env('DB_PREFIX') . 'customers_services_details`.`service_id`
                    )
                    -
                    (IF(
                    is_share = 1,

                    (price_buy),

                    (price_buy * (
                    SELECT
                      count(*)
                    FROM
                      `' . env('DB_PREFIX') . 'customers_services_details`
                    WHERE
                      `' . env('DB_PREFIX') . 'services`.`id` = `' . env('DB_PREFIX') . 'customers_services_details`.`service_id`
                    ))

                    ))) AS total_service_profit'
        ));

        $dashboard = new Dashboard();
        $data_services_exp = $dashboard->getData(false);
        $data = $data->addSelect(DB::raw($data_services_exp['services_total_sell'] . ' AS services_total_sell'));
        $data = $data->addSelect(DB::raw($data_services_exp['services_total_buy'] . ' AS services_total_buy'));
        $data = $data->addSelect(DB::raw($data_services_exp['services_total_profit'] . ' AS services_total_profit'));
//        $data = $data->addSelect(DB::raw('"' . url()->full() . '" AS saveRedirect'));

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
        // Creo un oggetto di dati vuoto
        $columns = Schema::getColumnListing('services');

        $customers_array = array();
        foreach ($columns as $customers_field) {
            $customers_array[$customers_field] = '';
        }

        unset($customers_array['id']);
        unset($customers_array['deleted_at']);
        unset($customers_array['created_at']);
        unset($customers_array['updated_at']);

        $customers_array['saveRedirect'] = Redirect::back()->getTargetUrl();

        $data = json_decode(json_encode($customers_array), true);

        return Inertia::render('Services/Form', [
            'data' => $data
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'                  => ['required'],
            'price_sell'            => ['required'],
            'price_buy'             => ['required'],
            'fic_cod'               => ['required'],
            'name_customer_view'    => ['required'],
        ]);

        $saveRedirect = $request['saveRedirect'];
        unset($request['saveRedirect']);

        $customer = new \App\Models\Service();
        $customer->fill($request->all());
        $customer->save();

        return Redirect::to($saveRedirect);
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
        $data = \App\Models\Service::find($id);

        $data->saveRedirect = Redirect::back()->getTargetUrl();

        $customers = Customer::query();
        $customers = $customers->join(
            'customers_services_details',
            'customers_services_details.customer_id',
            '=',
            'customers.id'
        );
        $customers = $customers->join(
            'services',
            'services.id',
            '=',
            'customers_services_details.service_id'
        );
        $customers = $customers->where('customers_services_details.service_id', $id);
        /*$customers = $customers->with(['servicesDetails' => function ($q) use ($id) {
            $q->where('service_id', $id);
        }]);*/
        $customers = $customers->with(['customerService.details' => function ($q) use ($id) {
            $q->where('service_id', $id);
        }]);
        $customers = $customers->with(['customerService.detailsService' => function ($q) use ($id) {
            $q->where('service_id', $id);
        }]);
        $customers = $customers->with(['servicesDetails.service' => function ($q) use ($id) {
            $q->where('id', $id);
        }]);
        $customers = $customers->withSum([
            'servicesDetails AS customer_total_sell_notax' => function ($q) use ($id) {
                $q->where('service_id', $id);
            }
        ], 'price_sell');
        $customers = $customers->addSelect(DB::raw(
            'IF (' . env('DB_PREFIX') . 'services.is_share = 1,

                    (price_buy / (
                    SELECT
                      count(*)
                    FROM
                      `' . env('DB_PREFIX') . 'customers_services_details`
                    WHERE
                      `' . env('DB_PREFIX') . 'services`.`id` = `' . env('DB_PREFIX') . 'customers_services_details`.`service_id`
                    ) * (
                    SELECT
                      count(*)
                    FROM
                      `' . env('DB_PREFIX') . 'customers_services_details`
                    WHERE
                      `' . env('DB_PREFIX') . 'services`.`id` = `' . env('DB_PREFIX') . 'customers_services_details`.`service_id`
                        AND `' . env('DB_PREFIX') . 'customers`.`id` = `' . env('DB_PREFIX') . 'customers_services_details`.`customer_id`
                    ))
                    ,

                    SUM(' . env('DB_PREFIX') . 'services.price_buy)

                ) AS customer_total_buy_notax'
        ));
        $customers = $customers->addSelect(DB::raw(
            'IF (' . env('DB_PREFIX') . 'services.is_share = 1,

                    (price_buy / (
                    SELECT
                      count(*)
                    FROM
                      `' . env('DB_PREFIX') . 'customers_services_details`
                    WHERE
                      `' . env('DB_PREFIX') . 'services`.`id` = `' . env('DB_PREFIX') . 'customers_services_details`.`service_id`
                    )),

                    ' . env('DB_PREFIX') . 'services.price_buy

                ) AS customer_single_buy_share_notax'
        ));
        /*$customers = $customers->select([
            'customers.id',
            'customers.piva',
            'customers.company',
            'customers.name',
            'customers.email',
        ]);*/
        $customers = $customers->addSelect(DB::raw('false AS isExpanded'));
        $customers = $customers->groupBy('customers.id');
        $customers = $customers->orderBy('customers.company');
//        dd($customers->get()[0]);
        $customers = $customers->get();

        return Inertia::render('Services/Form', [
            'data' => $data,
            'customers' => $customers,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'name'                  => ['required'],
            'price_sell'            => ['required'],
            'price_buy'             => ['required'],
            'fic_cod'               => ['required'],
            'name_customer_view'    => ['required'],
        ]);

        $saveRedirect = $request['saveRedirect'];
        unset($request['saveRedirect']);
        unset($request['created_at']);
        unset($request['updated_at']);

        $customer = \App\Models\Service::find($id);
        $customer->fill($request->all());
        $customer->save();

        return Redirect::to($saveRedirect);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        \App\Models\Service::destroy($id);

        return \redirect()->back();
    }
}
