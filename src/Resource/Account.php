<?php

namespace Coinbase\Wallet\Resource;

use Coinbase\Wallet\ActiveRecord\AccountActiveRecord;
use Coinbase\Wallet\Enum\AccountType;
use Coinbase\Wallet\Enum\ResourceType;
use Coinbase\Wallet\Value\Money;

class Account extends Resource
{
    use AccountActiveRecord;

    /** @var string */
    private $name;

    /** @var Boolean */
    private $primary;

    /**
     * @var string
     * @see AccountType
     */
    private $type;

    /** @var string */
    private $currency;

    /** @var Money */
    private $balance;

    /** @var Money */
    private $nativeBalance;

    /** @var \DateTime */
    private $createdAt;

    /** @var \DateTime */
    private $updatedAt;

    /**
     * Creates an account reference.
     *
     * @param string $accountId The account id
     *
     * @return Account An account reference
     */
    public static function reference($accountId)
    {
        return new static('/v2/accounts/'.$accountId);
    }

    public function __construct($resourcePath = null)
    {
        parent::__construct(ResourceType::ACCOUNT, $resourcePath);
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function isPrimary()
    {
        return $this->primary;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getCurrency()
    {
        return $this->currency;
    }

    public function getBalance()
    {
        return $this->balance;
    }

    public function getNativeBalance()
    {
        return $this->nativeBalance;
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
