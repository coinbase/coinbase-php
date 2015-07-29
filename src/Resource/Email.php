<?php

namespace Coinbase\Wallet\Resource;

use Coinbase\Wallet\Enum\ResourceType;

class Email extends Resource
{
    private $email;

    public function __construct($email)
    {
        parent::__construct(ResourceType::EMAIL);

        $this->email = $email;
    }

    public function getEmail()
    {
        return $this->email;
    }
}
