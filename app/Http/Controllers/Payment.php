<?php

namespace App\Http\Controllers;

use App\Model\CustomersServices;
use App\Model\CustomersServicesDetails;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class Payment extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($sid)
    {
        $payment = \App\Model\Payment::firstWhere('sid', $sid);

        if ($payment) {

            $customer_service = CustomersServices::with('customer')
                                                 ->with('details')
                                                 ->find($payment->customer_service_id);

        }

        /**
         * Se il link o il servizio non vengono trovati, mostrare una pagina notfound.
         */
        if (!$payment || !$customer_service) {
            return view('payment.nofound');
        }

        if ($payment->type != '') {

            /**
             * Se il pagamento è già stato eseguito, redirect su pagina
             * di conferma.
             */
            return redirect()->route('payment.confirm', $sid);

        } else {

            $customers_services_details = CustomersServicesDetails::with('service')
                                                                  ->where('customer_service_id', $payment->customer_service_id)
                                                                  ->orderBy('price_sell', 'DESC')
                                                                  ->orderBy('reference', 'ASC')
                                                                  ->get();

            foreach ($customers_services_details as $customer_service_detail) {

                $index = $customer_service_detail->service_id . $customer_service_detail->price_sell;

                if (!isset($array_services_rows[$index])) {

                    $array_services_rows[$index] = array(
                        'name' => $customer_service_detail->service->name_customer_view,
                        'price_sell' => $customer_service_detail->service->price_sell,
                        'price_customer_sell' => $customer_service_detail->price_sell,
                        'is_share' => $customer_service_detail->service->is_share,
                        'reference' => array()
                    );

                }

                $array_services_rows[$index]['reference'][] = $customer_service_detail->reference;
            }

            $fattureincloud = new FattureInCloudAPI();
            $cliente = $fattureincloud->api(
                'clienti/lista',
                array(
                    'piva' => $customer_service->piva ? $customer_service->piva : $customer_service->customer->piva
                )
            );

            if (count($cliente['lista_clienti']) < 1) {

                $cliente = $fattureincloud->api(
                    'clienti/lista',
                    array(
                        'cf' => $customer_service->piva ? $customer_service->piva : $customer_service->customer->piva
                    )
                );

            }

            $privacy_msg = Storage::disk('public')->get('privacy_template/privacy.html');

            return view('payment.checkout', [
                'payment' => $payment,
                'customer_service' => $customer_service,
                'customers_services_details' => $customers_services_details,
                'array_services_rows' => $array_services_rows,
                'cliente' => isset($cliente['lista_clienti'][0]) ? $cliente['lista_clienti'][0] : '',
                'privacy_msg' => $privacy_msg,
            ]);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function confirm($sid)
    {
        /*$payment = \App\Model\Payment::firstWhere('sid', $sid);

        $services = json_decode($payment->services);

        foreach ($services->details as $detail) {

            $index = $detail->service_id . $detail->price_sell;

            if (!isset($array_services_rows[$index])) {
                $array_services_rows[$index] = array(
                    'name' => $detail->service->name_customer_view,
                    'price_sell' => $detail->price_sell,
                    'reference' => array(),
                );
            }

            $array_services_rows[$index]['reference'][] = $detail->reference;

        }

        uasort($array_services_rows, function ($a, $b) {
            return $b['price_sell'] <=> $a['price_sell'];
        });*/

        $customerServiceInfo = $this->customerServiceInfo($sid);

        $email = new Email();
        $str_replace_array = $email->get_data_template_replace($sid, '');
        $payment_info = Storage::disk('public')->get('payment/' . $customerServiceInfo['payment']->type . '.html');

        foreach ($str_replace_array as $k => $v) {

            $payment_info = str_replace($k, $v, $payment_info);

        }

        return view('payment.confirm', [
            'payment' => $customerServiceInfo['payment'],
            'service' => $customerServiceInfo['service_json'],
            'array_services_rows' => $customerServiceInfo['array_services_rows'],
            'payment_info' => $payment_info
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $sid)
    {
        $type = $request->input('payment');

        $payment = \App\Model\Payment::firstWhere('sid', $sid);

        $customer_service = CustomersServices::with('customer')
                                             ->with('details')
                                             ->with('details.service')
                                             ->find($payment->customer_service_id);

        $amount = 0;
        foreach ($customer_service->details as $detail) {
            $amount += $detail->price_sell;
        }

        $payment->type = $type;
        $payment->payment_date = Carbon::now();
        $payment->amount = $amount;
        $payment->services = \GuzzleHttp\json_encode($customer_service);
        $payment->save();

        $email = new Email();
        $email->sendConfirmService($sid);

        return redirect()->route('payment.confirm', $sid);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    /**
     * Creazione SID per link al pagamento.
     *
     * @param $customer_service_id
     */
    public function sid_create($customer_service_id)
    {
        $service = CustomersServices::find($customer_service_id);

        $payment = \App\Model\Payment::where('customer_service_id', $customer_service_id)
                                     ->where('customer_service_expiration', $service->expiration)
                                     ->first();

        if (!$payment) {

            $payment = new \App\Model\Payment();
            $payment->sid = md5(uniqid(mt_rand(), true));
            $payment->customer_service_id = $customer_service_id;
            $payment->customer_service_expiration = $service->expiration;

            $payment->save();

        }

        return $payment->sid;
    }

    public function customerServiceInfo($sid)
    {
        $payment = \App\Model\Payment::firstWhere('sid', $sid);

        $services = json_decode($payment->services);

        if ($services) {

            foreach ($services->details as $detail) {

                $index = $detail->service_id . $detail->price_sell;

                if (!isset($array_services_rows[$index])) {
                    $array_services_rows[$index] = array(
                        'name' => $detail->service->name_customer_view,
                        'price_sell' => $detail->price_sell,
                        'reference' => array(),
                    );
                }

                $array_services_rows[$index]['reference'][] = $detail->reference;

            }

            uasort($array_services_rows, function ($a, $b) {
                return $b['price_sell'] <=> $a['price_sell'];
            });

            return [
                'payment' => $payment,
                'service_json' => json_decode($payment->services),
                'array_services_rows' => $array_services_rows
            ];

        }
    }

    public function customerServiceDestroySet($sid)
    {
        $payment = \App\Model\Payment::where('type', '')->firstWhere('sid', $sid);

        if ($payment) {

            $customer_service = CustomersServices::with('customer')
                                                 ->with('details')
                                                 ->with('details.service')
                                                 ->find($payment->customer_service_id);

            $amount = 0;
            foreach ($customer_service->details as $detail) {
                $amount += $detail->price_sell;
            }

            $payment->type = 'destroy';
            $payment->payment_date = Carbon::now();
            $payment->amount = $amount;
            $payment->services = \GuzzleHttp\json_encode($customer_service);
            $payment->save();

        }
    }
}
