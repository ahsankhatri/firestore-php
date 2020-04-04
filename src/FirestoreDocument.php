<?php

namespace MrShan0\PHPFirestore;

use DateTime;
use Exception;
use MrShan0\PHPFirestore\Attributes\FirestoreDeleteAttribute;
use MrShan0\PHPFirestore\Exceptions\Client\FieldNotFound;
use MrShan0\PHPFirestore\Exceptions\Client\FieldTypeError;
use MrShan0\PHPFirestore\Fields\FirestoreArray;
use MrShan0\PHPFirestore\Fields\FirestoreBytes;
use MrShan0\PHPFirestore\Fields\FirestoreGeoPoint;
use MrShan0\PHPFirestore\Fields\FirestoreObject;
use MrShan0\PHPFirestore\Fields\FirestoreReference;
use MrShan0\PHPFirestore\Fields\FirestoreTimestamp;
use MrShan0\PHPFirestore\Helpers\FirestoreHelper;

class FirestoreDocument {

    private $fields           = [];
    private $name             = null;
    private $createTime       = null;
    private $updateTime       = null;

    /**
     * Hold DatabaseResource Object
     *
     * @var \MrShan0\PHPFirestore\FirestoreDatabaseResource
     */
    private $databaseResource = null;

    /**
     * @throws \InvalidArgumentException
     */
    public function __construct($object = null, $databaseResource = null)
    {
        if (null !== $object) {
            $this->name       = $object['name'];
            $this->createTime = isset($object['createTime']) ? $object['createTime'] : null;
            $this->updateTime = isset($object['updateTime']) ? $object['updateTime'] : null;

            if (isset($object['fields'])) {
                foreach ($object['fields'] as $fieldName => $value) {
                    $this->fields[ $fieldName ] = $value;
                }
            }
        }

        if (null !== $databaseResource) {
            if (!$databaseResource instanceof FirestoreDatabaseResource) {
                throw new InvalidArgumentException('Instance passed must be of FirestoreDatabaseResource');
            }

            $this->databaseResource = $databaseResource;
        }
    }

    /**
     * @return string
     */
    public function getRelativeName()
    {
        $rootPath = FirestoreClient::getRelativeDatabasePath() . '/documents';

        return substr($this->getAbsoluteName(), strlen($rootPath));
    }

    /**
     * @return string
     */
    public function getAbsoluteName()
    {
        return $this->name;
    }

    /**
     * @return DateTime
     */
    public function getCreatedTime()
    {
        if (is_null($this->createTime)) {
            return null;
        }

        return new DateTime($this->createTime);
    }

    /**
     * @return DateTime
     */
    public function getUpdatedTime()
    {
        if (is_null($this->updateTime)) {
            return null;
        }

        return new DateTime($this->updateTime);
    }

    /**
     * To fill values into Document object appropriately.
     *
     * @param array $payload
     *
     * @return \MrShan0\PHPFirestore\FirestoreDocument
     */
    public function fillValues(array $payload)
    {
        foreach ($payload as $key => $value) {
            call_user_func_array([$this, 'set'.ucfirst(FirestoreHelper::getType($value))], [$key, $value]);
        }

        return $this;
    }

