# Prismic Migration API PHP Client

This is a client for Prismic.io's Migration API.

You can find documentation on the API at [prismic.io/docs/migration-api-technical-reference](https://prismic.io/docs/migration-api-technical-reference)

## Install

```bash
composer require netglue/prismic-migration-client
```

## Usage

You'll need to construct the concrete client yourself with all of its required dependencies:

- A [PSR-18](https://www.php-fig.org/psr/psr-18/) HTTP Client such as [php-http/curl-client](https://github.com/php-http/curl-client)
- [PSR-7](https://www.php-fig.org/psr/psr-7/) implementations with [PSR-17](https://www.php-fig.org/psr/psr-17/) Stream Factory, Request Factory and Uri Factory such as [laminas/laminas-diactoros](https://github.com/laminas/laminas-diactoros/)

You will need a write api token for the target repo and one of the "Demo API Keys" whatever the fuck they are. You can find them here: https://prismic.io/docs/migration-api-technical-reference#limits

Assuming you have a PSR-11 container set up, you might be able to do something like this in a factory:

```php
use Prismic\Migration\MigrationClientImplementation;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;

$client = new MigrationClientImplementation(
    'some-write-token',
    'some-repo-name',
    'some-demo-api-key',
    $container->get(ClientInterface::class),
    $container->get(RequestFactoryInterface::class),
    $container->get(UriFactoryInterface::class),
    $container->get(StreamFactoryInterface::class),
);
```

There's also a client shipped for the document read api which is a simplified client designed for fetching the source documents with minimum of fuss and in an easily serializable state.

```php

use Prismic\Migration\DocumentClientImplementation;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;

$client = new DocumentClientImplementation(
    'some-read-token-or-null', // Token can be null here if your read api doesn't require one
    'repo-name',
    $container->get(ClientInterface::class),
    $container->get(RequestFactoryInterface::class),
    $container->get(UriFactoryInterface::class),
);

```

## Basic Functionality

### Create Document

Create a document using the shipped value object `MigrationDocument`:

```php
use Prismic\Migration\DocumentClient;
use Prismic\Migration\MigrationClient;
use Prismic\Migration\Model\MigrationDocument;

$readClient = $container->get(DocumentClient::class);
assert($readClient instanceof DocumentClient);

$migrationClient = $container->get(MigrationClient::class);
assert($migrationClient instanceof MigrationClient);

$document = $readClient->findById('some-document-id');

$migrationDoc = new MigrationDocument(
    'Make up a title or figure it out from content',
    $document->type,
    $document->uid,
    $document->lang,
    $document->data,
);

$result = $migrationClient->createDocument($migrationDoc);
```

### Update Already Migrated Document

```php
use Prismic\Migration\MigrationClient;
use Prismic\Migration\Model\Document;
use Prismic\Migration\Model\MigrationDocumentPatch;
use Prismic\Migration\Model\MigrationResult;

assert($migrationClient instanceof MigrationClient);
assert($document instanceof Document);
assert($result instanceof MigrationResult);

$patch = new MigrationDocumentPatch(
    $result->id,
    $document->uid, // No idea if this can be mutated or not
    $document->data, // The entire payload of the source document is required
    $document->tags, // You must repeat existing tags or they will be removed
    'Updated Title', // Or null to leave it alone
);

$result = $migrationClient->updateDocument($patch);
```

Patching docs seems pointless given its design - you're better off sending the data you want first time with `createDocument`.

### Document Read API Client

The read-api client has two methods:

- `findById(non-empty-string): Document`
- `findAll(): list<Document>`

It will use the current `master` ref, so you'll need to ensure that anything you want to migrate is published already.

## Contributing

Please feel free to get involved with development. The project uses PHPUnit for tests, and [Psalm](https://psalm.dev) and [PHPStan](https://phpstan.org) for static analysis. CI should have your back if you want to submit a feature or fix ;)

## License

[MIT Licensed](LICENSE.md).
