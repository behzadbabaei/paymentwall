<?php

declare(strict_types = 1);

namespace BehzadBabaei\PaymentWall\Signature;

use function hash;
use function md5;
use function is_array;

class SignaturePingback extends SignatureAbstract
{
    /**
     * @param array $params
     * @param int   $version
     *
     * @return string
     */
    public function process($params = array(), $version = 0) : string
    {
        $baseString = '';

        unset($params['sig']);

        if ($version == self::VERSION_TWO || $version == self::VERSION_THREE) {
            self::ksortMultiDimensional($params);
        }

        $baseString = $this->prepareParams($params, $baseString);

        $baseString .= $this->getConfig()->getPrivateKey();

        if ($version == self::VERSION_THREE) {
            return hash('sha256', $baseString);
        }

        return md5($baseString);
    }

    /**
     * @param array  $params
     * @param string $baseString
     *
     * @return string
     */
    public function prepareParams($params = [], $baseString = '') : string
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
}
