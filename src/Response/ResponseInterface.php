<?php

declare(strict_types = 1);

namespace BehzadBabaei\PaymentWall\Response;

interface ResponseInterface
{
    /**
     * @return mixed
     */
    public function process();
}
