<?php

namespace Coinbase\Wallet\Resource;

use Coinbase\Wallet\ActiveRecord\CheckoutActiveRecord;
use Coinbase\Wallet\Enum\CheckoutStyle;
use Coinbase\Wallet\Enum\OrderType;
use Coinbase\Wallet\Enum\ResourceType;
use Coinbase\Wallet\Value\Money;

class Checkout extends Resource
{
    use CheckoutActiveRecord;

    /** @var string */
    private $embedCode;

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

    /** @var string */
    private $text;

    /**
     * @var string
     * @see CheckoutStyle
     */
    private $style;

    /** @var Boolean */
    private $customerDefinedAmount;

    /** @var array */
    private $amountPresets;

    /** @var string */
    private $notificationsUrl;

    /** @var string */
    private $successUrl;

    /** @var string */
    private $cancelUrl;

    /** @var Boolean */
    private $autoRedirect;

    /** @var Boolean */
    private $collectShippingAddress;

    /** @var Boolean */
    private $collectPhoneNumber;

    /** @var Boolean */
    private $collectEmail;

    /** @var Boolean */
    private $collectCountry;

    /** @var array */
    private $metadata;

    /** @var \DateTime */
    private $createdAt;

    /** @var \DateTime */
    private $updatedAt;

    /**
     * Creates a checkout reference.
     *
     * @param string $checkoutId The checkout id
     *
     * @return Checkout A checkout reference
     */
    public static function reference($checkoutId)
    {
        return new static('/v2/checkouts/'.$checkoutId);
    }

    public function __construct($resourcePath = null)
    {
        parent::__construct(ResourceType::CHECKOUT, $resourcePath);
    }

    public function getEmbedCode()
    {
        return $this->embedCode;
    }

    public function getEmbedHtml()
    {
        if (empty($this->embedCode))
        {
            throw new LogicException(
                'The Checkout has not been created ($client->createCheckout($checkout)).'
            );
        }

        $code_attribute = "data-code=\"$this->embedCode\"";
        $text_attrbute = empty($this->text) ? "" : "data-button-text=\"$this->text\"";

        return "<div class=\"coinbase-button\" $code_attribute $text_attrbute></div><script src=\"https://www.coinbase.com/assets/button.js\" type=\"text/javascript\"></script>";
    }

    public function getType()
    {
        return $this->type;
    }

    public function setType($type)
    {
        $this->type = $type;
    }

    public function getText()
    {
        return $this->text;
    }

    public function setText($text)
    {
        $this->text = $text;
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

    public function setAmount(Money $amount)
    {
        $this->amount = $amount;
    }

    public function getStyle()
    {
        return $this->style;
    }

    public function setStyle($style)
    {
        $this->style = $style;
    }

    public function isCustomerDefinedAmount()
    {
        return $this->customerDefinedAmount;
    }

    public function setCustomerDefinedAmount($customerDefinedAmount)
    {
        $this->customerDefinedAmount = $customerDefinedAmount;
    }

    public function getAmountPresets()
    {
        return $this->amountPresets;
    }

    public function setAmountPresets(array $amountPresets)
    {
        $this->amountPresets = $amountPresets;
    }

    public function getNotificationsUrl()
    {
        return $this->notificationsUrl;
    }

    public function setNotificationsUrl($notificationsUrl)
    {
        $this->notificationsUrl = $notificationsUrl;
    }

    public function getSuccessUrl()
    {
        return $this->successUrl;
    }

    public function setSuccessUrl($successUrl)
    {
        $this->successUrl = $successUrl;
    }

    public function getCancelUrl()
    {
        return $this->cancelUrl;
    }

    public function setCancelUrl($cancelUrl)
    {
        $this->cancelUrl = $cancelUrl;
    }

    public function isAutoRedirect()
    {
        return $this->autoRedirect;
    }

    public function setAutoRedirect($autoRedirect)
    {
        $this->autoRedirect = $autoRedirect;
    }

    public function isCollectShippingAddress()
    {
        return $this->collectShippingAddress;
    }

    public function setCollectShippingAddress($collectShippingAddress)
    {
        $this->collectShippingAddress = $collectShippingAddress;
    }

    public function isCollectPhoneNumber()
    {
        return $this->collectPhoneNumber;
    }

    public function setCollectPhoneNumber($collectPhoneNumber)
    {
        $this->collectPhoneNumber = $collectPhoneNumber;
    }

    public function isCollectEmail()
    {
        return $this->collectEmail;
    }

    public function setCollectEmail($collectEmail)
    {
        $this->collectEmail = $collectEmail;
    }

    public function isCollectCountry()
    {
        return $this->collectCountry;
    }

    public function setCollectCountry($collectCountry)
    {
        $this->collectCountry = $collectCountry;
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
