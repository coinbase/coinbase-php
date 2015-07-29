<?php

namespace Coinbase\Wallet\Resource;

use Coinbase\Wallet\ActiveRecord\UserActiveRecord;
use Coinbase\Wallet\Enum\ResourceType;

class User extends Resource
{
    use UserActiveRecord;

    /** @var string */
    protected $name;

    /** @var string */
    private $username;

    /** @var string */
    private $profileLocation;

    /** @var string */
    private $profileBio;

    /** @var string */
    private $profileUrl;

    /** @var string */
    private $avatarUrl;

    /**
     * Creates a user reference.
     *
     * @param string $userId The user id
     *
     * @return User A user reference
     */
    public static function reference($userId)
    {
        return new static('/v2/users/'.$userId);
    }

    public function __construct($resourcePath = null)
    {
        parent::__construct(ResourceType::USER, $resourcePath);
    }

    public function getName()
    {
        return $this->name;
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function getProfileLocation()
    {
        return $this->profileLocation;
    }

    public function getProfileBio()
    {
        return $this->profileBio;
    }

    public function getProfileUrl()
    {
        return $this->profileUrl;
    }

    public function getAvatarUrl()
    {
        return $this->avatarUrl;
    }
}
