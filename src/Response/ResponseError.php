<?php

declare(strict_types = 1);

namespace BehzadBabaei\PaymentWall\Response;

use function json_encode;

class ResponseError extends ResponseAbstract implements ResponseInterface
{
    /**
     * @return false|string
     */
    public function process()
    {
        if (!isset($this->response)) {
            return $this->wrapInternalError();
        }

        $response = [
            'success' => 0,
            'error'   => $this->getErrorMessageAndCode($this->response)
        ];

        return json_encode($response);
    }

    /**
     * @param $response
     *
     * @return array
     */
    public function getErrorMessageAndCode($response) : array
    {
        return [
            'message' => $response['error'],
            'code'    => $response['code']
        ];
    }
}
