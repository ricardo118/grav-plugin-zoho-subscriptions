<?php

namespace Zoho\Subscription\Api;

use Zoho\Subscription\Client\Client;

/**
 * @author Hang Pham <thi@yproximite.com>
 * @author Tristan Bessoussa <tristan.bessoussa@gmail.com>
 *
 * @link https://www.zoho.com/subscriptions/api/v1/#invoices
 */
class Invoice extends Client
{
    /**
     * @param string $customerId The customer's id
     *
     * @param string $filter
     * @return array
     * @throws \Exception
     */
    public function listInvoicesByCustomer($customerId, $filter = 'Unpaid')
    {
        $cacheKey = sprintf('zoho_invoices_%s', $customerId);
        $hit = $this->getFromCache($cacheKey);

        if (false === $hit) {
            $response = $this->client->request('GET', 'invoices', [
                'query' => [
                    'customer_id' => $customerId,
                    'filter_by' => 'Status.'. $filter
                ],
            ]);

            $result = $this->processResponse($response);

            $invoices = $result['invoices'];

            $this->saveToCache($cacheKey, $invoices);

            return $invoices;
        }

        return $hit;
    }

    /**
     * @param string $invoiceId The invoice's id
     *
     * @throws \Exception
     *
     * @return array
     */
    public function getInvoice($invoiceId)
    {
        $cacheKey = sprintf('zoho_invoice_%s', $invoiceId);
        $hit = $this->getFromCache($cacheKey);

        if (false === $hit) {
            $response = $this->client->request('GET', sprintf('invoices/%s', $invoiceId));

            $result = $this->processResponse($response);

            $invoice = $result['invoice'];

            $this->saveToCache($cacheKey, $invoice);

            return $invoice;
        }

        return $hit;
    }

    /**
     * @param string $invoiceId The invoice's id
     *
     * @throws \Exception
     *
     * @return array
     */
    public function getInvoicePdf($invoiceId)
    {
        if(!defined('STDOUT')) define('STDOUT', fopen('php://stdout', 'w'));
        $response = $this->client->request('GET', sprintf('invoices/%s', $invoiceId), [
            'stream' => true,
            'sink' => STDOUT,
            'query' => ['accept' => 'pdf']
        ]);
        header("Content-Description: File Transfer");
        header("Content-Type: ".$response->getHeaderLine('Content-Type'));
        header("Content-Disposition: ".$response->getHeaderLine('Content-Disposition'));
        $body = $response->getBody();

        while (!$body->eof()) {
            echo $body->read(1024);
        }
        exit();

    }
}
