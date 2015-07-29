<?php

namespace Coinbase\Wallet\Resource;

use Coinbase\Wallet\ActiveRecord\AddressActiveRecord;
use Coinbase\Wallet\Enum\ResourceType;

class Address extends Resource
{
    use AccountResource;
    use AddressActiveRecord;

    /** @var string */
    private $address;

    /** @var string */
    private $name;

    /** @var string */
    private $callbackUrl;

    /** @var \DateTime */
    private $createdAt;

    /** @var \DateTime */
    private $updatedAt;

    /**
     * Creates an address reference.
     *
     * @param string $accountId The account id
     * @param string $addressId The address id
     *
     * @return Address An address reference
     */
    public static function reference($accountId, $addressId)
    {
        $resourcePath = sprintf('/v2/accounts/%s/addresses/%s', $accountId, $addressId);

        return new static($resourcePath);
    }

    public function __construct($resourcePath = null)
    {
        parent::__construct(ResourceType::ADDRESS, $resourcePath);
    }

    public function getAddress()
    {
        return $this->address;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getCallbackUrl()
    {
        return $this->callbackUrl;
    }

    public function setCallbackUrl($callbackUrl)
    {
        $this->callbackUrl = $callbackUrl;
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
