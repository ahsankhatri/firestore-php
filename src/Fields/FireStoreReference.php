<?php

namespace MrShan0\PHPFirestore\Fields;

use MrShan0\PHPFirestore\Contracts\FireStoreDataTypeContract;
use MrShan0\PHPFirestore\FireStoreApiClient;
use MrShan0\PHPFirestore\Helpers\FireStoreHelper;

class FireStoreReference implements FireStoreDataTypeContract
{
    private $data;

    public function __construct($data='')
    {
        return $this->setData($data);
    }

    public function setData($data)
    {
        $this->data = FireStoreHelper::normalizeCollection($data);
    }

    public function getData()
    {
        return $this->data;
    }

    public function parseValue()
    {
        $value =
            'projects/' .
            FireStoreApiClient::getConfig('project') .
            '/databases/' .
            FireStoreApiClient::getConfig('database') .
            '/documents/' .
            $this->getData();

        return $value;
    }
}
