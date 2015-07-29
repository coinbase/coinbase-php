<?php

namespace Coinbase\Wallet\Resource;

class Resource
{
    /** @var string */
    private $id;

    /** @var string */
    private $resource;

    /** @var string */
    private $resourcePath;

    /** @var array */
    private $rawData;

    public function __construct($resourceType, $resourcePath = null, $id = null)
    {
        $this->resource = $resourceType;
        $this->resourcePath = $resourcePath;
        $this->id = $id;

        // extract id from resource path
        if (!$id && $resourcePath) {
            $parts = explode('/', $resourcePath);
            $this->id = array_pop($parts);
        }
    }

    public function getId()
    {
        return $this->id;
    }

    public function getResourceType()
    {
        return $this->resource;
    }

    public function getResourcePath()
    {
        return $this->resourcePath;
    }

    public function getRawData()
    {
        return $this->rawData;
    }

    public function isExpanded()
    {
        return (Boolean) $this->rawData;
    }
}
