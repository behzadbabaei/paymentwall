<?php

declare(strict_types = 1);

namespace BehzadBabaei\PaymentWall\Response;

use function json_encode;

abstract class ResponseAbstract
{
    /**
     * @var array
     */
    protected array $response;

    /**
     * ResponseAbstract constructor.
     *
     * @param array $response
     */
    public function __construct($response = [])
    {
        $this->response = $response;
    }

    /**
     * @return false|string
     */
    protected function wrapInternalError()
    {
        $response = [
            'success' => 0,
            'error'   => [
                'message' => 'Internal error'
            ]
        ];
        return json_encode($response);
    }
}
