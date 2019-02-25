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

or install it by adding it to `composer.json` then run `composer update`

```javascript
"require": {
    "ahsankhatri/firestore-php": "^2.0",
}
```

## Dependencies

The bindings require the following extensions in order to work properly:

- [`curl`](https://secure.php.net/manual/en/book.curl.php)
- [`json`](https://secure.php.net/manual/en/book.json.php)
- [`guzzlehttp/guzzle`](https://packagist.org/packages/guzzlehttp/guzzle)

If you use Composer, these dependencies should be handled automatically. If you install manually, you'll want to make sure that these extensions are available.

## Usage

#### Initialization

```php
$firestoreClient = new FirestoreClient('project-id', 'AIzaxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx', [
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
    'bytes' => new FirestoreBytes('bytesdata'),
    'array' => new FirestoreArray([
        'string' => 'abc123',
    ]),
    'reference' => new FirestoreReference('/users/23'),
    'object' => new FirestoreObject(['nested1' => new FirestoreObject(['nested2' => new FirestoreObject(['nested3' => 'test'])])]),
    'timestamp' => new FirestoreTimestamp,
    'geopoint' => new FirestoreGeoPoint(1,1),
]);
```

**NOTE:** Pass third argument if you want your custom **document id** to set else auto-id will generate it for you.

Or

```php
$document = new FirestoreDocument;
$document->setObject('sdf', new FirestoreObject(['nested1' => new FirestoreObject(['nested2' => new FirestoreObject(['nested3' => 'test'])])]));
$document->setBoolean('booleanTrue', true);
$document->setBoolean('booleanFalse', false);
$document->setNull('null', null);
$document->setString('string', 'abc123');
$document->setInteger('integer', 123456);
$document->setArray('arrayRaw', ['string'=>'abc123']);
$document->setBytes('bytes', new FirestoreBytes('bytesdata'));
$document->setArray('arrayObject', new FirestoreArray(['string' => 'abc123']));
$document->setTimestamp('timestamp', new FirestoreTimestamp);
$document->setGeoPoint('geopoint', new FirestoreGeoPoint(1.11,1.11));

$firestoreClient->addDocument($collection, $document, 'customDocumentId');
```

And..

```php
$document->fillValues([
    'string' => 'abc123',
    'boolean' => true,
]);
```

#### Inserting/Updating a document

- Update (Merge) or Insert document

Following will merge document (if exist) else insert the data.

```php
$firestoreClient->updateDocument($documentRoot, [
    'newFieldToAdd' => new FirestoreTimestamp(new DateTime('2018-04-20 15:00:00')),
    'existingFieldToRemove' => new FirestoreDeleteAttribute
]);
```

**NOTE:** Passing 3rd argument as a boolean _true_ will force check that document must exist and vice-versa in order to perform update operation.

For example: If you want to update document only if exist else `MrShan0\PHPFirestore\Exceptions\Client\NotFound` (Exception) will be thrown.

```php
$firestoreClient->updateDocument($documentPath, [
    'newFieldToAdd' => new FirestoreTimestamp(new DateTime('2018-04-20 15:00:00')),
    'existingFieldToRemove' => new FirestoreDeleteAttribute
], true);
```

- Overwirte or Insert document

```php
$firestoreClient->setDocument($collection, $documentId, [
    'newFieldToAdd' => new FirestoreTimestamp(new DateTime('2018-04-20 15:00:00')),
    'existingFieldToRemove' => new FirestoreDeleteAttribute
], [
    'exists' => true, // Indicate document must exist
]);
```

#### Deleting a document

```php
$collection = 'collection/document/innerCollection';
$firestoreClient->deleteDocument($collection, $documentId);
```

#### List documents with pagination (or custom parameters)

```php
$collections = $firestoreClient->listDocuments('users', [
    'pageSize' => 1,
    'pageToken' => 'nextpagetoken'
]);
```

**Note:** You can pass custom parameters as supported by [firestore list document](https://firebase.google.com/docs/firestore/reference/rest/v1/projects.databases.documents/list#query-parameters)

#### Get field from document

```php
$document->get('bytes')->parseValue(); // will return bytes decoded value.

// Catch field that doesn't exist in document
try {
    $document->get('allowed_notification');
} catch (\MrShan0\PHPFirestore\Exceptions\Client\FieldNotFound $e) {
    // Set default value
}
```

### Firebase Authentication

#### Sign in with Email and Password.

```php
$firestoreClient
    ->authenticator()
    ->signInEmailPassword('testuser@example.com', 'abc123');
```

#### Sign in Anonymously.

```php
$firestoreClient
    ->authenticator()
    ->signInAnonymously();
```

### Retrieve Auth Token

```php
$authToken = $firestoreClient->authenticator()->getAuthToken();
```

### TODO
- [x] Added delete attribute support.
- [x] Add Support for Object, Boolean, Null, String, Integer, Array, Timestamp, GeoPoint, Bytes
- [x] Add Exception Handling.
- [x] List all documents.
- [ ] List all collections.
- [x] Filters and pagination support.
- [ ] Structured Query support.
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
