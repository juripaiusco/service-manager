<?php

namespace App\Http\Controllers;

use App\Models\CustomerService;
use Brevo;
use GuzzleHttp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class Sms extends Controller
{
    public function sendExpiration($id)
    {
        $payment = new Payment();
        $sid = $payment->sid_create($id);

        $data_array = $this->get_data($id, $sid);

        if (env('SMS_API') != '' && $data_array['cellphone'] != '') {

            // Fix cellphone number
            if (substr($data_array['cellphone'], 0, 3) != '+39') {
                $data_array['cellphone'] = '+39' . $data_array['cellphone'];
            }

            $config = Brevo\Client\Configuration::getDefaultConfiguration()
                ->setApiKey(
                    'api-key',
                    env('SMS_API')
                );

            $apiInstance = new Brevo\Client\Api\TransactionalSMSApi(
                new GuzzleHttp\Client(),
                $config
            );

            $sendTransacSms = new \Brevo\Client\Model\SendTransacSms();
            $sendTransacSms['sender'] = env('SMS_SENDER');
            $sendTransacSms['recipient'] = $data_array['cellphone'];
            $sendTransacSms['content'] = $data_array['sms_txt'];
            $sendTransacSms['type'] = 'transactional';
//        $sendTransacSms['webUrl'] = 'https://example.com/notifyUrl';

            try {

                $result = $apiInstance->sendTransacSms($sendTransacSms);
//            print_r($result);

            } catch (\Exception $e) {
                echo 'Exception when calling TransactionalSMSApi->sendTransacSms: ', $e->getMessage(), PHP_EOL;
            }

        }
    }

    private function get_data($customer_service_id, $sid)
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

        // SMS Text
        $customer_service_array = json_decode($customer_service, true);

        $sms_txt_expiration = Storage::disk('public')->get('sms_template/expiration.txt');

        foreach ($customer_service_array as $k => $v) {

            if (substr($k, 0, strlen('customer_')) == 'customer_') {

                if ($v) {
                    $sms_txt_expiration = str_replace('[' . $k . ']', $v, $sms_txt_expiration);

                } else {

                    $sms_txt_expiration = str_replace(
                        '[' . $k . ']',
                        $customer_service_array['customer'][str_replace('customer_', '', $k)],
                        $sms_txt_expiration
                    );
                }

            }
        }

        $sms_txt_expiration = str_replace('[service_name]', $customer_service->name, $sms_txt_expiration);
        $sms_txt_expiration = str_replace('[service_reference]', $customer_service->reference, $sms_txt_expiration);
        $sms_txt_expiration = str_replace('[service_expiration]', date('d/m/Y', strtotime($customer_service->expiration)), $sms_txt_expiration);
        $sms_txt_expiration = str_replace('[link]', route('email.show', ['expiration', $sid]), $sms_txt_expiration);

        $array['sms_txt'] = $sms_txt_expiration;

        return $array;
    }
}