    /**
     * To determine is valid document when fetched from firestore.
     * Won't work with `new FirestoreDocument`
     *
     * @param  object
     *
     * @return boolean
     */
    public static function isValidDocument($object)
    {
        return ( array_key_exists('name', $object) && array_key_exists('fields', $object) );
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return void
     */
    public function setString($fieldName, $value)
    {
        $this->fields[$fieldName] = [
            'stringValue' => $value
        ];
    }

    /**
     * @param array $value
     *
     * @return string
     */
    public function getString($value)
    {
        return strval($value['stringValue']);
    }

    /**
     * @return void
     */
    public function setDouble($fieldName, $value)
    {
        $this->fields[$fieldName] = [
            'doubleValue' => floatval($value)
        ];
    }

    /**
     * @param array $value
     *
     * @return float
     */
    public function getDouble($value)
    {
        return floatval($value['doubleValue']);
    }

    /**
     * @return void
     */
    public function setArray($fieldName, $value)
    {
        if ( !$value instanceof FirestoreArray ) {
            $payload = new FirestoreArray;
            foreach ($value as $row) {
                $payload->add( $row );
            }

            $value = $payload;
        }

        $this->fields[$fieldName] = [
            'arrayValue' => $value->parseValue()
        ];
    }

    /**
     * @param array $value
     *
     * @return array
     */
    public function getArray($value)
    {
        $results = [];

        if (isset($value['arrayValue']['values']) && is_array($value['arrayValue']['values'])) {
            foreach ($value['arrayValue']['values'] as $key => $value) {
                $results[$key] = $this->castValue($value);
            }
        }

        return $results;
    }

    /**
     * @return void
     */
    public function setObject($fieldName, $value)
    {
        if ( !$value instanceof FirestoreObject ) {
            $payload = new FirestoreObject;
            foreach ($value as $row) {
                $payload->add( $row );
            }

            $value = $payload;
        }

        $this->fields[$fieldName] = [
            'mapValue' => $value->parseValue()
        ];
    }

    /**
     * @param array $value
     *
     * @return \MrShan0\PHPFirestore\Fields\FirestoreObject
     */
    public function getObject($value)
    {
        $results = [];

        foreach ($value['mapValue']['fields'] as $key => $value) {
            $results[$key] = $this->castValue($value);
        }

        return new FirestoreObject([$results]);
    }

    /**
     * @return void
     */
    public function setReference($fieldName, FirestoreReference $value)
    {
        $this->fields[$fieldName] = [
            'referenceValue' => $value->parseValue()
        ];
    }

    /**
     * @param array $value
     *
     * @return \MrShan0\PHPFirestore\Fields\FirestoreReference
     */
    public function getReference($value)
    {
        return new FirestoreReference( substr($value['referenceValue'], strpos($value['referenceValue'], '/documents/')+10), $this->databaseResource );
    }

    /**
     * @return void
     */
    public function setGeoPoint($fieldName, FirestoreGeoPoint $value)
    {
        $this->fields[$fieldName] = [
            'geoPointValue' => $value->parseValue()
        ];
    }

    /**
     * @param array $value
     *
     * @return \MrShan0\PHPFirestore\Fields\FirestoreGeoPoint
     */
    public function getGeoPoint($value)
    {
        return new FirestoreGeoPoint($value['geoPointValue']['latitude'], $value['geoPointValue']['longitude']);
    }

    /**
     * @return void
     */
    public function setTimestamp($fieldName, FirestoreTimestamp $value) {
        $this->fields[$fieldName] = [
            'timestampValue' => $value->parseValue()
        ];
    }

    /**
     * @return \MrShan0\PHPFirestore\Fields\FirestoreTimestamp
     */
    public function getTimestamp($value)
    {
        return new FirestoreTimestamp($value['timestampValue']);
    }

    /**
     * @return void
     */
    public function setBoolean($fieldName, $value)
    {
        $this->fields[$fieldName] = [
            'booleanValue' => !!$value
        ];
    }

    /**
     * @return boolean
     */
    public function getBoolean($value)
    {
        return (bool) $value['booleanValue'];
    }

    /**
     * @return void
     */
    public function setInteger($fieldName, $value)
    {
        $this->fields[$fieldName] = [
            'integerValue' => intval($value)
        ];
    }

    /**
     * @return integer
     */
    public function getInteger($value)
    {
        return intval($value['integerValue']);
    }

    /**
     * @return void
     */
    public function setNull($fieldName)
    {
        $this->fields[$fieldName] = [
            'nullValue' => null,
        ];
    }

    /**
     * @return null
     */
    public function getNull($value)
    {
        return null;
    }

    /**
     * @return void
     */
    public function setBytes($fieldName, FirestoreBytes $value)
    {
        $this->fields[$fieldName] = [
            'bytesValue' => FirestoreHelper::base64encode($value->getData()),
        ];
    }

    /**
     * @param string $value
     *
     * @return \MrShan0\PHPFirestore\Fields\FirestoreBytes
     */
    public function getBytes($value)
    {
        return new FirestoreBytes($value['bytesValue']);
    }

    /**
     * A placeholder to delete particular keys from database
     *
     * @param string $fieldName
     *
     * @return string
     */
    public function setDelete($fieldName)
    {
        $this->fields[$fieldName] = new FirestoreDeleteAttribute;
    }

    /**
     * Delete keys in bulk
     *
     * @param  string|array
     *
     * @return \MrShan0\PHPFirestore\FirestoreDocument
     */
    public function deleteFields($fields)
    {
        is_array($fields) || $fields = [$fields];

        foreach ($fields as $key) {
            $this->setDelete($key);
        }

        return $this;
    }

    /**
     * It will return value that Firestore needs to store.
     *
     * @throws \MrShan0\PHPFirestore\Exceptions\Client\FieldNotFound
     *
     * @param string $fieldName
     * @return mixed
     */
    public function _getRawField($fieldName)
    {
        if (array_key_exists($fieldName, $this->fields)) {
            return reset($this->fields);
        }

        throw new FieldNotFound($fieldName);
    }

    /**
     * Check whether document has such field or not.
     *
     * @param  string $fieldName
     * @return boolean
     */
    public function has($fieldName)
    {
        return (array_key_exists($fieldName, $this->fields) && !$this->fields[$fieldName] instanceof FirestoreDeleteAttribute);
    }

    /**
     * Extract value from object by key
     *
     * @throws \MrShan0\PHPFirestore\Exceptions\Client\FieldNotFound
     *
     * @param string $fieldName
     * @return mixed
     */
    public function get($fieldName)
    {
        if ($this->has($fieldName)) {
            $result = $this->castValue($this->fields[$fieldName]);

            return $result;
        }

        throw new FieldNotFound($fieldName);
    }

    /**
     * @return string
     */
    public function toJson()
    {
        return FirestoreHelper::encode([
            'fields' => (object) FirestoreHelper::filter($this->fields)
        ]);
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $results = [];

        foreach ($this->fields as $key => $value)
        {
            $results[ $key ] = $this->castValue($value);
        }

        return $results;
    }

    /**
     * This is our DataMapper, which shapes data into appropriate form
     *
     * @param array $value
     * @return mixed
     */
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
        } else if ( array_key_exists('bytesValue', $value ) ) {
            $parsedValue = $this->getBytes($value);
        }

        return $parsedValue;
    }

}
