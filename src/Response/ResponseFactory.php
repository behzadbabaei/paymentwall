<?php

declare(strict_types = 1);

namespace BehzadBabaei\PaymentWall\Response;

use function ucfirst;

class ResponseFactory
{
    const CLASS_NAME_PREFIX = 'Response';

    const RESPONSE_SUCCESS = 'success';
    const RESPONSE_ERROR = 'error';

    /**
     * @param array $response
     *
     * @return mixed
     */
    public static function get($response = [])
    {
        $responseModel = null;

        $responseModel = self::getClassName($response);

        return new $responseModel($response);
    }

    /**
     * @param array $response
     *
     * @return string
     */
    public static function getClassName($response = []) : string
    {
        $responseType = (isset($response['type']) && $response['type'] == 'Error') ? self::RESPONSE_ERROR : self::RESPONSE_SUCCESS;

        return self::CLASS_NAME_PREFIX.ucfirst($responseType);
    }
}
