<?php

namespace Coinbase\Wallet\Value;

class Error
{
    private $id;
    private $message;
    private $url;

    public function __construct($id, $message, $url = null)
    {
        $this->id = $id;
        $this->message = $message;
        $this->url = $url;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function __toString()
    {
        if ($this->url) {
            return sprintf('%s (%s)', $this->message, $this->url);
        } else {
            return (string) $this->message;
        }
    }
}
