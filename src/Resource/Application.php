<?php

namespace Coinbase\Wallet\Resource;

use Coinbase\Wallet\Enum\ResourceType;

class Application extends Resource
{
    /** @var string */
    private $description;

    /** @var string */
    private $name;

    /** @var string */
    private $imageUrl;

    public function __construct($resourcePath = null)
    {
        parent::__construct(ResourceType::APPLICATION, $resourcePath);
    }

    public function getName()
    {
        return $this->name;
    }

    public function getImageUrl()
    {
        return $this->imageUrl;
    }

    public function getDescription()
    {
        return $this->description;
    }
}
