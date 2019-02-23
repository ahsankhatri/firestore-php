<?php

namespace MrShan0\PHPFirestore\Exceptions\Client;

class NotFound extends \Exception
{
    public function __construct($response)
    {
        $message = 'The requested resource could not be found. Response: '.$response;
        parent::__construct($message);
    }
}
