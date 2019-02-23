<?php

namespace MrShan0\PHPFirestore\Fields;

use MrShan0\PHPFirestore\Contracts\FirestoreDataTypeContract;
use MrShan0\PHPFirestore\Helpers\FirestoreHelper;

class FirestoreBytes implements FirestoreDataTypeContract
{
    private $data;

    public function __construct($string='')
    {
        if (!is_string($string)) {
            throw new \InvalidArgumentException('Argument given must be string.');
        }

        return $this->setData($string);
    }

    public function setData($data)
    {
        return $this->data = $data;
    }

    public function getData()
    {
        return $this->data;
    }

    public function parseValue()
    {
        $value = $this->getData();

        return FirestoreHelper::base64decode($value);
    }
}
