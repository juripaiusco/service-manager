<?php

namespace App\Http\Controllers;

use App\Models\CustomerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class Email extends Controller
{
    /**
     * Mostra la mail tramite browser.
     *
     * @param $view
     * @param $sid
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function show($view, $sid)
    {
        $payment = \App\Models\Payment::firstWhere('sid', $sid);

        /**
         * Se il link o il servizio non vengono trovati, mostrare una pagina notfound.
         */
        if (!$payment) {
            return view('payment.nofound');
        }

        $html = Storage::disk('public')->get('mail_template/' . $view . '_' . env('TEMPLATE') . '.html');
        $content = $this->get_template($sid, $view, $html);

        return view('mail.service-msg', [
            'content' => $content
        ]);
    }

    /**
     * Invio email di avviso per la scadenza del servizio.
     * Vengono inviate email CC nel caso siano presenti.
     *
     * Il redirect non è presente, perché questo modulo può essere
     * richiamato per inviare le mail alla lista dei clienti con servizio
     * in scadenza.
     *
     * @param $id
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function sendExpiration($id)
    {
        $payment = new Payment();
        $sid = $payment->sid_create($id);

        $html = Storage::disk('public')->get('mail_template/expiration_' . env('TEMPLATE') . '.html');
        $content = $this->get_template($sid, 'expiration', $html);
        $data_array = $this->get_data($id);

        $email_array = explode(';', $data_array['to']);

        $mail = Mail::to($email_array[0]);

        if (count($email_array) > 0) {

            foreach ($email_array as $k => $email) {
                if ($k > 0) {
                    $mail->cc($email);
                }
            }

        }

        if (env('MAIL_BCC_ADDRESS')) {
            $mail->bcc(env('MAIL_BCC_ADDRESS'));
        }

        $mail->send(new \App\Mail\Service(
            $data_array['subject_expiration'],
            $content
        ));
    }

    /**
     * Send email to customer with expiration services
     * @return void
     */
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
        $customers_services = $customers_services->where(function ($q) {
            $q->where('no_email_alert', '=', 0);
            $q->orWhereNull('no_email_alert');
        });
        $customers_services = $customers_services->select([
            'customers_services.id as id'
        ]);
        $customers_services = $customers_services->orderBy('expiration');
        $customers_services = $customers_services->get();

        foreach ($customers_services as $customer_service) {

            /*echo $customer_service->expiration . ' - ';
            echo $customer_service->reference . '<br>';*/
            $this->sendExpiration($customer_service->id);

        }
    }

    /**
     * Creazione template da inviare via email e visualizzare online
     *
     * @param $customer_service_id
     * @param $view
     * @param $html
     *
     * @return string|string[]
     */
    public function get_template($sid, $view, $html)
    {
        $str_replace_array = $this->get_data_template_replace($sid, $view);

        $style_custom = '
            <style>
            h2 {
                margin-bottom: 15px;
            }
            .date-exp-container {
                border: 4px dashed #f00;
                padding: 30px 0 15px 0;
                text-align: center;
                border-radius: 8px;
                margin: 30px 0 0 0;
            }
            .date-exp {
                font-size: 3em;
                font-weight: bold;
                white-space: nowrap;
                margin-bottom: 10px;
            }
            .date-exp-msg {
                font-size: .75em;
                white-space: nowrap;
            }
            .tbl-container {
                background-color: #f5f5f5;
                padding: 5px 15px 5px 15px;
                border-radius: 6px;
            }
            .tbl-details {
                color: #aaa;
                margin-top: 5px;
                margin-bottom: 15px;
            }
            .tbl-details th {
                border-bottom: 1px solid #ccc;
                font-weight: normal;
            }
            .title-service-details {
                margin-bottom: 15px;
                text-align: center;
                color: #aaa;
            }
            </style>
        ';
        $style_custom = str_replace('<style>', '', $style_custom);
        $html = str_replace('</style>', $style_custom, $html);

        foreach ($str_replace_array as $k => $v) {

            $html = str_replace($k, $v, $html);

        }

        return $html;
    }

    /**
     * Restituisco i dati necessari a popolare il template
     *
     * @param $customer_service_id
     *
     * @return array
     */
    public function get_data_template_replace($sid, $view)
    {
        $payment = \App\Models\Payment::firstWhere('sid', $sid);

        $customer_service = CustomerService::with('customer')
            ->with('details')
            ->find($payment->customer_service_id);

        if (!$customer_service) {
            $customer_service = json_decode($payment->services);
        }

        $price_sell_tot = 0;

        foreach ($customer_service->details as $detail) {
            $price_sell_tot += $detail->price_sell;
        }
        $price_sell_tot = '&euro; ' . number_format($price_sell_tot * 1.22, 2, ',', '.');

        $str_replace_array = array(
            '[customers-name]' => $customer_service->customer_name ? $customer_service->customer_name : $customer_service->customer->name,
            '[customers_services-name]' => $customer_service->name,
            '[customers_services-reference]' => $customer_service->reference,
            '[customers_services-expiration]' => date('d/m/Y', strtotime($payment->customer_service_expiration)),
            '[customers_services-expiration-banner_]' => '
                <div class="date-exp-container">
                    <div class="date-exp">'. date('d-m-Y', strtotime($payment->customer_service_expiration)) . '</div>
                    <div class="date-exp-msg">(data di scadenza e disattivazione dei servizi)</div>
                </div>
            ',
            '[customers_services-list]' => $this->customersServicesList($sid),
            '[customers_services-total_]' => $price_sell_tot,

            'http://[customers_services-link_]' => '[customers_services-link_]',
            'https://[customers_services-link_]' => '[customers_services-link_]',
            '[customers_services-link_]' => route('payment.checkout', $sid),

            'http://[email-link_]' => '[email-link_]',
            'https://[email-link_]' => '[email-link_]',
            '[email-link_]' => route('email.show', [$view, $sid]),

            '*|MC:SUBJECT|*' => '[' . $customer_service->reference . '] - ' . $customer_service->name . ' in scadenza',
            '*|MC_PREVIEW_TEXT|*' => date('d/m/Y', strtotime($payment->customer_service_expiration)) . ' disattivazione ' . $customer_service->name . ' ' . $customer_service->reference,
        );

        return $str_replace_array;
    }

    /**
     * Prendo i dati per popolare la mail da inviare.
     *
     * @param $customer_service_id
     *
     * @return array
     */
    public function get_data($customer_service_id)
    {
        $customer_service = CustomerService::with('customer')
            ->with('details')
            ->find($customer_service_id);

        $array = array(
            'to' => $customer_service->customer_email ? $customer_service->customer_email : $customer_service->customer->email,
            'subject_expiration' => '[' . $customer_service->reference . '] - ' . $customer_service->name . ' in scadenza',
            'subject_confirm_bonifico' => '[' . $customer_service->reference . '] - Richiesta bonifico bancario ' . $customer_service->name,
            'subject_destroy' => '[' . $customer_service->reference . '] - disdetta ' . $customer_service->name,
        );

        return $array;
    }

    public function customersServicesList($sid)
    {
        $payment = new Payment();
        $customerServiceInfo = $payment->customerServiceInfo($sid);

        if ($customerServiceInfo) {

            $out = '
                <table style="width: 100%;">
                    <thead>
                    <tr>
                        <th>Servizio</th>
                        <th>Riferimento</th>
                    </tr>
                    </thead>

                    <tbody>
                ';

            foreach ($customerServiceInfo['array_services_rows'] as $k => $v)
            {
                $out .= '<tr style="border-bottom: 1px solid #fdd;">';
                $out .= '<td>';
                $out .= $v['name'];

                if(count($v['reference']) > 1) {
                    $out .= '<small> x ' . count($v['reference']) . '</small>';
                }

                $out .= '</td>';
                $out .= '<td>';

                $reference_unique = array_unique($v['reference']);

                $out .= implode('<br>', $reference_unique);

                $out .= '</td>';
                $out .= '</tr>';
            }

            $out .= '
                    </tbody>
                </table>
                ';

            return $out;
        }
    }

    /**
     * Dopo aver confermato il rinnovo, viene inviata questa email di conferma.
     * @param $sid
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function sendConfirmService($sid)
    {
        $payment = \App\Models\Payment::firstWhere('sid', $sid);

        $html = Storage::disk('public')->get('mail_template/confirm-' . $payment->type . '.html');
        $content = $this->get_template($sid, 'confirm-' . $payment->type, $html);
        $data_array = $this->get_data($payment->customer_service_id);

        $email_array = explode(';', $data_array['to']);

        $mail = Mail::to($email_array[0]);

        if (env('MAIL_BCC_ADDRESS')) {
            $mail->bcc(env('MAIL_BCC_ADDRESS'));
        }

        $mail->send(new \App\Mail\Service(
            $data_array['subject_confirm_' . $payment->type],
            $content
        ));
    }
}
