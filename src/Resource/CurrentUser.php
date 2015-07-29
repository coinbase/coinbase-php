<?php

namespace Coinbase\Wallet\Resource;

use Coinbase\Wallet\ActiveRecord\CurrentUserActiveRecord;

class CurrentUser extends User
{
    use CurrentUserActiveRecord;

    /** @var string */
    private $timeZone;

    /** @var string */
    private $nativeCurrency;

    public function __construct()
    {
        // disallow setting the resource path
        parent::__construct();
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function setTimeZone($timeZone)
    {
        $this->timeZone = $timeZone;
    }

    public function setNativeCurrency($nativeCurrency)
    {
        $this->nativeCurrency = $nativeCurrency;
    }
}
