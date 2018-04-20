<?php

namespace MrShan0\PHPFirestore\Fields;

use MrShan0\PHPFirestore\Contracts\FireStoreDataTypeContract;
use MrShan0\PHPFirestore\FireStoreDocument;
use MrShan0\PHPFirestore\Helpers\FireStoreHelper;

class FireStoreObject implements FireStoreDataTypeContract
{
    private $data = [];

    public function __construct($data='')
    {
        if ( !empty($data) ) {
            return $this->setData((array) $data);
        }
    }

    public function add($data)
    {
        array_push($this->data, $data);

        return $this;
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
        $payload = [
            'fields' => [],
        ];

        foreach ($this->data as $key => $data) {
            $document = new FireStoreDocument;
            call_user_func_array([$document, 'set'.ucfirst(FireStoreHelper::getType($data))], ['string', $data]);
            $payload['fields'][$key] = $document->get('string');
        }

        return $payload;
    }
}
