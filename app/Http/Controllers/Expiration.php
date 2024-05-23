<?php

namespace App\Http\Controllers;

use App\Models\CustomerService;
use Illuminate\Http\Request;

class Expiration extends Controller
{
    public function sendExpiration($id)
    {
        $email = new Email();
        $email->sendExpiration($id);

        $sms = new Sms();
        $sms->sendExpiration($id);
    }

    public function sendExpirationList()
    {
        $customers_services = CustomerService::query();
        $customers_services = $customers_services->leftJoin('payments', function($join) {
            $join->on('payments.customer_service_id', '=', 'customers_services.id');
            $join->on('payments.customer_service_expiration', '=', 'customers_services.expiration');
        });
        $customers_services = $customers_services->where(function ($query){
            $query->where('payments.type', '')
                ->orWhereNull('payments.type');
        });
        $customers_services = $customers_services->where(
            'expiration', '>=', date('Y-m-d H:i:s')
        );
        $customers_services = $customers_services->where(
            'expiration', '<=', date('Y-m-d', strtotime('+2 month'))
        );
        /*$customers_services = $customers_services->where(function ($q) {
            $q->where('no_email_alert', '=', 0);
            $q->orWhereNull('no_email_alert');
        });*/
        $customers_services = $customers_services->select([
            'customers_services.id as id',
            'customers_services.no_email_alert as no_email_alert',
            'customers_services.no_sms_alert as no_sms_alert',
        ]);
        $customers_services = $customers_services->orderBy('expiration');
        $customers_services = $customers_services->get();

        foreach ($customers_services as $customer_service) {

            if (!$customer_service->no_email_alert) {
                $email = new Email();
                $email->sendExpiration($customer_service->id);
            }

            if (!$customer_service->no_sms_alert) {
                $sms = new Sms();
                $sms->sendExpiration($customer_service->id);
            }

        }
    }
}
