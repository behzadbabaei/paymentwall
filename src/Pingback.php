<?php

declare(strict_types = 1);

namespace BehzadBabaei\PaymentWall;

use BehzadBabaei\PaymentWall\Signature\SignatureAbstract;
use BehzadBabaei\PaymentWall\Signature\SignaturePingback;

use function in_array;
use function explode;
use function ip2long;
use function array_key_exists;

class Pingback extends PaymentwallInstance
{
    const PINGBACK_TYPE_REGULAR = 0;
    const PINGBACK_TYPE_GOODWILL = 1;
    const PINGBACK_TYPE_NEGATIVE = 2;

    const PINGBACK_TYPE_RISK_UNDER_REVIEW = 200;
    const PINGBACK_TYPE_RISK_REVIEWED_ACCEPTED = 201;
    const PINGBACK_TYPE_RISK_REVIEWED_DECLINED = 202;

    const PINGBACK_TYPE_RISK_AUTHORIZATION_VOIDED = 203;

    const PINGBACK_TYPE_SUBSCRIPTION_CANCELLATION = 12;
    const PINGBACK_TYPE_SUBSCRIPTION_EXPIRED = 13;
    const PINGBACK_TYPE_SUBSCRIPTION_PAYMENT_FAILED = 14;

    /**
     * @var array
     */
    protected array $parameters;

    /**
     * @var string
     */
    protected string $ipAddress;

    /**
     * Pingback constructor.
     *
     * @param array  $parameters
     * @param string $ipAddress
     */
    public function __construct(array $parameters, string $ipAddress)
    {
        $this->parameters = $parameters;
        $this->ipAddress = $ipAddress;
    }

    /**
     * @param false $skipIpWhitelistCheck
     *
     * @return bool
     */
    public function validate($skipIpWhitelistCheck = false) : bool
    {
        $validated = false;

        if ($this->isParametersValid()) {
            if ($this->isIpAddressValid() || $skipIpWhitelistCheck) {
                if ($this->isSignatureValid()) {
                    $validated = true;
                } else {
                    $this->appendToErrors('Wrong signature');
                }
            } else {
                $this->appendToErrors('IP address is not whitelisted');
            }
        } else {
            $this->appendToErrors('Missing parameters');
        }

        return $validated;
    }

    /**
     * @return bool
     */
    public function isSignatureValid() : bool
    {
        $signatureParamsToSign = [];

        if ($this->getApiType() == Config::API_VC) {
            $signatureParams = array('uid', 'currency', 'type', 'ref');
        } else if ($this->getApiType() == Config::API_GOODS) {
            $signatureParams = array('uid', 'goodsid', 'slength', 'speriod', 'type', 'ref');
        } else { // API_CART
            $signatureParams = array('uid', 'goodsid', 'type', 'ref');
            $this->parameters['sign_version'] = SignatureAbstract::VERSION_TWO;
        }

        if (empty($this->parameters['sign_version']) || $this->parameters['sign_version'] == SignatureAbstract::VERSION_ONE) {

            foreach ($signatureParams as $field) {
                $signatureParamsToSign[$field] = isset($this->parameters[$field]) ? $this->parameters[$field] : null;
            }

            $this->parameters['sign_version'] = SignatureAbstract::VERSION_ONE;
        } else {
            $signatureParamsToSign = $this->parameters;
        }

        $pingbackSignatureModel = new SignaturePingback();

        $signatureCalculated = $pingbackSignatureModel->calculate(
            $signatureParamsToSign,
            $this->parameters['sign_version']
        );

        $signature = isset($this->parameters['sig']) ? $this->parameters['sig'] : null;

        return $signature == $signatureCalculated;
    }

