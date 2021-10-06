<?php

declare(strict_types = 1);

namespace BehzadBabaei\PaymentWall;

use function round;

class Product
{
    const TYPE_SUBSCRIPTION = 'subscription';
    const TYPE_FIXED = 'fixed';

    const PERIOD_TYPE_DAY = 'day';
    const PERIOD_TYPE_WEEK = 'week';
    const PERIOD_TYPE_MONTH = 'month';
    const PERIOD_TYPE_YEAR = 'year';

    /**
     * Product constructor.
     *
     * @param                                       $productId
     * @param float                                 $amount
     * @param null                                  $currencyCode
     * @param null                                  $name
     * @param string                                $productType
     * @param int                                   $periodLength
     * @param null                                  $periodType
     * @param false                                 $recurring
     * @param \BehzadBabaei\PaymentWall\Product|null             $trialProduct
     */
    public function __construct($productId,
                                $amount = 0.0,
                                $currencyCode = null,
                                $name = null,
                                $productType = self::TYPE_FIXED,
                                $periodLength = 0,
                                $periodType = null,
                                $recurring = false,
                                Product $trialProduct = null
    ) {
        $this->productId = $productId;
        $this->amount = round($amount, 2);
        $this->currencyCode = $currencyCode;
        $this->name = $name;
        $this->productType = $productType;
        $this->periodLength = $periodLength;
        $this->periodType = $periodType;
        $this->recurring = $recurring;
        $this->trialProduct = ($productType == Product::TYPE_SUBSCRIPTION && $recurring) ? $trialProduct : null;
    }

    /**
     * @return mixed|null
     */
    public function getId()
    {
        return $this->productId;
    }

    /**
     * @return mixed|null
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @return mixed|null
     */
    public function getCurrencyCode()
    {
        return $this->currencyCode;
    }

    /**
     * @return mixed|null
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return null|string
     */
    public function getType()
    {
        return $this->productType;
    }

    /**
     * @return mixed|null
     */
    public function getPeriodType()
    {
        return $this->periodType;
    }

    /**
     * @return mixed|null
     */
    public function getPeriodLength()
    {
        return $this->periodLength;
    }

    /**
     * @return mixed|null
     */
    public function isRecurring()
    {
        return $this->recurring;
    }

    /**
     * @return mixed|null
     */
    public function getTrialProduct()
    {
        return $this->trialProduct;
    }
}
