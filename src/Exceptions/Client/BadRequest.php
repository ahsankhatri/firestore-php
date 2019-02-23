<?php

namespace MrShan0\PHPFirestore\Exceptions\Client;

class BadRequest extends \Exception
{
    public function __construct($response)
    {
        $message = 'The request failed. This is most commonly the result of failing to include all required fields or failing validation on the object. Response: '.$response;
        parent::__construct($message);
    }
}
