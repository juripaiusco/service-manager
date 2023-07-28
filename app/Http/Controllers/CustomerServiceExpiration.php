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
        $services = \App\Models\Service::query();
        $services = $services->paginate(env('VIEWS_PAGINATE'))->withQueryString();

        $data = \App\Models\CustomerService::with('details')
            ->with('details.service')
            ->find($id);

        $data->saveRedirect = Redirect::back()->getTargetUrl();

        $this->serviceExpAction($request, json_decode($data->details));

        return Inertia::render('Customers/ServiceExp/Form', [
            'data' => $data,
            'services' => $services,
            'filters' => request()->all(['s', 'orderby', 'ordertype']),
            'create_url' => $request->input('currentUrl') ? $request->input('currentUrl') : null
        ]);
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

    public function serviceExpActionInit(Request $request)
    {
        $request->session()->forget('serviceExp');
    }

    public function serviceExpAction(Request $request, $data = array())
    {
        if (count($data) > 0 && $request->session()->get('serviceExp') == null) {

            $request->session()->put('serviceExp', $data);

        }

        $this->serviceExpActionDel($request);
        $this->serviceExpActionAdd($request);
    }

    public function serviceExpActionAdd(Request $request)
    {
        if ($request->input('serviceExpAddTo') == true) {

            $service = \App\Models\Service::find($request->input('serviceExp_id'));
            $service->index = $request->session()->get('serviceExp') ? array_key_last($request->session()->get('serviceExp')) + 1 : 0;

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
                $serviceExp_array[$k]->index = $k;
            }

            $request->session()->put('serviceExp', $serviceExp_array);

            Inertia::share('serviceExp', $request->session()->get('serviceExp'));

        }
    }
}
