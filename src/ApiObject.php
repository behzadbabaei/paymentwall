<?php

declare(strict_types = 1);

namespace BehzadBabaei\PaymentWall;

use Exception;

use function json_encode;
use function in_array;

abstract class ApiObject extends PaymentwallInstance
{
    const API_BRICK_SUBPATH = 'brick';
    const API_OBJECT_CHARGE = 'charge';
    const API_OBJECT_SUBSCRIPTION = 'subscription';
    const API_OBJECT_ONE_TIME_TOKEN = 'token';

    protected $properties = [];
    protected $_id;
    protected $_rawResponse = '';
    protected $_responseLogInformation = [];
    protected $brickSubEndpoints = [
        self::API_OBJECT_CHARGE, self::API_OBJECT_SUBSCRIPTION, self::API_OBJECT_ONE_TIME_TOKEN
    ];

    /**
     * @return mixed
     */
    abstract function getEndpointName();

    /**
     * ApiObject constructor.
     *
     * @param string $id
     */
    public function __construct($id = '')
    {
        if (!empty($id)) {
            $this->_id = $id;
        }
    }

    /**
     * @param array $params
     *
     * @return $this
     * @throws \Exception
     */
    public final function create($params = [])
    {
        $httpAction = new HttpAction($this, $params, [$this->getApiBaseHeader()]);

        $this->setPropertiesFromResponse($httpAction->run());

        return $this;
    }

    /**
     * @param $property
     *
     * @return mixed|null
     */
    public function __get($property)
    {
        return isset($this->properties[$property]) ? $this->properties[$property] : null;
    }

    /**
     * @return string
     */
    public function getApiUrl() : string
    {
        if ($this->getEndpointName() === self::API_OBJECT_ONE_TIME_TOKEN && !$this->getConfig()->isTest()) {
            return OneTimeToken::GATEWAY_TOKENIZATION_URL;
        } else {
            return $this->getApiBaseUrl().$this->getSubPath().'/'.$this->getEndpointName();
        }
    }

    /**
     * Returns raw data about the response that can be presented to the end-user:
     *    success => 0 or 1
     *    error =>
     *        message    - human-readable error message
     *        code        - error code, see https://www.paymentwall.com/us/documentation/Brick/2968#error
     *    secure =>
     *        formHTML    - needed to complete 3D Secure step, HTML of the form to be submitted to the user to redirect
     *        him to the bank page
     *
     * @return array
     *
     */
    public function _getPublicData()
    {
        /**
         * $responseModel = ResponseFactory::get($this->getPropertiesFromResponse());
         * return $responseModel instanceof ResponseInterface ? $responseModel->process() : '';
         */

        /**
         * @todo encapsulate this into Paymentwall_Response_Factory better; right now it returns success=1 for 3ds case
         */
        $response = $this->getPropertiesFromResponse();
        $result = [];

        if (isset($response['type']) && $response['type'] == 'Error') {
            $result = [
                'success' => 0,
                'error'   => [
                    'message' => $response['error'],
                    'code'    => $response['code']
                ]
            ];
        } elseif (!empty($response['secure'])) {
            $result = [
                'success' => 0,
                'secure'  => $response['secure']
            ];
        } elseif ($this->isSuccessful()) {
            $result['success'] = 1;
        } else {
            $result = [
                'success' => 0,
                'error'   => [
                    'message' => 'Internal error'
                ]
            ];
        }

        return $result;
    }

    /**
     * @return string json encoded result of ApiObject::getPublicData()
     */
    public function getPublicData()
    {
        return json_encode($this->_getPublicData());
    }

    /**
     * @return array
     */
    public function getProperties() : array
    {
        return $this->properties;
    }

    /**
     * @return string
     */
    public function getRawResponseData() : string
    {
        return $this->_rawResponse;
    }

    /**
     * @param string $response
     *
     * @return void
     * @throws \Exception
     */
    protected function setPropertiesFromResponse($response = '')
    {
        if (!empty($response)) {
            $this->_rawResponse = $response;
            $this->properties = (array) $this->preparePropertiesFromResponse($response);
        } else {
            throw new Exception('Empty response');
        }
    }

    /**
     * @return string
     */
    protected function getSubPath() : string
    {
        return (in_array($this->getEndpointName(), $this->brickSubEndpoints))
            ? '/'.self::API_BRICK_SUBPATH
            : '';
    }

    /**
     * @return array
     */
    protected function getPropertiesFromResponse() : array
    {
        return $this->properties;
    }

    /**
     * @param string $string
     *
     * @return mixed
     */
    protected function preparePropertiesFromResponse($string = '')
    {
        return json_decode($string, false);
    }

    /**
     * @return string
     */
    protected function getApiBaseHeader() : string
    {
        return 'X-ApiKey: '.$this->getPrivateKey();
    }

    /**
     * @param string $action
     * @param string $method
     *
     * @return $this
     * @throws \Exception
     */
    protected function doApiAction($action = '', $method = 'post')
    {
        $actionUrl = $this->getApiUrl().'/'.$this->_id.'/'.$action;
        $httpAction = new HttpAction($this, ['id' => $this->_id], [$this->getApiBaseHeader()]);

        $this->_responseLogInformation = $httpAction->getResponseLogInformation();
        $this->setPropertiesFromResponse(
            $method == 'get' ? $httpAction->get($actionUrl) : $httpAction->post($actionUrl)
        );

        return $this;
    }

    /**
     * @return array
     */
    public function getResponseLogInformation() : array
    {
        return $this->_responseLogInformation;
    }
}
