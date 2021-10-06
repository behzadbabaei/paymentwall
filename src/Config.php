<?php

declare(strict_types = 1);

namespace BehzadBabaei\PaymentWall;

use function strpos;

class Config
{
    const VERSION = '2.0.0';

    const API_BASE_URL = 'https://api.paymentwall.com/api';

    const API_VC = 1;
    const API_GOODS = 2;
    const API_CART = 3;

    protected int $apiType = self::API_GOODS;
    protected string $apiBaseUrl = self::API_BASE_URL;

    protected $publicKey;
    protected $privateKey;

    private static $instance;

    /**
     * @return string
     */
    public function getApiBaseUrl()
    {
        return $this->apiBaseUrl;
    }

    /**
     * @param string $url
     */
    public function setApiBaseUrl($url = '') : void
    {
        $this->apiBaseUrl = $url;
    }

    /**
     * @return int
     */
    public function getLocalApiType()
    {
        return $this->apiType;
    }

    /**
     * @param int $apiType
     */
    public function setLocalApiType($apiType = 0)
    {
        $this->apiType = $apiType;
    }

    /**
     * @return mixed
     */
    public function getPublicKey()
    {
        return $this->publicKey;
    }

    /**
     * @param string $key
     */
    public function setPublicKey($key = '')
    {
        $this->publicKey = $key;
    }

    /**
     * @return mixed
     */
    public function getPrivateKey()
    {
        return $this->privateKey;
    }

    /**
     * @param string $key
     */
    public function setPrivateKey($key = '')
    {
        $this->privateKey = $key;
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        return self::VERSION;
    }

    /**
     * @return bool
     */
    public function isTest()
    {
        return strpos($this->getPublicKey(), 't_') === 0;
    }

    /**
     * @param array $config
     */
    public function set($config = array())
    {
        if (isset($config['api_base_url'])) {
            $this->setApiBaseUrl($config['api_base_url']);
        }
        if (isset($config['api_type'])) {
            $this->setLocalApiType($config['api_type']);
        }
        if (isset($config['public_key'])) {
            $this->setPublicKey($config['public_key']);
        }
        if (isset($config['private_key'])) {
            $this->setPrivateKey($config['private_key']);
        }
    }

    /**
     * @return $this Returns class instance.
     */
    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            $className = __CLASS__;
            self::$instance = new $className;
        }
        return self::$instance;
    }
}
