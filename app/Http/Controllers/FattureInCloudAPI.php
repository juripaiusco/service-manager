<?php

namespace App\Http\Controllers;

use FattureInCloud\Api;
use FattureInCloud\Configuration;
use FattureInCloud\Filter\Condition;
use FattureInCloud\Filter\Operator;
use GuzzleHttp;

class FattureInCloudAPI extends Controller
{
    public function api(string $resource, array $args = array())
    {
        try {

            switch ($resource)
            {
                case 'get.clients':
                    return $this->clientsGet($args);
                    break;
                case 'get.products':
                    return $this->productsGet($args);
                    break;
                case 'get.invoice':
                    return $this->invoiceGet($args);
                    break;
                case 'create.invoice':
                    return $this->invoiceCreate($args);
                    break;
                case 'get.invoice.email':
                    return $this->invoiceGetEmail($args);
                    break;
                case 'send.invoice.email':
                    $this->invoiceSendEmail($args);
                    break;
                case 'get.email':
                    return $this->emailGet($args);
                    break;
                case 'get.invoice.received':
                    return $this->invoiceReceivedGet($args);
                    break;
                case 'get.documents':
                    return $this->documentsGet($args);
                    break;
                case 'get.documents.received':
                    return $this->documentsReceivedGet($args);
                    break;
            }

        } catch (Exception $e) {

            echo 'Exception when calling the API: ', $e->getMessage(), PHP_EOL;

        }
    }

    private function clientsGet(array $filter = array())
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

    private function productsGet(array $filter = array())
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

    private function invoiceGet(array $filter = array())
    {
        $apiIstance = new Api\IssuedDocumentsApi(
            new GuzzleHttp\Client(),
            Configuration::getDefaultConfiguration()->setAccessToken(env('FIC_API_TOKEN'))
        );

        $results = $apiIstance->listIssuedDocuments(
            env('FIC_API_UID'),
            'invoice',
            null,
            'detailed',
            null,
            null,
            null,
            isset($filter['q']) ? $filter['q'] : null
        );

        return $results;
    }

    private function invoiceCreate(array $args = array())
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

    private function invoiceGetEmail(array $args = array())
    {
        $apiIstance = new Api\IssuedDocumentsApi(
            new GuzzleHttp\Client(),
            Configuration::getDefaultConfiguration()->setAccessToken(env('FIC_API_TOKEN'))
        );

        $results = $apiIstance->getEmailData(
            env('FIC_API_UID'),
            $args['document_id'],
        );

        return $results;
    }

    private function invoiceSendEmail(array $args = array())
    {
        $apiIstance = new Api\IssuedDocumentsApi(
            new GuzzleHttp\Client(),
            Configuration::getDefaultConfiguration()->setAccessToken(env('FIC_API_TOKEN'))
        );

        $apiIstance->scheduleEmail(
            env('FIC_API_UID'),
            $args['document_id'],
            array(
                'data' => $args['data']
            )
        );
    }

    private function emailGet(array $filter = array())
    {
        $apiIstance = new Api\EmailsApi(
            new GuzzleHttp\Client(),
            Configuration::getDefaultConfiguration()->setAccessToken(env('FIC_API_TOKEN'))
        );

        $results = $apiIstance->listEmails(
            env('FIC_API_UID'),
        );

        return $results;
    }

    private function invoiceReceivedGet(array $filter = array())
    {
        $apiIstance = new Api\ReceivedDocumentsApi(
            new GuzzleHttp\Client(),
            Configuration::getDefaultConfiguration()->setAccessToken(env('FIC_API_TOKEN'))
        );

        $results = $apiIstance->listReceivedDocuments(
            env('FIC_API_UID'),
            'expense',
            null,
            'detailed',
            '-date',
            null,
            null,
            $filter['q']
        );

        return $results;
    }

    private function documentsGet(array $args = array())
    {
        $apiIstance = new Api\IssuedDocumentsApi(
            new GuzzleHttp\Client(),
            Configuration::getDefaultConfiguration()->setAccessToken(env('FIC_API_TOKEN'))
        );

        $results = $apiIstance->listIssuedDocuments(
            env('FIC_API_UID'),
            isset($args['type']) ? $args['type'] : null,
            null,
            'detailed',
            '-date',
            null,
            100,
            isset($args['q']) ? $args['q'] : null
        );

        return $results;
    }

    private function documentsReceivedGet(array $args = array())
    {
        $apiIstance = new Api\ReceivedDocumentsApi(
            new GuzzleHttp\Client(),
            Configuration::getDefaultConfiguration()->setAccessToken(env('FIC_API_TOKEN'))
        );

        $results = $apiIstance->listReceivedDocuments(
            env('FIC_API_UID'),
            isset($args['type']) ? $args['type'] : null,
            null,
            'detailed',
            '-date',
            null,
            100,
            isset($args['q']) ? $args['q'] : null
        );

        return $results;
    }
}
