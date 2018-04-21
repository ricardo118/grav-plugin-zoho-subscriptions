<?php

namespace Zoho\Subscription\Client;

use Doctrine\Common\Cache\Cache;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7\Response;

class Client
{
    /**
     * @var String
     */
    protected $token;

    /**
     * @var String
     */
    protected $organizationId;

    /**
     * @var Cache
     */
    protected $cache;

    /**
     * @var GuzzleClient
     */
    protected $client;

    /**
     * @var int
     */
    protected $ttl;

    /**
     * @param string                            $token
     * @param int                               $organizationId
     * @param \Doctrine\Common\Cache\Cache|null $cache
     * @param int                               $ttl
     */
    public function __construct($token, $organizationId, Cache $cache, $ttl = 7200)//Cache $cache,
    {
        $this->token = $token;
        $this->organizationId = $organizationId;
        $this->ttl = $ttl;
        $this->cache = $cache;
        $this->client = new GuzzleClient([
            'headers' => [
                'Authorization' => 'Zoho-authtoken '.$token,
                'X-com-zoho-subscriptions-organizationid' => $organizationId,
            ],
            'base_uri' => 'https://subscriptions.zoho.com/api/v1/',
        ]);
    }

    /**
     * @param Response $response
     *
     * @throws \Exception
     *
     * @return array
     */
    protected function processResponse(Response $response)
    {
        $data = json_decode($response->getBody(), true);

        if ($data['code'] != 0) {

            throw new \Exception('Zoho Api subscription error : '.$data['message']);
        }

        return $data;
    }

    /**
     * @param $key
     *
     * @throws \LogicException
     *
     * @return bool|mixed
     */
    public function getFromCache($key)
    {
        // If the results are already cached
        if ($this->cache->contains($key)) {
            return unserialize($this->cache->fetch($key));
        }

        return false;
    }

    /**
     * @param string $key
     * @param mixed  $values
     *
     * @throws \LogicException
     *
     * @return bool
     */
    public function saveToCache($key, $values)
    {
        if (null === $key) {
            throw new \LogicException('If you want to save to cache, an unique key must be set');
        }

        return $this->cache->save($key, serialize($values), $this->ttl);
    }

    /**
     * @param string $key
     */
    public function deleteCacheByKey($key)
    {
        $this->cache->delete($key);
    }
}
