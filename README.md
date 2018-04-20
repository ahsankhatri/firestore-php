# Firestore Client for PHP

[![Latest Version on Packagist](https://img.shields.io/packagist/v/ahsankhatri/firestore-php.svg?style=flat-square)](https://packagist.org/packages/ahsankhatri/firestore-php)
[![Total Downloads](https://img.shields.io/packagist/dt/ahsankhatri/firestore-php.svg?style=flat-square)](https://packagist.org/packages/ahsankhatri/firestore-php)
[![License](https://poser.pugx.org/ahsankhatri/firestore-php/license?format=flat-square)](https://packagist.org/packages/ahsankhatri/firestore-php)

This package is totally based on [Firestore REST API](https://firebase.google.com/docs/firestore/use-rest-api)

## Authentication / Generate API Key

1) Visit [Google Cloud Firestore API](https://console.cloud.google.com/projectselector/apis/api/firestore.googleapis.com/overview)  
2) Select your desired project.  
3) Select `Credentials` from left menu and select `API Key` from Server key or `Create your own credentials`  

## Installation

You can install the package via composer:

```bash
composer require ahsankhatri/firestore-php
```

## Dependencies

The bindings require the following extensions in order to work properly:

- [`curl`](https://secure.php.net/manual/en/book.curl.php)
- [`json`](https://secure.php.net/manual/en/book.json.php)

If you use Composer, these dependencies should be handled automatically. If you install manually, you'll want to make sure that these extensions are available.

## Usage

#### Initialization

```php
$firestoreClient = new FireStoreApiClient('project-id', 'AIzaxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx', [
    'database' => '(default)',
]);
```

#### Adding a document

```php
$firestoreClient->addDocument($collection, [
    'booleanTrue' => true,
    'booleanFalse' => false,
    'null' => null,
    'string' => 'abc123',
    'integer' => 123456,
    'arrayRaw' => [
        'string' => 'abc123',
    ],
    'array' => new FireStoreArray([
        'string' => 'abc123',
    ]),
    'reference' => new FireStoreReference('/users/23'),
    'object' => new FireStoreObject(['nested1' => new FireStoreObject(['nested2' => new FireStoreObject(['nested3' => 'test'])])]),
    'timestamp' => new FireStoreTimestamp,
    'geopoint' => new FireStoreGeoPoint(1,1),
]);
```

**NOTE:** Pass third argument if you want your custom _document id_ to set else auto-id will generate it for you.

Or

```php
$document = new FireStoreDocument;
$document->setObject('sdf', new FireStoreObject(['nested1' => new FireStoreObject(['nested2' => new FireStoreObject(['nested3' => 'test'])])]));
$document->setBoolean('booleanTrue', true);
$document->setBoolean('booleanFalse', false);
$document->setNull('null', null);
$document->setString('string', 'abc123');
$document->setInteger('integer', 123456);
$document->setArray('arrayRaw', ['string'=>'abc123']);
$document->setArray('arrayObject', new FireStoreArray(['string' => 'abc123']));
$document->setTimestamp('timestamp', new FireStoreTimestamp);
$document->setGeoPoint('geopoint', new FireStoreGeoPoint(1.11,1.11));
$firestoreClient->addDocument($collection, $document, 'customDocumentId');
```

And..

```php
$document->fillValues([
    'string' => 'abc123',
    'boolean' => true,
]);
```

#### Updating a document

- Update existing document

```php
$firestoreClient->updateDocument($collection, $documentId, [
    'newFieldToAdd' => new FireStoreTimestamp(new DateTime('2018-04-20 15:00:00')),
    'existingFieldToRemove' => new FireStoreDeleteAttribute
], true);
```

**NOTE:** Passing 3rd argument as a boolean _true_ will indicate that document must exist and vice-versa.

- Overwrite existing document

```php
$firestoreClient->setDocument($collection, $documentId, [
    'newFieldToAdd' => new FireStoreTimestamp(new DateTime('2018-04-20 15:00:00')),
    'existingFieldToRemove' => new FireStoreDeleteAttribute
], [
    'exists' => true, // Indicate document must exist
]);
```

#### Deleting a document

```php
$collection = 'collection/document/innerCollection';
$firestoreClient->deleteDocument($collection, $documentId);
```

### TODO
- [x] Added delete attribute support.
- [x] Add Support for Object, Boolean, Null, String, Integer, Array, Timestamp, GeoPoint
- [ ] Add Exception Handling.
- [ ] List all documents and collections.
- [ ] Filters and pagination support.
- [ ] Transaction support.
- [ ] Indexes support.
- [ ] Entire collection delete support.

### Testing

``` bash
composer test
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email ahsankhatri1992@gmail.com instead of using the issue tracker.

## Credits

- [Ahsaan Muhammad Yousuf](https://ahsaan.me)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
