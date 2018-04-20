<?php

namespace MrShan0\PHPFirestore\Helpers;

use MrShan0\PHPFirestore\Attributes\FireStoreDeleteAttribute;
use MrShan0\PHPFirestore\Fields\FireStoreArray;
use MrShan0\PHPFirestore\Fields\FireStoreGeoPoint;
use MrShan0\PHPFirestore\Fields\FireStoreReference;
use MrShan0\PHPFirestore\Fields\FireStoreTimestamp;

class FireStoreHelper
{
    /**
     * Decode payload to object
     *
     * @param  string
     * @return object
     */
    public static function decode($value)
    {
        return json_decode($value, true, JSON_FORCE_OBJECT);
    }

    /**
     * Encode payload to post on firestore.
     *
     * @param  object
     * @return string
     */
    public static function encode($value)
    {
        return json_encode($value);
    }

    /**
     * Remove heading slash for collection
     *
     * @param  string
     * @return string
     */
    public static function normalizeCollection($value)
    {
        return ltrim($value, '/');
    }

    /**
     * Filter will filter out those values which is not needed to send to server
     *
     * @param  array $value
     * @return array
     */
    public static function filter($value)
    {
        return array_filter($value, function($v) {
            return in_array(self::getType($v), ['delete']) ? false : true;
        });
    }

    /**
     * Decides which class to call when field matched.
     *
     * @param  string $value
     * @return string
     */
    public static function getType($value)
    {
        $type = gettype($value);

        if ( $type === 'object' ) {
            if ( $value instanceof FireStoreReference ) {
                return 'reference';
            }

            if ( $value instanceof FireStoreTimestamp ) {
                return 'timestamp';
            }

            if ( $value instanceof FireStoreArray ) {
                return 'array';
            }

            if ( $value instanceof FireStoreGeoPoint ) {
                return 'geoPoint';
            }

            if ( $value instanceof FireStoreDeleteAttribute ) {
                return 'delete';
            }
        }

        return $type;
    }

}
