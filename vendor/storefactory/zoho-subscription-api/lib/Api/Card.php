<?php

namespace Zoho\Subscription\Api;

use Zoho\Subscription\Client\Client;

/**
 * @author Ricardo Verdugo
 *
 * @link   https://www.zoho.com/subscriptions/api/v1/#customers
 */
class Card extends Client
{

    public function getCardListByCustomerId($customerId)
    {
        $cacheKey = sprintf('zoho_card_%s', md5($customerId));
        $hit = $this->getFromCache($cacheKey);

        if (false === $hit) {
            $response = $this->client->request('GET', sprintf('customers/%s/cards', $customerId));
            $result = $this->processResponse($response);

            $cards = $result['cards'];

            $this->saveToCache($cacheKey, $cards);

            return $cards;
        }

        return $hit;
    }

    public function chargeCard($invoiceId, $cardId)
    {
        $response = $this->client->request('POST', sprintf('invoices/%s/collect', $invoiceId), [
            'content-type' => 'application/json',
            'body' => json_encode($cardId),
        ]);
        $result = $this->processResponse($response);

        return $result;
    }

    public function updateCardDetails($customerId, $cardId, $data) {

        $response = $this->client->request('PUT', sprintf('customers/%s/cards/%s', $customerId, $cardId), [
            'content-type' => 'application/json',
            'body' => json_encode($data),
        ]);

        $result = $this->processResponse($response);

        if ($result['code'] == '0') {
            $card = $result['card'];

            return $card;
        } else {
            return false;
        }
    }

}
