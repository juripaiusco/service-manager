<?php

namespace App\Http\Controllers;

use App\Models\CustomerService;
use Brevo;
use GuzzleHttp;
use Illuminate\Http\Request;

class Sms extends Controller
{
    public function sendExpiration($id)
    {
        $payment = new Payment();
        $sid = $payment->sid_create($id);

        $data_array = $this->get_data($id);

        $config = Brevo\Client\Configuration::getDefaultConfiguration()
            ->setApiKey(
                'api-key',
                env('SMS_API')
            );

        $apiInstance = new Brevo\Client\Api\TransactionalSMSApi(
            new GuzzleHttp\Client(),
            $config
        );

        if (substr($data_array['cellphone'], 0, 3) != '+39') {
            $data_array['cellphone'] = '+39' . $data_array['cellphone'];
        }

        $sendTransacSms = new \Brevo\Client\Model\SendTransacSms();
        $sendTransacSms['sender'] = env('SMS_SENDER');
        $sendTransacSms['recipient'] = $data_array['cellphone'];
        $sendTransacSms['content'] = 'This is a transactional SMS';
        $sendTransacSms['type'] = 'transactional';
//        $sendTransacSms['webUrl'] = 'https://example.com/notifyUrl';

        try {

            $result = $apiInstance->sendTransacSms($sendTransacSms);
//            print_r($result);

        } catch (\Exception $e) {
            echo 'Exception when calling TransactionalSMSApi->sendTransacSms: ', $e->getMessage(), PHP_EOL;
        }
    }

    private function get_data($customer_service_id)
    {
        $customer_service = CustomerService::with('customer')
            ->with('details')
            ->find($customer_service_id);

        $array = array(
            'cellphone' => $customer_service->customer_cellphone ? $customer_service->customer_cellphone : $customer_service->customer->cellphone,
            'subject_expiration' => '[' . $customer_service->reference . '] - ' . $customer_service->name . ' in scadenza',
            'subject_confirm_bonifico' => '[' . $customer_service->reference . '] - Richiesta bonifico bancario ' . $customer_service->name,
            'subject_destroy' => '[' . $customer_service->reference . '] - disdetta ' . $customer_service->name,
        );

        return $array;
    }
}