    /**
     * @return bool
     */
    public function isIpAddressValid()
    {
        $ipsWhitelist = [
            '174.36.92.186',
            '174.36.96.66',
            '174.36.92.187',
            '174.36.92.192',
            '174.37.14.28'
        ];

        $rangesWhitelist = [
            '216.127.71.0/24'
        ];

        if (in_array($this->ipAddress, $ipsWhitelist)) {
            return true;
        }

        foreach ($rangesWhitelist as $range) {
            if ($this->isCidrMatched($this->ipAddress, $range)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param $ip
     * @param $range
     *
     * @return bool
     */
    public function isCidrMatched($ip, $range)
    {
        list($subnet, $bits) = explode('/', $range);
        $ip = ip2long($ip);
        $subnet = ip2long($subnet);
        $mask = -1 << (32 - $bits);
        $subnet &= $mask;

        return ($ip & $mask) == $subnet;
    }

    /**
     * @return bool
     */
    public function isParametersValid()
    {
        $errorsNumber = 0;

        if ($this->getApiType() == Config::API_VC) {
            $requiredParams = array('uid', 'currency', 'type', 'ref', 'sig');
        } else if ($this->getApiType() == Config::API_GOODS) {
            $requiredParams = array('uid', 'goodsid', 'type', 'ref', 'sig');
        } else { // Cart API
            $requiredParams = array('uid', 'goodsid', 'type', 'ref', 'sig');
        }

        foreach ($requiredParams as $field) {
            if (!isset($this->parameters[$field]) || $this->parameters[$field] === '') {
                $this->appendToErrors('Parameter '.$field.' is missing');
                $errorsNumber++;
            }
        }

        return $errorsNumber == 0;
    }

    /**
     * @param $param
     *
     * @return mixed|null
     */
    public function getParameter($param)
    {
        return isset($this->parameters[$param]) ? $this->parameters[$param] : null;
    }

    /**
     * @return int|null
     */
    public function getType()
    {
        return isset($this->parameters['type']) ? intval($this->parameters['type']) : null;
    }

    /**
     * @return string
     */
    public function getTypeVerbal()
    {
        $typeVerbal = '';

        $pingbackTypes = [
            self::PINGBACK_TYPE_SUBSCRIPTION_CANCELLATION   => 'user_subscription_cancellation',
            self::PINGBACK_TYPE_SUBSCRIPTION_EXPIRED        => 'user_subscription_expired',
            self::PINGBACK_TYPE_SUBSCRIPTION_PAYMENT_FAILED => 'user_subscription_payment_failed'
        ];

        if (!empty($this->parameters['type'])) {
            if (array_key_exists($this->parameters['type'], $pingbackTypes)) {
                $typeVerbal = $pingbackTypes[$this->parameters['type']];
            }
        }

        return $typeVerbal;
    }

    /**
     * @return mixed|null
     */
    public function getUserId()
    {
        return $this->getParameter('uid');
    }

    /**
     * @return mixed|null
     */
    public function getVirtualCurrencyAmount()
    {
        return $this->getParameter('currency');
    }

    /**
     * @return mixed|null
     */
    public function getProductId()
    {
        return $this->getParameter('goodsid');
    }

    /**
     * @return mixed|null
     */
    public function getProductPeriodLength()
    {
        return $this->getParameter('slength');
    }

    /**
     * @return mixed|null
     */
    public function getProductPeriodType()
    {
        return $this->getParameter('speriod');
    }

    /**
     * @return \BehzadBabaei\PaymentWall\Product
     */
    public function getProduct()
    {
        return new Product(
            $this->getProductId(),
            0,
            null,
            null,
            $this->getProductPeriodLength() > 0 ? Product::TYPE_SUBSCRIPTION : Product::TYPE_FIXED,
            $this->getProductPeriodLength(),
            $this->getProductPeriodType()
        );
    }

    /**
     * @return array
     */
    public function getProducts() : array
    {
        $result = array();
        $productIds = $this->getParameter('goodsid');

        if (!empty($productIds) && is_array($productIds)) {
            foreach ($productIds as $Id) {
                $result[] = new Product($Id);
            }
        }

        return $result;
    }

    /**
     * @return mixed|null
     */
    public function getReferenceId()
    {
        return $this->getParameter('ref');
    }

    /**
     * @return string
     */
    public function getPingbackUniqueId() : string
    {
        return $this->getReferenceId().'_'.$this->getType();
    }

    /**
     * @return bool
     */
    public function isDeliverable() : bool
    {
        return (
            $this->getType() === self::PINGBACK_TYPE_REGULAR ||
            $this->getType() === self::PINGBACK_TYPE_GOODWILL ||
            $this->getType() === self::PINGBACK_TYPE_RISK_REVIEWED_ACCEPTED
        );
    }

    /**
     * @return bool
     */
    public function isCancelable() : bool
    {
        return (
            $this->getType() === self::PINGBACK_TYPE_NEGATIVE
            || $this->getType() === self::PINGBACK_TYPE_RISK_REVIEWED_DECLINED
        );
    }

    /**
     * @return bool
     */
    public function isUnderReview() : bool
    {
        return $this->getType() === self::PINGBACK_TYPE_RISK_UNDER_REVIEW;
    }
}
