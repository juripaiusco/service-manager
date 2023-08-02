<?php

namespace App\Http\Controllers;

use FattureInCloud\Api;
use FattureInCloud\Configuration;
use GuzzleHttp;

class FattureInCloudAPI extends Controller
{
    public function api(string $resource, array $filter)
    {
        try {

            if ($resource == 'clients')
            {
                return $this->clients($filter);
            }

        } catch (Exception $e) {

            echo 'Exception when calling the API: ', $e->getMessage(), PHP_EOL;

        }
    }

    public function clients($filter)
    {
        $apiIstance = new Api\ClientsApi(
            new GuzzleHttp\Client(),
            Configuration::getDefaultConfiguration()->setAccessToken(env('FIC_API_TOKEN'))
        );

        $clients = $apiIstance->listClients(
            env('FIC_API_UID'),
            null,
            'detailed',
            null,
            null,
            null,
            array_key_first($filter) . ' = \'' . $filter[array_key_first($filter)] . '\''
        );

        return $clients;
    }
}
