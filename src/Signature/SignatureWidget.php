<?php

declare(strict_types = 1);

namespace BehzadBabaei\PaymentWall\Signature;

use function md5;
use function hash;
use function is_array;

class SignatureWidget extends SignatureAbstract
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

        if ($version == self::VERSION_ONE) {

            $baseString .= isset($params['uid']) ? $params['uid'] : '';
            $baseString .= $this->getConfig()->getPrivateKey();

            return md5($baseString);

        } else {

            self::ksortMultiDimensional($params);

            $baseString = $this->prepareParams($params, $baseString);

            $baseString .= $this->getConfig()->getPrivateKey();

            if ($version == self::VERSION_TWO) {
                return md5($baseString);
            }

            return hash('sha256', $baseString);
        }
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
            if (!isset($value)) {
                continue;
            }
            if (is_array($value)) {
                foreach ($value as $k => $v) {
                    $baseString .= $key.'['.$k.']'.'='.($v === false ? '0' : $v);
                }
            } else {
                $baseString .= $key.'='.($value === false ? '0' : $value);
            }
        }
        return $baseString;
    }
}
