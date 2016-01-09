<?php

namespace Coinbase\Wallet\Resource;

use Coinbase\Wallet\ActiveRecord\OrderActiveRecord;
use Coinbase\Wallet\Enum\OrderStatus;
use Coinbase\Wallet\Enum\OrderType;
use Coinbase\Wallet\Enum\ResourceType;
use Coinbase\Wallet\Value\Money;

class Order extends Resource
{
    use OrderActiveRecord;

    /** @var string */
    private $code;

    /**
     * @var string
     * @see OrderStatus
     */
    private $status;

    /**
     * @var string
     * @see OrderType
     */
    private $type;

    /** @var string */
    private $name;

    /** @var string */
    private $description;

    /** @var Money */
    private $amount;

    /** @var Money */
    private $payoutAmount;

    /** @var string */
    private $bitcoinAddress;

    /** @var Money */
    private $bitcoinAmount;

    /** @var string */
    private $notificationsUrl;

    /** @var string */
    private $bitcoinUri;

    /** @var string */
    private $receiptUrl;

    /** @var \DateTime */
    private $expiresAt;

    /** @var \DateTime */
    private $mispaidAt;

    /** @var \DateTime */
    private $paidAt;

    /** @var string */
    private $refundAddress;

    /** @var Transaction */
    private $transaction;

    /** @var array */
    private $refunds;

    /** @var array */
    private $mispayments;

    /** @var array */
    private $metadata;

    /** @var \DateTime */
    private $createdAt;

    /** @var \DateTime */
    private $updatedAt;

    /**
     * Creates an order reference.
     *
     * @param string $orderId The order id
     *
     * @return Order An order reference
     */
    public static function reference($orderId)
    {
        return new static('/v2/orders/'.$orderId);
    }

    public function __construct($resourcePath = null)
    {
        parent::__construct(ResourceType::ORDER, $resourcePath);
    }

    public function getCode()
    {
        return $this->code;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function getAmount()
    {
        return $this->amount;
    }

    public function setAmount($amount)
    {
        $this->amount = $amount;
    }

    public function getPayoutAmount()
    {
        return $this->payoutAmount;
    }

    public function getBitcoinAddress()
    {
        return $this->bitcoinAddress;
    }

    public function getBitcoinAmount()
    {
        return $this->bitcoinAmount;
    }

    public function getNotificationsUrl()
    {
        return $this->notificationsUrl;
    }

    public function setNotificationsUrl($notificationsUrl)
    {
        $this->notificationsUrl = $notificationsUrl;
    }

    public function getBitcoinUri()
    {
        return $this->bitcoinUri;
    }

    public function getReceiptUrl()
    {
        return $this->receiptUrl;
    }

    public function getExpiresAt()
    {
        return $this->expiresAt;
    }

    public function getMispaidAt()
    {
        return $this->mispaidAt;
    }

    public function getPaidAt()
    {
        return $this->paidAt;
    }

    public function getRefundAddress()
    {
        return $this->refundAddress;
    }

    public function getTransaction()
    {
        return $this->transaction;
    }

    public function getRefunds()
    {
        return $this->refunds;
    }

    public function getMispayments()
    {
        return $this->mispayments;
    }

    public function getMetadata()
    {
        return $this->metadata;
    }

    public function setMetadata(array $metadata)
    {
        $this->metadata = $metadata;
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
