<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use FattureInCloud\Api;
use FattureInCloud\Configuration;
use GuzzleHttp;

class FattureInCloudAPI extends Controller
{
    public function api(string $call, array $filter)
    {
        $config = Configuration::getDefaultConfiguration()->setAccessToken(env('FIC_API_TOKEN'));

        try {

            if ($call == 'clients')
            {
                $apiIstance = new Api\ClientsApi(
                    new GuzzleHttp\Client(),
                    $config
                );

                $clients = $apiIstance->listClients(
                    env('FIC_API_UID'),
                    null,
                    null,
                    null,
                    null,
                    null,
                    array_key_first($filter) . ' = \'' . $filter[array_key_first($filter)] . '\''
                );

                return $clients;
            }

        } catch (Exception $e) {

            echo 'Exception when calling the API: ', $e->getMessage(), PHP_EOL;

        }
    }
}
