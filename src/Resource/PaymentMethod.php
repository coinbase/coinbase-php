<?php

namespace Coinbase\Wallet\Resource;

use Coinbase\Wallet\ActiveRecord\PaymentMethodActiveRecord;
use Coinbase\Wallet\Enum\PaymentMethodType;
use Coinbase\Wallet\Enum\ResourceType;

class PaymentMethod extends Resource
{
    use PaymentMethodActiveRecord;

    /**
     * @var string
     * @see PaymentMethodType
     */
    private $type;

    /** @var string */
    private $name;

    /** @var string */
    private $currency;

    /** @var Boolean */
    private $primaryBuy;

    /** @var Boolean */
    private $primarySell;

    /** @var Boolean */
    private $allowBuy;

    /** @var Boolean */
    private $allowSell;

    /** @var \DateTime */
    private $createdAt;

    /** @var \DateTime */
    private $updatedAt;
    private $limits;

    /**
     * Creates a payment method reference.
     *
     * @param string $paymentMethodId The payment method id
     *
     * @return PaymentMethod A payment method reference
     */
    public static function reference($paymentMethodId)
    {
        return new static('/v2/payment-methods/'.$paymentMethodId);
    }

    public function __construct($resourcePath = null)
    {
        parent::__construct(ResourceType::PAYMENT_METHOD, $resourcePath);
    }

    public function getType()
    {
        return $this->type;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getCurrency()
    {
        return $this->currency;
    }

    public function isPrimaryBuy()
    {
        return $this->primaryBuy;
    }

    public function isPrimarySell()
    {
        return $this->primarySell;
    }

    public function isAllowBuy()
    {
        return $this->allowBuy;
    }

    public function isAllowSell()
    {
        return $this->allowSell;
    }

    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    public function getLimits()
    {
        return $this->limits;
    }
}
