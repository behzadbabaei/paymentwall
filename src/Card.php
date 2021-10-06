<?php

declare(strict_types = 1);

namespace BehzadBabaei\PaymentWall;

class Card
{
    /**
     * @var array
     */
    protected array $fields = [];

    /**
     * @var mixed|null
     */
    protected $token;

    /**
     * @var mixed|null
     */
    protected $type;

    /**
     * @var mixed|null
     */
    protected $last4;

    /**
     * @var mixed|null
     */
    protected $exp_year;

    /**
     * Card constructor.
     *
     * @param array $details
     */
    public function __construct($details = [])
    {
        $this->fields = $details;
    }

    public function __get($property)
    {
        return isset($this->fields[$property]) ? $this->fields[$property] : null;
    }

    /**`
     * @return mixed|null
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @return mixed|null
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return mixed|null
     */
    public function getAlias()
    {
        return $this->last4;
    }

    /**
     * @return mixed|null
     */
    public function getMonthExpirationDate()
    {
        return $this->exp_month;
    }

    /**
     * @return mixed|null
     */
    public function getYearExpirationDate()
    {
        return $this->exp_year;
    }
}
