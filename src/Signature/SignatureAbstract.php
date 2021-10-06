<?php

declare(strict_types = 1);

namespace BehzadBabaei\PaymentWall\Signature;

use BehzadBabaei\PaymentWall\PaymentwallInstance;

use function is_array;
use function ksort;

abstract class SignatureAbstract extends PaymentwallInstance
{
    const VERSION_ONE = 1;
    const VERSION_TWO = 2;
    const VERSION_THREE = 3;
    const DEFAULT_VERSION = 3;

    /**
     * @param array $params
     * @param int   $version
     *
     * @return mixed
     */
    abstract function process($params = [], $version = 0);

    /**
     * @param array  $params
     * @param string $baseString
     *
     * @return mixed
     */
    abstract function prepareParams($params = [], $baseString = '');

    /**
     * @param array $params
     * @param int   $version
     *
     * @return mixed
     */
    public final function calculate($params = [], $version = 0)
    {
        return $this->process($params, $version);
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
}
