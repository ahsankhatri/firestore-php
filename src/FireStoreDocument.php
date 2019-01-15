<?php

namespace MrShan0\PHPFirestore;

use Exception;
use MrShan0\PHPFirestore\Attributes\FireStoreDeleteAttribute;
use MrShan0\PHPFirestore\Fields\FireStoreArray;
use MrShan0\PHPFirestore\Fields\FireStoreGeoPoint;
use MrShan0\PHPFirestore\Fields\FireStoreObject;
use MrShan0\PHPFirestore\Fields\FireStoreReference;
use MrShan0\PHPFirestore\Fields\FireStoreTimestamp;
use MrShan0\PHPFirestore\Helpers\FireStoreHelper;

class FireStoreDocument {

    private $fields     = [];
    private $name       = null;
    private $createTime = null;
    private $updateTime = null;

    public function __construct($object = null) {

        if (null !== $object) {
            $this->name       = $object['name'];
            $this->createTime = $object['createTime'];
            $this->updateTime = $object['updateTime'];

            foreach ($object['fields'] as $fieldName => $value) {
                $this->fields[ $fieldName ] = $value;
            }
        }
    }

    public function fillValues(array $payload)
    {
        foreach ($payload as $key => $value) {
            call_user_func_array([$this, 'set'.ucfirst(FireStoreHelper::getType($value))], [$key, $value]);
        }

        return $this;
    }

    /**
     * To determine is valid document when fetched from firestore.
     * Won't work wiht `new FireStoreDocument`
     *
     * @param  object
     * @return boolean
     */
    public static function isValidDocument($object)
    {
        return ( array_key_exists('name', $object) && array_key_exists('fields', $object) );
    }

    public function getName() {
        return $this->name;
    }

    public function setName($value) {
        return $this->name = $value;
    }

    public function setString($fieldName, $value) {
        $this->fields[$fieldName] = [
            'stringValue' => $value
        ];
    }

    public function getString($value)
    {
        return strval($value['stringValue']);
    }

    public function setDouble($fieldName, $value) {
        $this->fields[$fieldName] = [
            'doubleValue' => floatval($value)
        ];
    }

    public function getDouble($value) {
        return floatval($value['doubleValue']);
    }

    public function setArray($fieldName, $value) {

        if ( !$value instanceof FireStoreArray ) {
            $payload = new FireStoreArray;
            foreach ($value as $row) {
                $payload->add( $row );
            }

            $value = $payload;
        }

        $this->fields[$fieldName] = [
            'arrayValue' => $value->parseValue()
        ];
    }

    public function getArray($value)
    {
        $results = [];

        foreach ($value['arrayValue']['values'] as $key => $value) {
            $results[$key] = $this->castValue($value);
        }

        return $results;
    }

    public function setObject($fieldName, $value) {

        if ( !$value instanceof FireStoreObject ) {
            $payload = new FireStoreObject;
            foreach ($value as $row) {
                $payload->add( $row );
            }

            $value = $payload;
        }

        $this->fields[$fieldName] = [
            'mapValue' => $value->parseValue()
        ];
    }

    public function getObject($value) {

        $results = [];

        foreach ($value['mapValue']['fields'] as $key => $value) {
            $results[$key] = $this->castValue($value);
        }

        return new FireStoreObject([$results]);
    }

    public function setReference($fieldName, FireStoreReference $value) {
        $this->fields[$fieldName] = [
            'referenceValue' => $value->parseValue()
        ];
    }

    public function getReference($value)
    {
        return new FireStoreReference( substr($value['referenceValue'], strpos($value['referenceValue'], '/documents/')+10) );
    }

    public function setGeoPoint($fieldName, FireStoreGeoPoint $value) {
        $this->fields[$fieldName] = [
            'geoPointValue' => $value->parseValue()
        ];
    }

    public function getGeoPoint($value)
    {
        return new FireStoreGeoPoint($value['geoPointValue']['latitude'], $value['geoPointValue']['longitude']);
    }

    public function setTimestamp($fieldName, FireStoreTimestamp $value) {
        $this->fields[$fieldName] = [
            'timestampValue' => $value->parseValue()
        ];
    }

    public function getTimestamp($value)
    {
        return new FireStoreTimestamp($value['timestampValue']);
    }

    public function setBoolean($fieldName, $value) {
        $this->fields[$fieldName] = [
            'booleanValue' => !!$value
        ];
    }

    public function getBoolean($value) {
        return (bool) $value['booleanValue'];
    }

    public function setInteger($fieldName, $value) {
        $this->fields[$fieldName] = [
            'integerValue' => intval($value)
        ];
    }

    public function getInteger($value) {
        return intval($value['integerValue']);
    }

    public function setNull($fieldName) {
        $this->fields[$fieldName] = [
            'nullValue' => null,
        ];
    }

    public function getNull($value) {
        return null;
    }

    public function setDelete($fieldName, FireStoreDeleteAttribute $value) {
        $this->fields[$fieldName] = $value;
    }

    /**
     * A placeholder to delete particular keys from database
     *
     * @param  string|array
     * @return \MrShan0\PHPFirestore\FireStoreDocument
     */
    public function deleteFields($fields)
    {
        is_array($fields) || $fields = [$fields];
        foreach ($fields as $key) {
            $this->setDelete($key, new FireStoreDeleteAttribute);
        }

        return $this;
    }

    public function get($fieldName) {
        if (array_key_exists($fieldName, $this->fields)) {
            return reset($this->fields);
        }
        throw new Exception('No such field');
    }

    public function toJson() {
        return FireStoreHelper::encode([
            'fields' => (object) FireStoreHelper::filter($this->fields)
        ]);
    }

    public function toArray() {
        $results = [];

        foreach ($this->fields as $key => $value) {
            $results[ $key ] = $this->castValue($value);
        }

        return $results;
    }

    private function castValue($value)
    {
        $parsedValue = '';

        if ( array_key_exists('stringValue', $value ) ) {
            $parsedValue = $this->getString($value);
        } else if ( array_key_exists('arrayValue', $value ) ) {
            $parsedValue = $this->getArray($value);
        } else if ( array_key_exists('integerValue', $value ) ) {
            $parsedValue = $this->getInteger($value);
        } else if ( array_key_exists('doubleValue', $value ) ) {
            $parsedValue = $this->getDouble($value);
        } else if ( array_key_exists('booleanValue', $value ) ) {
            $parsedValue = $this->getBoolean($value);
        } else if ( array_key_exists('nullValue', $value ) ) {
            $parsedValue = $this->getNull($value);
        } else if ( array_key_exists('mapValue', $value ) ) {
            $parsedValue = $this->getObject($value);
        } else if ( array_key_exists('referenceValue', $value ) ) {
            $parsedValue = $this->getReference($value);
        } else if ( array_key_exists('geoPointValue', $value ) ) {
            $parsedValue = $this->getGeoPoint($value);
        } else if ( array_key_exists('timestampValue', $value ) ) {
            $parsedValue = $this->getTimestamp($value);
        }

        return $parsedValue;
    }

}
