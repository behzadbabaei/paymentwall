<?php

declare(strict_types = 1);

namespace BehzadBabaei\PaymentWall\Response;

use function json_encode;

class ResponseSuccess extends ResponseAbstract implements ResponseInterface
{
    /**
     * @return mixed
     */
    public function process()
    {
        if (!isset($this->response)) {
            return $this->wrapInternalError();
        }

        $response = array(
            'success' => 1
        );

        return json_encode($response);
    }
}
