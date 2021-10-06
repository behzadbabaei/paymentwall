<?php

declare(strict_types = 1);

namespace BehzadBabaei\PaymentWall;

use BehzadBabaei\PaymentWall\Signature\SignatureAbstract;

use function time;
use function array_merge;
use function md5;
use function ksort;

class Mobiamo extends ApiObject
{
    protected $token;

    const API_OBJECT_MOBIAMO = 'mobiamo';

    /**
     * @return string
     */
    public function getEndpointName() : string
    {
        return self::API_OBJECT_MOBIAMO;
    }

    /**
     * @param $params
     *
     * @return array
     * @throws \Exception
     */
    public function getToken($params)
    {
        $defaultParams = [
            'key'          => $this->getConfig()->getPublicKey(),
            'ts'           => time(),
            'sign_version' => SignatureAbstract::VERSION_TWO
        ];
        $params = array_merge($defaultParams, $params);
        $params['sign'] = $this->calculateSignature($params);
        $this->doApiAction('token', 'post', $params);

        return $this->getProperties();
    }

    /**
     * @param $token
     * @param $params
     *
     * @return array
     * @throws \Exception
     */
    public function initPayment($token, $params)
    {
        $this->token = $token;
        $params['key'] = $this->getConfig()->getPublicKey();
        $this->doApiAction('init-payment', 'post', $params);

        return $this->getProperties();
    }

    /**
     * @param $token
     * @param $params
     *
     * @return array
     * @throws \Exception
     */
    public function processPayment($token, $params)
    {
        $this->token = $token;
        $params['key'] = $this->getConfig()->getPublicKey();
        $this->doApiAction('process-payment', 'post', $params);

        return $this->getProperties();
    }

    /**
     * @param $token
     * @param $params
     *
     * @return array
     * @throws \Exception
     */
    public function getPaymentInfo($token, $params)
    {
        $this->token = $token;
        $params['key'] = $this->getConfig()->getPublicKey();
        $this->doApiAction('get-payment', 'post', $params);

        return $this->getProperties();
    }

    /**
     * @param array $params
     *
     * @return string
     */
    protected function calculateSignature($params = [])
    {
        $baseString = '';
        $this->ksortMultiDimensional($params);

        $baseString = $this->prepareParams($params, $baseString);

        $baseString .= $this->getConfig()->getPrivateKey();

        return md5($baseString);
    }

    /**
     * @param array  $params
     * @param string $baseString
     *
     * @return string
     */
    protected function prepareParams($params = [], $baseString = '')
    {
        foreach ($params as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $k => $v) {
                    $baseString .= $key.'['.$k.']'.'='.$v;
                }
            } else {
                $baseString .= $key.'='.$value;
            }
        }

        return $baseString;
    }

    /**
     * @param array $params
     */
    protected function ksortMultiDimensional(&$params = [])
    {
        if (is_array($params)) {
            ksort($params);
            foreach ($params as &$p) {
                if (is_array($p)) {
                    ksort($p);
                }
            }
        }
    }

    /**
     * @return string
     */
    public function getApiUrl() : string
    {
        if ($this->getEndpointName() === self::API_OBJECT_ONE_TIME_TOKEN && !$this->getConfig()->isTest()) {
            return OneTimeToken::GATEWAY_TOKENIZATION_URL;
        } else {
            return $this->getApiBaseUrl().'/'.$this->getEndpointName();
        }
    }

    /**
     * @param string $action
     * @param string $method
     * @param array  $params
     *
     * @return $this|\BehzadBabaei\PaymentWall\Mobiamo
     * @throws \Exception
     */
    protected function doApiAction($action = '', $method = 'post', $params = [])
    {
        $actionUrl = $this->getApiUrl().'/'.$action;
        $httpAction = new HttpAction($this, $params, [$this->getApiBaseHeader()]);

        $this->setPropertiesFromResponse(
            $method == 'get' ? $httpAction->get($actionUrl) : $httpAction->post($actionUrl)
        );

        return $this;
    }

    /**
     * @return string
     */
    protected function getApiBaseHeader() : string
    {
        if ($this->token) {
            return 'token: '.$this->token;
        }

        return '';
    }
}
