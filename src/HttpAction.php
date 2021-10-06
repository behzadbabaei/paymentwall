<?php

declare(strict_types = 1);

namespace BehzadBabaei\PaymentWall;

use function curl_init;
use function array_merge;
use function curl_setopt;
use function http_build_query;
use function defined;
use function curl_exec;
use function curl_getinfo;
use function substr;
use function curl_close;
use function preg_replace;

class HttpAction extends PaymentwallInstance
{
    protected $apiObject;

    /**
     * @var array
     */
    protected array $apiParams = [];

    /**
     * @var array
     */
    protected array $apiHeaders = [];

    /**
     * @var array
     */
    protected array $responseLogInformation = [];

    /**
     * HttpAction constructor.
     *
     * @param       $object
     * @param array $params
     * @param array $headers
     */
    public function __construct($object, $params = [], $headers = [])
    {
        $this->setApiObject($object);
        $this->setApiParams($params);
        $this->setApiHeaders($headers);
    }

    /**
     * @return mixed
     */
    public function getApiObject()
    {
        return $this->apiObject;
    }

    /**
     * @param \BehzadBabaei\PaymentWall\ApiObject $apiObject
     */
    public function setApiObject(ApiObject $apiObject)
    {
        $this->apiObject = $apiObject;
    }

    /**
     * @return array
     */
    public function getApiParams()
    {
        return $this->apiParams;
    }

    /**
     * @param array $params
     */
    public function setApiParams($params = array())
    {
        $this->apiParams = $params;
    }

    /**
     * @return array
     */
    public function getApiHeaders()
    {
        return $this->apiHeaders;
    }

    /**
     * @param array $headers
     */
    public function setApiHeaders($headers = [])
    {
        $this->apiHeaders = $headers;
    }

    /**
     * @return string|string[]|null
     */
    public function run()
    {
        $result = null;

        if ($this->getApiObject() instanceof ApiObject) {
            $result = $this->apiObjectPostRequest($this->getApiObject());
        }

        return $result;
    }

    /**
     * @param \BehzadBabaei\PaymentWall\ApiObject $object
     *
     * @return string|string[]|null
     */
    public function apiObjectPostRequest(ApiObject $object)
    {
        return $this->request('POST', $object->getApiUrl(), $this->getApiParams(), $this->getApiHeaders());
    }

    /**
     * @param string $url
     *
     * @return string|string[]|null
     */
    public function post($url = '')
    {
        return $this->request('POST', $url, $this->getApiParams(), $this->getApiHeaders());
    }

    /**
     * @param string $url
     *
     * @return string|string[]|null
     */
    public function get($url = '')
    {
        return $this->request('GET', $url, $this->getApiParams(), $this->getApiHeaders());
    }

    /**
     * @param string $httpVerb
     * @param string $url
     * @param array  $params
     * @param array  $customHeaders
     *
     * @return string|string[]|null
     */
    protected function request($httpVerb = '', $url = '', $params = [], $customHeaders = [])
    {
        $curl = curl_init();

        $headers = [$this->getLibraryDefaultRequestHeader()];

        if (!empty($customHeaders)) {
            $headers = array_merge($headers, $customHeaders);
        }

        if (!empty($params)) {
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($params));
        }

        // CURL_SSLVERSION_TLSv1_2 is defined in libcurl version 7.34 or later
        // but unless PHP has been compiled with the correct libcurl headers it
        // won't be defined in your PHP instance.  PHP > 5.5.19 or > 5.6.3
        if (!defined('CURL_SSLVERSION_TLSv1_2')) {
            define('CURL_SSLVERSION_TLSv1_2', 6);
        }

        curl_setopt($curl, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_2);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $httpVerb);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_TIMEOUT, 60);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_HEADER, true);

        $response = curl_exec($curl);

        $headerSize = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        $header = substr($response, 0, $headerSize);
        $body = substr($response, $headerSize);

        $this->responseLogInformation = [
            'header' => $header,
            'body'   => $body,
            'status' => curl_getinfo($curl, CURLINFO_HTTP_CODE)
        ];

        curl_close($curl);

        return $this->prepareResponse($body);
    }

    /**
     * @return string
     */
    protected function getLibraryDefaultRequestHeader() : string
    {
        return 'User-Agent: Paymentwall PHP Library v. '.$this->getConfig()->getVersion();
    }

    /**
     * @param string $string
     *
     * @return string|string[]|null
     */
    protected function prepareResponse($string = '')
    {
        return preg_replace('/\x{FEFF}/u', '', $string);
    }

    /**
     * @return array
     */
    public function getResponseLogInformation()
    {
        return $this->responseLogInformation;
    }
}
