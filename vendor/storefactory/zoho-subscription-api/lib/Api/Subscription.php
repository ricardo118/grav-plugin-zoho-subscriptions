<?php

namespace Zoho\Subscription\Api;

use Zoho\Subscription\Client\Client;

/**
 * Subscription.
 *
 * @author Elodie Nazaret <elodie@yproximite.com>
 *
 * @link https://www.zoho.com/subscriptions/api/v1/#subscriptions
 */
class Subscription extends Client
{
    /**
     * @param array $data
     *
     * @throws \Exception
     *
     * @return string
     */
    public function createSubscription($data)
    {
        $response = $this->client->request('POST', 'subscriptions', [
            'content-type' => 'application/json',
            'body' => json_encode($data),
        ]);

        return $this->processResponse($response);
    }

    /**
     * @param string $subscriptionId The subscription's id
     * @param array  $data
     *
     * @throws \Exception
     *
     * @return string
     */
    public function buyOneTimeAddonForASubscription($subscriptionId, $data)
    {
        $response = $this->client->request('POST', sprintf('subscriptions/%s/buyonetimeaddon', $subscriptionId), [
            'json' => json_encode($data),
        ]);

        return $this->processResponse($response);
    }

    /**
     * @param string $subscriptionId The subscription's id
     * @param string $couponCode     The coupon's code
     *
     * @throws \Exception
     *
     * @return array
     */
    public function associateCouponToASubscription($subscriptionId, $couponCode)
    {
        $response = $this->client->request('POST', sprintf('subscriptions/%s/coupons/%s', $subscriptionId, $couponCode));

        return $this->processResponse($response);
    }

    /**
     * @param string $subscriptionId The subscription's id
     *
     * @throws \Exception
     *
     * @return string
     */
    public function reactivateSubscription($subscriptionId)
    {
        $response = $this->client->request('POST', sprintf('subscriptions/%s/reactivate', $subscriptionId));

        return $this->processResponse($response);
    }

    /**
     * @param string $subscriptionId The subscription's id
     *
     * @throws \Exception
     *
     * @return array
     */
    public function getSubscription($subscriptionId)
    {
        $cacheKey = sprintf('zoho_subscription_%s', $subscriptionId);
        $hit = $this->getFromCache($cacheKey);

        if (false === $hit) {
            $response = $this->client->request('GET', sprintf('subscriptions/%s', $subscriptionId));

            $result = $this->processResponse($response);

            $subscription = $result['subscription'];

            $this->saveToCache($cacheKey, $subscription);

            return $subscription;
        }

        return $hit;
    }

    /**
     * @param string $customerId The customer's id
     *
     * @throws \Exception
     *
     * @return array
     */
    public function listSubscriptionsByCustomer($customerId)
    {
        $cacheKey = sprintf('zoho_subscriptions_%s', $customerId);
        $hit = $this->getFromCache($cacheKey);

        if (false === $hit) {
            $response = $this->client->request('GET', 'subscriptions', [
                'query' => ['customer_id' => $customerId],
            ]);

            $result = $this->processResponse($response);

            $invoices = $result['subscriptions'];

            $this->saveToCache($cacheKey, $invoices);

            return $invoices;
        }

        return $hit;
    }
}
