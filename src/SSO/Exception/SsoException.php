<?php

namespace SSO\Exception;

class SsoException extends \Exception
{

    private $messages;

    /**
     * SsoException constructor.
     */
    public function __construct($message = "")
    {
        $message = 'Keycloak error: ' . $message;
        parent::__construct($message);
    }


}