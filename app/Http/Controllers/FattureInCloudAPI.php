<?php

namespace App\Http\Controllers;

use FattureInCloud\Api;
use FattureInCloud\Configuration;
use GuzzleHttp;

class FattureInCloudAPI extends Controller
{
    public function api(string $resource, array $args = array())
    {
        try {

            switch ($resource)
            {
                case 'clients':
                    return $this->clients($args);
                    break;
                case 'products':
                    return $this->products($args);
                    break;
                case 'invoice':
                    return $this->invoice($args);
                    break;
            }

        } catch (Exception $e) {

            echo 'Exception when calling the API: ', $e->getMessage(), PHP_EOL;

        }
    }

    private function clients(array $filter = array())
    {
        $apiIstance = new Api\ClientsApi(
            new GuzzleHttp\Client(),
            Configuration::getDefaultConfiguration()->setAccessToken(env('FIC_API_TOKEN'))
        );

        $results = $apiIstance->listClients(
            env('FIC_API_UID'),
            null,
            'detailed',
            null,
            null,
            null,
            $filter ? array_key_first($filter) . ' = \'' . $filter[array_key_first($filter)] . '\'' : ''
        );

        return $results;
    }

    private function products(array $filter = array())
    {
        $apiIstance = new Api\ProductsApi(
            new GuzzleHttp\Client(),
            Configuration::getDefaultConfiguration()->setAccessToken(env('FIC_API_TOKEN'))
        );

        $results = $apiIstance->listProducts(
            env('FIC_API_UID'),
            null,
            'detailed',
            null,
            null,
            null,
            $filter ? array_key_first($filter) . ' = \'' . $filter[array_key_first($filter)] . '\'' : ''
        );

        return $results;
    }

    private function invoice(array $args = array())
    {
        $apiIstance = new Api\IssuedDocumentsApi(
            new GuzzleHttp\Client(),
            Configuration::getDefaultConfiguration()->setAccessToken(env('FIC_API_TOKEN'))
        );

        $args['type'] = 'invoice';

        $results = $apiIstance->createIssuedDocument(
            env('FIC_API_UID'),
            array(
                'data' => $args
            )
        );

        return $results;
    }
}
