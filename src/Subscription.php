<?php

declare(strict_types = 1);

namespace BehzadBabaei\PaymentWall;

class Subscription extends ApiObject
{
    /**
     * @var mixed|null
     */
    protected $is_trial;

    /**
     * @var mixed|null
     */
    protected $active;

    /**
     * @var mixed|null
     */
    protected $object;

    /**
     * @var mixed|null
     */
    protected $expired;

    /**
     * @var mixed|null
     */
    protected $id;

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
    public function isTrial()
    {
        return $this->is_trial;
    }

    /**
     * @return mixed|null
     */
    public function isActive()
    {
        return $this->active;
    }

    /**
     * @return bool
     */
    public function isSuccessful() : bool
    {
        return $this->object == self::API_OBJECT_SUBSCRIPTION;
    }

    /**
     * @return mixed|null
     */
    public function isExpired()
    {
        return $this->expired;
    }

    /**
     * @return mixed|string
     */
    public function getEndpointName()
    {
        return self::API_OBJECT_SUBSCRIPTION;
    }

    /**
     * @return mixed|null
     * @throws \Exception
     */
    public function get()
    {
        return $this->doApiAction('', 'get');
    }

    /**
     * @return mixed|null
     * @throws \Exception
     */
    public function cancel()
    {
        return $this->doApiAction('cancel');
    }
}
