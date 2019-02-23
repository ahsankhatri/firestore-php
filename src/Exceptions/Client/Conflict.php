<?php

namespace MrShan0\PHPFirestore\Exceptions\Client;

class Conflict extends \Exception
{
    public function __construct($response)
    {
        $message = 'The request failed due to a resource conflict. This can occur if you attempt to to create a duplicate of an existing resource as well as when you attempt to delete a resource that is needed for the functioning of other resources. Response: '.$response;
        parent::__construct($message);
    }
}
