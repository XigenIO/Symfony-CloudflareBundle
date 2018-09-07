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
}
