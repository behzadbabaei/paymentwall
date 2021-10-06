<?php

declare(strict_types = 1);

namespace BehzadBabaei\PaymentWall;

use BehzadBabaei\PaymentWall\Signature\SignatureAbstract;
use BehzadBabaei\PaymentWall\Signature\SignatureWidget;

use function count;
use function current;
use function intval;
use function array_filter;
use function array_merge;
use function http_build_query;
use function preg_match;

class Widget extends PaymentwallInstance
{
    const CONTROLLER_PAYMENT_VIRTUAL_CURRENCY = 'ps';
    const CONTROLLER_PAYMENT_DIGITAL_GOODS = 'subscription';
    const CONTROLLER_PAYMENT_CART = 'cart';

    protected $userId;

    /**
     * @var string
     */
    protected string $widgetCode;

    /**
     * @var array
     */
    protected array $products;

    /**
     * @var array
     */
    protected array $extraParams;

    /**
     * Widget constructor.
     *
     * @param        $userId
     * @param string $widgetCode
     * @param array  $products
     * @param array  $extraParams
     */
    public function __construct($userId, $widgetCode = '', $products = [], $extraParams = [])
    {
        $this->userId = $userId;
        $this->widgetCode = $widgetCode;
        $this->products = $products;
        $this->extraParams = $extraParams;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        $params = [
            'key'    => $this->getPublicKey(),
            'uid'    => $this->userId,
            'widget' => $this->widgetCode
        ];

        $productsNumber = count($this->products);

        if ($this->getApiType() == Config::API_GOODS) {

            if (!empty($this->products)) {

                if ($productsNumber == 1) {

                    $product = current($this->products);

                    if ($product->getTrialProduct() instanceof Product) {
                        $postTrialProduct = $product;
                        $product = $product->getTrialProduct();
                    }

                    $params['amount'] = $product->getAmount();
                    $params['currencyCode'] = $product->getCurrencyCode();
                    $params['ag_name'] = $product->getName();
                    $params['ag_external_id'] = $product->getId();
                    $params['ag_type'] = $product->getType();

                    if ($product->getType() == Product::TYPE_SUBSCRIPTION) {
                        $params['ag_period_length'] = $product->getPeriodLength();
                        $params['ag_period_type'] = $product->getPeriodType();
                        if ($product->isRecurring()) {

                            $params['ag_recurring'] = intval($product->isRecurring());

                            if (isset($postTrialProduct)) {
                                $params['ag_trial'] = 1;
                                $params['ag_post_trial_external_id'] = $postTrialProduct->getId();
                                $params['ag_post_trial_period_length'] = $postTrialProduct->getPeriodLength();
                                $params['ag_post_trial_period_type'] = $postTrialProduct->getPeriodType();
                                $params['ag_post_trial_name'] = $postTrialProduct->getName();
                                $params['post_trial_amount'] = $postTrialProduct->getAmount();
                                $params['post_trial_currencyCode'] = $postTrialProduct->getCurrencyCode();
                            }

                        }
                    }

                } else {
                    //TODO: $this->appendToErrors('Only 1 product is allowed in flexible widget call');
                }

            }

        } else if ($this->getApiType() == Config::API_CART) {

            $external_ids = [];
            $prices = [];
            $currencies = [];
            $names = [];

            foreach ($this->products as $product) {
                $external_ids[] = $product->getId();
                $prices[] = $product->amount ? : 0;
                $currencies[] = $product->currencyCode ? : '';
                $names[] = $product->name ? : '';
            }

            $params['external_ids'] = $external_ids;

            if (!empty($prices)) {
                $params['prices'] = $prices;
            }

            if (!empty($currencies)) {
                $params['currencies'] = $currencies;
            }

            if (array_filter($names)) {
                $params['names'] = $names;
            }
        }

        $params['sign_version'] = $signatureVersion = $this->getDefaultSignatureVersion();

        if (!empty($this->extraParams['sign_version'])) {
            $signatureVersion = $params['sign_version'] = $this->extraParams['sign_version'];
        }

        $params = array_merge($params, $this->extraParams);

        $widgetSignatureModel = new SignatureWidget();
        $params['sign'] = $widgetSignatureModel->calculate(
            $params,
            $signatureVersion
        );

        return $this->getApiBaseUrl().'/'.$this->buildController($this->widgetCode).'?'.http_build_query($params);
    }

    /**
     * @param array $attributes
     *
     * @return string
     */
    public function getHtmlCode($attributes = array())
    {
        $defaultAttributes = [
            'frameborder' => '0',
            'width'       => '750',
            'height'      => '800'
        ];

        $attributes = array_merge($defaultAttributes, $attributes);

        $attributesQuery = '';
        foreach ($attributes as $attr => $value) {
            $attributesQuery .= ' '.$attr.'="'.$value.'"';
        }

        return '<iframe src="'.$this->getUrl().'" '.$attributesQuery.'></iframe>';

    }

    /**
     * @return int
     */
    protected function getDefaultSignatureVersion()
    {
        return $this->getApiType() != Config::API_CART ? SignatureAbstract::DEFAULT_VERSION : SignatureAbstract::VERSION_TWO;
    }

    /**
     * @param string $widget
     *
     * @return string|null
     */
    protected function buildController($widget = '')
    {
        $controller = null;
        $isPaymentWidget = !preg_match('/^w|s|mw/', $widget);

        if ($this->getApiType() == Config::API_VC) {
            if ($isPaymentWidget) {
                $controller = self::CONTROLLER_PAYMENT_VIRTUAL_CURRENCY;
            }
        } else if ($this->getApiType() == Config::API_GOODS) {
            /**
             * @todo cover case with offer widget for digital goods for non-flexible widget call
             */
            $controller = self::CONTROLLER_PAYMENT_DIGITAL_GOODS;
        } else {
            $controller = self::CONTROLLER_PAYMENT_CART;
        }

        return $controller;
    }
}
