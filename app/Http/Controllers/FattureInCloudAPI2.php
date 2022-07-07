<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use FattureInCloud\Api\SuppliersApi;
use FattureInCloud\Api\UserApi;
use FattureInCloud\Configuration;
use GuzzleHttp\Client;

class FattureInCloudAPI2 extends Controller
{
    public function quickstart()
    {
        // Retrieve the access token from the session variable
        $accessToken = env('FIC_TOKEN');

        // Get the API config and construct the service object.
        $config = Configuration::getDefaultConfiguration()->setAccessToken($accessToken);

        $userApi = new UserApi(
            new Client(),
            $config
        );
        $suppliersApi = new SuppliersApi(
            new Client(),
            $config
        );

        try {
            // Retrieve the first company id
            $companies = $userApi->listUserCompanies();
            $firstCompanyId = $companies->getData()->getCompanies()[0]->getId();

            // Retrieve the list of first 10 Suppliers for the selected company
            $suppliers = $suppliersApi->listSuppliers($firstCompanyId, null, null, null, 1, 10);
            foreach ($suppliers->getData() as $supplier) {
                $name = $supplier->getName();
                echo("$name </br>\n");
            }

        } catch (\Exception $e) {
            echo 'Exception when calling the API: ', $e->getMessage(), PHP_EOL;
        }
    }
}
