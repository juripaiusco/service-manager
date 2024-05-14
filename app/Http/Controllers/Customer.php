<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class Customer extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        /*if (Storage::disk('public')->exists('customer_up/customers.csv')) {

            $customers_file = Storage::disk('public')->get('customer_up/customers.csv');
            $customers_file_rows = explode("\r\n", $customers_file);

            $c = 0;

            foreach ($customers_file_rows as $i => $file_row) {

                if (!$c) {

                    $array_fields = explode(';', $file_row);

                } else {

                    $array_value = explode(';', $file_row);

                    foreach ($array_fields as $k => $field) {

                        $array_customers[$i - 1][$field] = $array_value[$k];

                    }
                }

                $c++;

            }


            foreach ($array_customers as $customer_d) {

                $customer = new \App\Models\Customer();

                $customer->company = $customer_d['name'] . ' ' . $customer_d['surname'];
                $customer->piva = '0';
                $customer->cf = '0';
                $customer->address = $customer_d['address'];
                $customer->city = '';
                $customer->cap = '';
                $customer->name = $customer_d['name'];
                $customer->cellphone = $customer_d['cellphone1'];
                $customer->telephone = '';
                $customer->email = str_replace(' ::: ', ';', $customer_d['email1']);
                $customer->note = '';

                $customer->save();

            }

        }*/

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

        $data = $data->leftJoin(
            'customers_services_details',
            'customers_services_details.customer_id',
            '=',
            'customers.id'
        );
        $data = $data->leftJoin(
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

        session()->forget('saveRedirectCustomer');

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
        // Creo un oggetto di dati vuoto
        $columns = Schema::getColumnListing('customers');

        $data = array();
        foreach ($columns as $field) {
            $data[$field] = '';
        }

        unset($data['id']);
        unset($data['deleted_at']);
        unset($data['created_at']);
        unset($data['updated_at']);

        $data['saveRedirect'] = Redirect::back()->getTargetUrl();

        $data = json_decode(json_encode($data), true);

        return Inertia::render('Customers/Form', [
            'data' => $data,
            'filters' => request()->all(['s', 'orderby', 'ordertype'])
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'company'   => ['required'],
            'email'     => ['required'],
            'name'      => ['required'],
            'piva'      => ['required'],
        ]);

        $saveRedirect = $request['saveRedirect'];
        unset($request['saveRedirect']);
        unset($request['customer_service']);

        $customer = new \App\Models\Customer();
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
    public function edit(Request $request, string $id)
    {
        $customerServiceExpiration = new CustomerServiceExpiration();
        $customerServiceExpiration->serviceExpActionInit($request);

        $data = \App\Models\Customer::with('customerService')
            ->with('customerService.details')
            ->with('customerService.details.service')
            ->find($id);

        if (!$request->session()->get('saveRedirectCustomer')) {
            $request->session()->put('saveRedirectCustomer', Redirect::back()->getTargetUrl());
        }

        $data->saveRedirect = $request->session()->get('saveRedirectCustomer');

//        dd($data);

        return Inertia::render('Customers/Form', [
            'data' => $data,
            'filters' => request()->all(['s', 'orderby', 'ordertype'])
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'company'   => ['required'],
            'email'     => ['required'],
            'name'      => ['required'],
            'piva'      => ['required'],
        ]);

        $saveRedirect = $request['saveRedirect'];
        unset($request['customer_service']);
        unset($request['saveRedirect']);
        unset($request['created_at']);
        unset($request['updated_at']);

        $customer = \App\Models\Customer::find($id);
        $customer->fill($request->all());
        $customer->save();

        return Redirect::to($saveRedirect);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        \App\Models\Customer::destroy($id);
        \App\Models\CustomerService::where('customer_id', $id)->delete();
        \App\Models\CustomerServiceDetail::where('customer_id', $id)->delete();

        return \redirect()->back();
    }

    public function service_exp_renew(string $id)
    {
        $service_exp = \App\Models\CustomerService::find($id);

        $expirationTime = strtotime($service_exp->expiration . ' +1 year');

        if ($service_exp->expiration_monthly == 1) {
            $expirationTime = strtotime($service_exp->expiration . ' +1 month');
        }

        $expirationTimestamp = date('YmdHis', $expirationTime);

        $service_exp->expiration = $expirationTimestamp;
        $service_exp->save();
    }
}
