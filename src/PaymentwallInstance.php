<?php

declare(strict_types = 1);

namespace BehzadBabaei\PaymentWall;

use function implode;

abstract class PaymentwallInstance
{
    protected $config;

    /**
     * @var array
     */
    protected array $errors = [];

    /**
     * @return string
     */
    public function getErrorSummary()
    {
        return implode("\n", $this->getErrors());
    }

    /**
     * @return mixed|null
     */
    protected function getConfig()
    {
        if (!isset($this->config)) {
            $this->config = Config::getInstance();
        }
        return $this->config;
    }

    /**
     * @return string
     */
    protected function getApiBaseUrl() : string
    {
        return $this->getConfig()->getApiBaseUrl();
    }

    /**
     * @return int
     */
    protected function getApiType()
    {
        return $this->getConfig()->getLocalApiType();
    }

    /**
     * @return mixed
     */
    protected function getPublicKey()
    {
        return $this->getConfig()->getPublicKey();
    }

    /**
     * @return mixed
     */
    protected function getPrivateKey()
    {
        return $this->getConfig()->getPrivateKey();
    }

    /**
     * @param string $error
     */
    protected function appendToErrors($error = '')
    {
        $this->errors[] = $error;
    }

    /**
     * @return array
     */
    protected function getErrors()
    {
        return $this->errors;
    }
}
