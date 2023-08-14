<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerService;
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
            'is_monthly_cost',
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

                    (
                        (
                        SELECT
                          sum(`' . env('DB_PREFIX') . 'customers_services_details`.`price_sell`)
                        FROM
                          `' . env('DB_PREFIX') . 'customers_services_details`
                        WHERE
                          `' . env('DB_PREFIX') . 'services`.`id` = `' . env('DB_PREFIX') . 'customers_services_details`.`service_id`
                        ) - price_buy
                    ),

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
        $customers = $customers->with(['customerServiceDetail.service' => function ($q) use ($id) {
            $q->where('id', $id);
        }]);
        $customers = $customers->withSum([
            'customerServiceDetail AS customer_total_sell_notax' => function ($q) use ($id) {
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

        session()->forget('saveRedirectCustomer');

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

    public function service_exp_invoice($args, $id)
    {
        $service_exp = CustomerService::query();
        $service_exp = $service_exp->with('customer');
        $service_exp = $service_exp->with('details');
        $service_exp = $service_exp->with('details.service');
        $service_exp = $service_exp->where('id', $id);
        $service_exp = $service_exp->first();

        // -----------------------------

        $fic = new FattureInCloudAPI();

        $fic_clients = $fic->api('get.clients', array(
            'vat_number' => $service_exp->piva ? $service_exp->piva : $service_exp->customer->piva
        ));

        if (!$fic_clients->getData()) {
            $fic_clients = $fic->api('get.clients', array(
                'tax_code' => $service_exp->piva ? $service_exp->piva : $service_exp->customer->piva
            ));
        }

        if (!$fic_clients->getData()) {
            dd('Cliente non trovato in Fatture in Cloud');
        }

        // -----------------------------

        $fic_products = $fic->api('get.products');
        $items_list = array();
        $invoice_items_rows = array();
        $items_net_price_total = 0;
        $items_gross_price_total = 0;

        foreach ($service_exp->details as $details) {

            $index = $details->service_id . $details->price_sell;

            if (!isset($invoice_items_rows[$index])) {

                foreach ($fic_products->getData() as $product) {

                    if ($details->service->fic_cod == $product->getCode()) {

                        $invoice_items_rows[$index] = array(
                            'id' => $product->getId(),
                            'code' => $product->getCode(),
                            'name' => $details->service->name_customer_view,
                            'category' => $product->getCategory(),
                            /*'description' => $details->reference,
                            'qty' => 1,*/
                            'net_price' => $details->price_sell,
                            'gross_price' => $details->price_sell * 1.22,
                        );

                        break;

                    }
                }
            }

            $invoice_items_rows[$index]['reference'][] = $details->reference;

        }

        foreach ($invoice_items_rows as $k => $item_row) {

            $item_row_unique = array_unique($item_row['reference']);
            sort($item_row_unique);
            $description = implode("\n", $item_row_unique);

            $item_row['qty'] = count($item_row['reference']);
            $item_row['description'] = $description;

            $items_net_price_total += $item_row['net_price'] * $item_row['qty'];
            $items_gross_price_total += $item_row['gross_price'] * $item_row['qty'];

            $items_list[$k] = $item_row;

            unset($items_list[$k]['reference']);

        }

        sort($items_list);

        // -----------------------------

        $invoice_args = array(
            'entity' => array(
                'id' => $fic_clients->getData()[0]->getId(),
                'name' => $fic_clients->getData()[0]->getName(),
                'vat_number' => $fic_clients->getData()[0]->getVatNumber(),
                'tax_code' => $fic_clients->getData()[0]->getTaxCode(),
                'address_street' => $fic_clients->getData()[0]->getAddressStreet(),
                'address_postal_code' => $fic_clients->getData()[0]->getAddressPostalCode(),
                'address_city' => $fic_clients->getData()[0]->getAddressCity(),
                'address_province' => $fic_clients->getData()[0]->getAddressProvince(),
                'address_extra' => $fic_clients->getData()[0]->getAddressExtra(),
                'country' => $fic_clients->getData()[0]->getCountry(),
                'certified_email' => $fic_clients->getData()[0]->getCertifiedEmail(),
                'ei_code' => $fic_clients->getData()[0]->getEiCode(),
            ),
            'date' => $args['date'],
            'items_list' => $items_list,
            'payments_list' => array(
                array(
                    'due_date' => $args['date'],
                    'amount' => $items_gross_price_total,
                    'status' => $args['payment_received'] == 1 ? 'paid' : 'not_paid',
                    'paid_date' => $args['payment_received'] == 1 ? $args['date'] : null,
                    'payment_terms' => array(
                        'days' => 0,
                        'type' => 'standard'
                    ),
                    'payment_account' => array(
                        'id' => env('FIC_pay_account_id'),
                        'name' => env('FIC_pay_account_name'),
                    )
                )
            ),
            'payment_method' => array(
                'id' => env('FIC_pay_method_id'),
                'name' => env('FIC_pay_method_name'),
            ),
            'show_payment_method' => true,
            'e_invoice' => true,
            'ei_data' => array(
                'payment_method' => env('FIC_EI_method'),
                'bank_iban' => env('FIC_EI_bank_iban'),
                'bank_beneficiary' => env('FIC_EI_bank_beneficiary'),
            )
        );

        $invoice = $fic->api('create.invoice', $invoice_args);

        if ($args['email_send'] == 1) {

            $emailData = $fic->api('get.invoice.email', array('document_id' => $invoice->getData()->getId()));

            $emailSend_args = array(
                'document_id' => $invoice->getData()->getId(),
                'data' => array(
                    'sender_email' => $emailData->getData()->getDefaultSenderEmail()->getEmail(),
                    'recipient_email' => $emailData->getData()->getRecipientEmail(),
                    'subject' => $emailData->getData()->getSubject(),
                    'body' => $emailData->getData()->getBody(),
                    'include' => array(
                        'document' => $emailData->getData()->getDocumentExists(),
                        'delivery_note' => $emailData->getData()->getDeliveryNoteExists(),
                        'attachment' => $emailData->getData()->getAttachmentExists(),
                        'accompanying_invoice' => $emailData->getData()->getAccompanyingInvoiceExists(),
                    ),
                    'attach_pdf' => false,
                    'send_copy' => false,
                )
            );
            $fic->api('send.invoice.email', $emailSend_args);

        }

        // Rinnovo servizio
        $customer = new \App\Http\Controllers\Customer();
        $customer->service_exp_renew($id);
    }

    public function autorenew()
    {
        $customers_services = CustomerService::query();
        $customers_services = $customers_services->where(
            'expiration', '<=', date('Y-m-d H:i:s')
        );
        $customers_services = $customers_services->where('autorenew', '=', 1);
        $customers_services = $customers_services->orderBy('expiration');
        $customers_services = $customers_services->get();

        foreach ($customers_services as $customer_service) {

            $this->service_exp_invoice(array(
                'date' => date('Y-m-d'),
                'payment_received' => 'not_paid',
                'email_send' => 1,
            ), $customer_service->id);

        }
    }
}
