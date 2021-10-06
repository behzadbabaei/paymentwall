<?php

declare(strict_types = 1);

namespace BehzadBabaei\PaymentWall;

use function array_merge;

class GenerericApiObject extends ApiObject
{
    /**
     * API type
     *
     * @var string
     */
    protected string $api;

    /**
     * Paymentwall_HttpAction object
     *
     * @var HttpAction
     */
    protected HttpAction $httpAction;

    /**
     * GenerericApiObject constructor.
     *
     * @param $type
     */
    public function __construct($type)
    {
        parent::__construct('');
        $this->api = $type;
        $this->httpAction = new HttpAction($this);
    }

    /**
     * @return string
     */
    public function getEndpointName() : string
    {
        return $this->api;
    }

    /**
     * Make post request
     *
     * @param array $params
     * @param array $headers
     *
     * @return array|null
     */
    public function post($params = [], $headers = []) : ?array
    {
        if (empty($params)) {
            return null;
        }

        $this->httpAction->setApiParams($params);

        $this->httpAction->setApiHeaders(array_merge([$this->getApiBaseHeader()], $headers));

        return (array) $this->preparePropertiesFromResponse(
            $this->httpAction->post(
                $this->getApiUrl()
            )
        );
    }
}
