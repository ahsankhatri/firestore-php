<?php

namespace MrShan0\PHPFirestore\Exceptions\Client;

class FieldTypeError extends \Exception
{
    public function __construct($response)
    {
        $message = 'Unexpected field type detected. Received type: '.$response;
        parent::__construct($message);
    }
}
