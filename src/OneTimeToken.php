<?php

declare(strict_types = 1);

namespace BehzadBabaei\PaymentWall;

class OneTimeToken extends ApiObject
{
    const GATEWAY_TOKENIZATION_URL = 'https://pwgateway.com/api/token';

    /**
     * @var mixed|null
     */
    protected $token;

    /**
     * @var mixed|null
     */
    protected $test;

    /**
     * @var mixed|null
     */
    protected $active;

    /**
     * @var mixed|null
     */
    protected $expires_in;

    /**
     * @return mixed|null
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @return mixed|null
     */
    public function isTest()
    {
        return $this->test;
    }

    /**
     * @return mixed|null
     */
    public function isActive()
    {
        return $this->active;
    }

    /**
     * @return mixed|null
     */
    public function getExpirationTime()
    {
        return $this->expires_in;
    }

    /**
     * @return mixed|string
     */
    public function getEndpointName()
    {
        return self::API_OBJECT_ONE_TIME_TOKEN;
    }
}
