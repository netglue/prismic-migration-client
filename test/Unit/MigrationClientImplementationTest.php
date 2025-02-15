<?php

declare(strict_types=1);

namespace Prismic\Migration\Test\Unit;

use Http\Mock\Client;
use Laminas\Diactoros\RequestFactory;
use Laminas\Diactoros\Response;
use Laminas\Diactoros\ResponseFactory;
use Laminas\Diactoros\StreamFactory;
use Laminas\Diactoros\UriFactory;
use PHPUnit\Framework\TestCase;
use Prismic\Migration\MigrationClientImplementation;
use Prismic\Migration\Model\MigrationDocument;
use Prismic\Migration\Model\MigrationDocumentPatch;
use Psr\Http\Message\ResponseInterface;

use function file_get_contents;

final class MigrationClientImplementationTest extends TestCase
{
    private Client $http;
    private MigrationClientImplementation $client;

    protected function setUp(): void
    {
        $this->http = new Client(new ResponseFactory());
        $this->client = new MigrationClientImplementation(
            'token',
            'repo',
            'api-key',
            $this->http,
            new RequestFactory(),
            new UriFactory(),
            new StreamFactory(),
        );
    }

    /** @param non-empty-string $filePath */
    private function fixtureResponse(string $filePath): ResponseInterface
    {
        $contents = file_get_contents($filePath);
        self::assertIsString($contents);

        return (new Response())->withBody(
            (new StreamFactory())->createStream($contents),
        );
    }

    public function testCreateDocumentHappyPath(): void
    {
        $this->http->addResponse($this->fixtureResponse(__DIR__ . '/../fixtures/migration-result.json'));

        $result = $this->client->createDocument(new MigrationDocument(
            'Title',
            'type',
            'uid',
            'en-GB',
            [],
        ));

        self::assertSame('some-id', $result->id);
    }

    public function testUpdateDocumentHappyPath(): void
    {
        $this->http->addResponse($this->fixtureResponse(__DIR__ . '/../fixtures/migration-result.json'));

        $result = $this->client->updateDocument(new MigrationDocumentPatch(
            'id',
            'type',
            [],
            ['tag'],
            null,
        ));

        self::assertSame('some-id', $result->id);
    }
}
