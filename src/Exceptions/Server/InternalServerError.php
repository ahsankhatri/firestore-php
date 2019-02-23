<?php

namespace MrShan0\PHPFirestore\Exceptions\Server;

class InternalServerError extends \Exception
{
    public function __construct($response)
    {
        $message = 'Something went wrong with the Firestore API. Response: '.$response;
        parent::__construct($message);
    }
}
