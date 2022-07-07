<?php

namespace App\Http\Controllers;

use FattureInCloud\Api\IssuedDocumentsApi;
use Illuminate\Http\Request;

use FattureInCloud\Api\ArchiveApi;
use FattureInCloud\Api\ClientsApi;
use FattureInCloud\Api\SuppliersApi;
use FattureInCloud\Api\UserApi;
use FattureInCloud\Configuration;
use GuzzleHttp\Client;

class FattureInCloudAPI2 extends Controller
{
    protected   $config,
                $companyId;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Recupero la configurazione per la connessione a FIC.
     *
     * @return Configuration
     */
    private function getConfig()
    {
        if (!isset($this->config)) {

            // Retrieve the access token from the session variable
            $accessToken = env('FIC_TOKEN');

            // Get the API config and construct the service object.
            $this->config = Configuration::getDefaultConfiguration()->setAccessToken($accessToken);

        }

        return $this->config;
    }

    /**
     * Recupero l'ID dell'azienda che utilizza FIC.
     *
     * @return int|null
     * @throws \FattureInCloud\ApiException
     */
    private function getCompanyId()
    {
        if (!isset($this->companyId)) {

            $userApi = new UserApi(
                new Client(),
                $this->getConfig()
            );

            // Retrieve the first company id
            $companies = $userApi->listUserCompanies();
            $this->companyId = $companies->getData()->getCompanies()[0]->getId();

        }

        return $this->companyId;
    }

    public function get($resource, $action, $filter = array())
    {
        $issuedDocumentsApi = new IssuedDocumentsApi(
            new Client(),
            $this->getConfig()
        );

        $fic_result = $issuedDocumentsApi->listIssuedDocuments(
            $this->getCompanyId(),
            'invoice',
            '',
            '',
            '-number',
            '',
            '',
            'date > ' . $filter['data_inizio'] . ' AND date < ' . $filter['data_fine']
        );

        return $fic_result['data'];
    }

    public function quickstart()
    {
        /*$fic = new FattureInCloudAPI();
        $fatture_attive = $fic->get(
            'fatture',
            'lista',
            array(
                'anno' => env('GOOGLE_SHEETS_YEAR'),
                'data_inizio' => '01/01/' . env('GOOGLE_SHEETS_YEAR'),
                'data_fine' => '31/12/' . env('GOOGLE_SHEETS_YEAR')
            )
        );

        echo '<table border="1">';
        foreach ($fatture_attive as $fattura) {

            echo '<tr>';
            echo '<td>' . $fattura['data'] . '</td>';
            echo '<td>' . $fattura['numero'] . '</td>';
            echo '<td style="text-align: right;">' . $fattura['importo_netto'] . '</td>';
            echo '<td>' . $fattura['nome'] . '</td>';
            echo '</tr>';

        }
        echo '</table>';*/

        /*$fatture_attive = $this->get(
            'fatture',
            'lista',
            array(
                'anno' => env('GOOGLE_SHEETS_YEAR'),
                'data_inizio' => env('GOOGLE_SHEETS_YEAR') . '0101',
                'data_fine' => env('GOOGLE_SHEETS_YEAR') . '1231'
            )
        );

        echo '<table border="1">';
        foreach ($fatture_attive as $fattura) {

            $date_decode = json_decode(json_encode($fattura['date']));
            $dateTime = $date_decode->date;
            $date = new \DateTime($dateTime);

            echo '<tr>';
            echo '<td>' . $date->format('d/m/Y') . '</td>';
            echo '<td>' . $fattura['number'] . '</td>';
            echo '<td style="text-align: right;">' . number_format($fattura['amount_net'], 2, '.', '') . '</td>';
            echo '<td>' . $fattura['entity']['name'] . '</td>';
            echo '</tr>';

        }
        echo '</table>';*/

        // ---------------------------------------------------

        /*$customersApi = new ClientsApi(
            new Client(),
            $this->config()
        );

        $customers = $customersApi->listClients(
            $this->companyId(),
            null,
            null,
            null,
            1,
            5
        );

        dd($customers);*/

        /*$userApi = new UserApi(
            new Client(),
            $config
        );
        $suppliersApi = new SuppliersApi(
            new Client(),
            $config
        );
        $customersApi = new ClientsApi(
            new Client(),
            $config
        );*/

        /*try {
            // Retrieve the first company id
            $companies = $userApi->listUserCompanies();
            $firstCompanyId = $companies->getData()->getCompanies()[0]->getId();

            // Retrieve the list of first 10 Suppliers for the selected company
            $suppliers = $suppliersApi->listSuppliers($firstCompanyId, null, null, null, 1, 10);

            foreach ($suppliers->getData() as $supplier) {
                $name = $supplier->getName();
                echo("$name </br>\n");
            }

            echo '<hr>';

            $customers = $customersApi->listClients($firstCompanyId, null, null, null, 1, 10);

            foreach ($customers->getData() as $customer) {
                echo $customer->getName() . "<br>\n";
            }

        } catch (\Exception $e) {
            echo 'Exception when calling the API: ', $e->getMessage(), PHP_EOL;
        }*/
    }
}
