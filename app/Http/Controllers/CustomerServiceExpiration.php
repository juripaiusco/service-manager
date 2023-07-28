<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class CustomerServiceExpiration extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
    public function edit(Request $request, string $id)
    {
        $data = \App\Models\CustomerService::with('details')
            ->with('details.service')
            ->find($id);

        $data->saveRedirect = Redirect::back()->getTargetUrl();

        $customer = \App\Models\Customer::find($data->customer_id);

        $this->serviceExpAction($request, json_decode($data->details));

        return Inertia::render('Customers/ServiceExp/Form', [
            'data' => $data,
            'services' => $this->servicesGet(),
            'customer' => $customer,
            'filters' => request()->all(['s', 'orderby', 'ordertype']),
            'create_url' => $request->input('currentUrl') ? $request->input('currentUrl') : null
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $saveRedirect = $request['saveRedirect'];
        unset($request['saveRedirect']);
        unset($request['created_at']);
        unset($request['updated_at']);
        unset($request['details']);

        dd($request->session()->get('serviceExp'));
        dd($request->all());

        return to_route('customer.edit', $request->input('customer_id'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function serviceExpActionInit(Request $request)
    {
        $request->session()->forget('serviceExp');
    }

    public function serviceExpAction(Request $request, $data = array())
    {
        if (count($data) > 0 && $request->session()->get('serviceExp') == null) {

            foreach ($data as $k => $d) {
                $data[$k]->serviceExp_index = $k;
            }

            $request->session()->put('serviceExp', $data);

        }

        $this->serviceExpActionDel($request);
        $this->serviceExpActionAdd($request);
    }

    public function serviceExpActionAdd(Request $request)
    {
        if ($request->input('serviceExpAddTo') == true) {

            $service = \App\Models\Service::find($request->input('serviceExp_id'));
            $service->serviceExp_index = $request->session()->get('serviceExp') ? array_key_last($request->session()->get('serviceExp')) + 1 : 0;

            $request->session()->push('serviceExp', $service);

            Inertia::share('serviceExp', $request->session()->get('serviceExp'));

            return true;
        }

        Inertia::share('serviceExp', $request->session()->get('serviceExp'));

        return false;
    }

    public function serviceExpActionDel(Request $request)
    {
        if ($request->input('serviceExpRemove') == true) {

            $serviceExp_array = $request->session()->get('serviceExp');

            unset($serviceExp_array[$request->input('serviceExp_index')]);

            $serviceExp_array = array_values($serviceExp_array);

            foreach ($serviceExp_array as $k => $service) {
                $serviceExp_array[$k]->serviceExp_index = $k;
            }

            $request->session()->put('serviceExp', $serviceExp_array);

            Inertia::share('serviceExp', $request->session()->get('serviceExp'));

        }
    }

    public function servicesGet()
    {
        $request_validate_array = [
            'fic_cod',
            'name',
            'name_customer_view',
        ];

        $services = \App\Models\Service::query();

        // Request validate
        request()->validate([
            'orderby' => ['in:' . implode(',', $request_validate_array)],
            'ordertype' => ['in:asc,desc']
        ]);

        // Filtro RICERCA
        if (request('s')) {
            $services->where(function ($q) use ($request_validate_array) {

                foreach ($request_validate_array as $field) {
                    $q->orWhere($field, 'like', '%' . request('s') . '%');
                }

            });
        }

        // Filtro ORDINAMENTO
        if (request('orderby') && request('ordertype')) {
            $services->orderby(request('orderby'), strtoupper(request('ordertype')));
        }

        $services = $services->select();

        return $services->paginate(5)->withQueryString();
    }
}
