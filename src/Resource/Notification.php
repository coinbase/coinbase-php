<?php

namespace Coinbase\Wallet\Resource;

use Coinbase\Wallet\Enum\ResourceType;

class Notification extends Resource
{
    /**
     * @var string
     * @see NotificationType
     */
    private $type;

    private $data;

    private $additionalData;

    /** @var User */
    private $user;

    /** @var Account */
    private $account;

    /** @var int */
    private $deliveryAttempts;

    private $deliveryResponse;

    /** @var \DateTime */
    private $createdAt;

    /** @var \DateTime */
    private $updatedAt;

    public function __construct($resourcePath = null)
    {
        parent::__construct(ResourceType::NOTIFICATION, $resourcePath);
    }

    public function getType()
    {
        return $this->type;
    }

    public function getData()
    {
        return $this->data;
    }

    public function getAdditionalData()
    {
        return $this->additionalData;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function getAccount()
    {
        return $this->account;
    }

    public function getDeliveryAttempts()
    {
        return $this->deliveryAttempts;
    }

    public function getDeliveryResponse()
    {
        return $this->deliveryResponse;
    }

    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }
}

