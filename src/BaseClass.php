<?php

declare(strict_types = 1);

namespace BehzadBabaei\PaymentWall;

class BaseClass extends Config
{
    /**
     * @param int $apiType
     */
    public static function setApiType($apiType = 0)
    {
        return self::getInstance()->setLocalApiType($apiType);
    }

    /**
     * @param string $appKey
     */
    public static function setAppKey($appKey = '')
    {
        return self::getInstance()->setPublicKey($appKey);
    }

    /**
     * @param string $secretKey
     */
    public static function setSecretKey($secretKey = '')
    {
        return self::getInstance()->setPrivateKey($secretKey);
    }
}
