<?php

declare(strict_types=1);

use Http\Client\Curl\Client;
use Laminas\Diactoros\RequestFactory;
use Laminas\Diactoros\ResponseFactory;
use Laminas\Diactoros\StreamFactory;
use Laminas\Diactoros\UriFactory;
use Prismic\Migration\DocumentClientImplementation;

require __DIR__ . '/../vendor/autoload.php';

$token = getenv('PRISMIC_REPO_TOKEN');
$token = $token === '' ? null : $token;
assert(is_string($token) || $token === null);

$repo = getenv('PRISMIC_REPO');
assert(is_string($repo) && $repo !== '');

$client = new DocumentClientImplementation(
    $token,
    $repo,
    new Client(
        new ResponseFactory(),
        new StreamFactory(),
    ),
    new RequestFactory(),
    new UriFactory(),
);

$documents = $client->findAll();

foreach ($documents as $document) {
    printf(
        '- Document: %s of type %s%s',
        $document->id,
        $document->type,
        PHP_EOL,
    );
}

printf("\nTotal Document Count: %d\n", count($documents));

shuffle($documents);

$randomDoc = $documents[0];

$result = $client->findById($randomDoc->id);

printf("\nRandom document %s fetched\n", $result->id);
