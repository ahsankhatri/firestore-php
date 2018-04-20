<?php

namespace MrShan0\PHPFirestore\Fields;

use DateTime;
use MrShan0\PHPFirestore\Contracts\FireStoreDataTypeContract;

class FireStoreTimestamp implements FireStoreDataTypeContract
{
    const DEFAULT_FORMAT = 'Y-m-d\TG:i:s.z\Z';

    private $data;

    public function __construct($data='')
    {
        if ( $data === '' || $data === 'now' ) {
            $data = gmdate(self::DEFAULT_FORMAT);
        }

        return $this->setData($data);
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

        if ( $value instanceof DateTime && method_exists($value, 'format') ) {
            return $value->format(self::DEFAULT_FORMAT);
        }

        return $value;
    }
}
