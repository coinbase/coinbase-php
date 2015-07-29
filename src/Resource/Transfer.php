<?php

namespace Coinbase\Wallet\Resource;

use Coinbase\Wallet\Enum\CurrencyCode;
use Coinbase\Wallet\Value\Fee;
use Coinbase\Wallet\Value\Money;

abstract class Transfer extends Resource
{
    use AccountResource;

    /** @var string */
    private $status;

    /** @var PaymentMethod */
    private $paymentMethod;

    /** @var Transaction */
    private $transaction;

    /** @var Money */
    private $amount;

    /** @var Money */
    private $subtotal;

    /** @var \DateTime */
    private $createdAt;

    /** @var \DateTime */
    private $updatedAt;

    /** @var Boolean */
    private $committed;

    /** @var Fee[] */
    private $fees;

    /** @var \DateTime */
    private $payoutAt;

    public function getStatus()
    {
        return $this->status;
    }

    public function getPaymentMethod()
    {
        return $this->paymentMethod;
    }

    public function setPaymentMethod(PaymentMethod $paymentMethod)
    {
        $this->paymentMethod = $paymentMethod;
    }

    public function setPaymentMethodId($id)
    {
        $this->paymentMethod = PaymentMethod::reference($id);
    }

    public function getTransaction()
    {
        return $this->transaction;
    }

    public function getAmount()
    {
        return $this->amount;
    }

    public function setAmount(Money $amount)
    {
        $this->amount = $amount;
    }

    public function setBitcoinAmount($amount)
    {
        $this->amount = new Money($amount, CurrencyCode::BTC);
    }

    public function getSubtotal()
    {
        return $this->subtotal;
    }

    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    public function isCommitted()
    {
        return $this->committed;
    }

    public function getFees()
    {
        return $this->fees;
    }

    public function getPayoutAt()
    {
        return $this->payoutAt;
    }
}
