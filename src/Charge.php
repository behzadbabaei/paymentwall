<?php

declare(strict_types = 1);

namespace BehzadBabaei\PaymentWall;

class Charge extends ApiObject implements ApiObjectInterface
{
    protected $id;
    protected $test;
    protected $object;
    protected $captured;
    protected $risk;
    protected $refunded;

    /**
     * @return mixed|null
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed|null
     */
    public function isTest()
    {
        return $this->test;
    }

    /**
     * @return bool
     */
    public function isSuccessful()
    {
        return $this->object == self::API_OBJECT_CHARGE;
    }

    /**
     * @return mixed|null
     */
    public function isCaptured()
    {
        return $this->captured;
    }

    /**
     * @return bool
     */
    public function isUnderReview()
    {
        return $this->risk == 'pending';
    }

    /**
     * @return mixed|null
     */
    public function isRefunded()
    {
        return $this->refunded;
    }

    /**
     * @param string $response
     *
     * @throws \Exception
     */
    public function setPropertiesFromResponse($response = '')
    {
        parent::setPropertiesFromResponse($response);
        $this->card = new Card($this->card);
    }

    /**
     * @return mixed|string
     */
    public function getEndpointName()
    {
        return self::API_OBJECT_CHARGE;
    }

    /**
     * @return mixed|\BehzadBabaei\PaymentWall\Card
     */
    public function getCard()
    {
        return new Card($this->card);
    }

    /**
     * @return \BehzadBabaei\PaymentWall\Charge
     * @throws \Exception
     */
    public function get()
    {
        return $this->doApiAction('', 'get');
    }

    /**
     * @return \BehzadBabaei\PaymentWall\Charge
     * @throws \Exception
     */
    public function refund()
    {
        return $this->doApiAction('refund');
    }

    /**
     * @return \BehzadBabaei\PaymentWall\Charge
     * @throws \Exception
     */
    public function capture()
    {
        return $this->doApiAction('capture');
    }

    /**
     * @return \BehzadBabaei\PaymentWall\Charge
     * @throws \Exception
     */
    public function void()
    {
        return $this->doApiAction('void');
    }
}
