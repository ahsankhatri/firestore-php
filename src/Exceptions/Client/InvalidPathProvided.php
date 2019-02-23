<?php

namespace MrShan0\PHPFirestore\Exceptions\Client;

class InvalidPathProvided extends \Exception
{
    public function __construct($response)
    {
        $message = 'The path you have defined is invalid. Path: '.$response;
        parent::__construct($message);
    }
}
