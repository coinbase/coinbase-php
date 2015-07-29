<?php

namespace Coinbase\Wallet\Resource;

use Coinbase\Wallet\ActiveRecord\MerchantActiveRecord;
use Coinbase\Wallet\Enum\ResourceType;

class Merchant extends Resource
{
    use MerchantActiveRecord;

    /** @var string */
    private $name;

    /** @var string */
    private $websiteUrl;

    /** @var array */
    private $address;

    /** @var string */
    private $avatarUrl;

    /** @var string */
    private $logoUrl;

    /** @var string */
    private $coverImageUrl;

    /**
     * Creates a merchant reference.
     *
     * @param string $merchantId The merchant id
     *
     * @return Merchant A merchant reference
     */
    public static function reference($merchantId)
    {
        return new static('/v2/merchants/'.$merchantId);
    }

    public function __construct($resourcePath = null)
    {
        parent::__construct(ResourceType::MERCHANT, $resourcePath);
    }

    public function getName()
    {
        return $this->name;
    }

    public function getWebsiteUrl()
    {
        return $this->websiteUrl;
    }

    public function getAddress()
    {
        return $this->address;
    }

    public function getAvatarUrl()
    {
        return $this->avatarUrl;
    }

    public function getLogoUrl()
    {
        return $this->logoUrl;
    }

    public function getCoverImageUrl()
    {
        return $this->coverImageUrl;
    }
}
