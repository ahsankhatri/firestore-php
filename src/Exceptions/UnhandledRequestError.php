<?php

namespace MrShan0\PHPFirestore\Exceptions;

class UnhandledRequestError extends \Exception
{
    public function __construct($code, $response)
    {
        $message = 'The request failed with the error: '.$code.'.  Response: '.$response;
        parent::__construct($message, $code);
    }
}
