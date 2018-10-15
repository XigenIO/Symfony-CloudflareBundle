<?php

namespace Xigen\Bundle\CloudflareBundle\Service;

use Cloudflare\API\Auth\APIKey;
use Cloudflare\API\Adapter\Guzzle as GuzzleAdapter;
use Cloudflare\API\Endpoints;

class Cloudflare
{
    /**
     * @var string
     */
    protected $email;

    /**
     * @var string
     */
    protected $apikey;

    /**
     * @var \Cloudflare\API\Adapter\Guzzle
     */
    protected $adapter;

    /**
     * __construct
     * @param string $email
     * @param string $apikey
     */
    public function __construct($email, $apikey)
    {
        $this->email = $email;
        $this->apikey = $apikey;

        $this->setAdapter();
    }

    /**
     * Set the Guzzle adapter
     */
    private function setAdapter()
    {
        $this->adapter = new GuzzleAdapter(
            new APIKey($this->email, $this->apikey)
        );
    }

    /**
     * @link https://github.com/cloudflare/cloudflare-php/blob/master/src/Endpoints/User.php
     * @return \Cloudflare\API\Endpoints\User
     */
    public function getUser()
    {
        return new Endpoints\User($this->adapter);
    }

    /**
     * @link https://github.com/cloudflare/cloudflare-php/blob/master/src/Endpoints/Zones.php
     * @return \Cloudflare\API\Endpoints\Zones
     */
    public function getZones()
    {
        return new Endpoints\Zones($this->adapter);
    }

    public function getPageRules()
    {
        return new Endpoints\PageRules($this->adapter);
    }

    public function fetchZoneId($domain): ?string
    {
        $zones = $this->getZones();
        foreach ($zones->listZones()->result as $zone) {
            if ($domain === $zone->name) {
                return (string) $zone->id;
            }
        }

        return false;
    }

    public function clearCache($zone, array $files = []): bool
    {
        $zones = $this->getZones();

        if ([] === $files) {
            return $zones->cachePurgeEverything($zone);
        }

        if (count($files) <= 30) {
            return $zones->cachePurge($zone, $files);
        }

        $this->clearRemainingFiles($zone, $files);

        return true;
    }

    private function clearRemainingFiles($zone, array $files = []): bool
    {
        $clearing = [];
        for ($i = 0; $i < 30; $i++) {
            $result = array_shift($files);
            if (is_null($result)) {
                break;
            }

            $clearing[] = $result;
        }

        if ([] === $clearing) {
            return true;
        }

        $zones = $this->getZones();
        $zones->cachePurge($zone, $clearing);

        if (count($files) !== 0) {
            $this->clearCache($zone, $files);
        }

        return true;
    }
}
