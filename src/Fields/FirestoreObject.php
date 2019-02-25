<?php

namespace MrShan0\PHPFirestore\Fields;

use MrShan0\PHPFirestore\Contracts\FirestoreDataTypeContract;
use MrShan0\PHPFirestore\FirestoreDocument;
use MrShan0\PHPFirestore\Helpers\FirestoreHelper;

class FirestoreObject implements FirestoreDataTypeContract
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
            $document = new FirestoreDocument;
            call_user_func_array([$document, 'set'.ucfirst(FirestoreHelper::getType($data))], ['firestore', $data]);
            $payload['fields'][$key] = $document->_getRawField('firestore');
        }

        return $payload;
    }
}
