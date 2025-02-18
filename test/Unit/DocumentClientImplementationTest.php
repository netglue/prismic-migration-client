<?php

declare(strict_types=1);

namespace Prismic\Migration\Test\Unit;

use Http\Mock\Client;
use Laminas\Diactoros\RequestFactory;
use Laminas\Diactoros\Response;
use Laminas\Diactoros\ResponseFactory;
use Laminas\Diactoros\StreamFactory;
use Laminas\Diactoros\UriFactory;
use Override;
use PHPUnit\Framework\TestCase;
use Prismic\Migration\DocumentClientImplementation;
use Prismic\Migration\Exception\GenericRequestFailure;
use Prismic\Migration\Exception\RuntimeError;
use Psr\Http\Message\ResponseInterface;

use function file_get_contents;

final class DocumentClientImplementationTest extends TestCase
{
    private Client $http;
    private DocumentClientImplementation $client;

    #[Override]
    protected function setUp(): void
    {
        $this->http = new Client(new ResponseFactory());
        $this->client = new DocumentClientImplementation(
            'token',
            'repo',
            $this->http,
            new RequestFactory(),
            new UriFactory(),
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

    public function testFindByIdHappyPath(): void
    {
        $this->http->addResponse($this->fixtureResponse(__DIR__ . '/../fixtures/repository-data.json'));
        $this->http->addResponse($this->fixtureResponse(__DIR__ . '/../fixtures/result-set-1.json'));

        $document = $this->client->findById('foo');

        self::assertSame('document-1', $document->id);
        self::assertSame('page', $document->type);
    }

    public function testFindAllHappyPath(): void
    {
        $this->http->addResponse($this->fixtureResponse(__DIR__ . '/../fixtures/repository-data.json'));
        $this->http->addResponse($this->fixtureResponse(__DIR__ . '/../fixtures/result-set-1.json'));
        $this->http->addResponse($this->fixtureResponse(__DIR__ . '/../fixtures/result-set-2.json'));

        $list = $this->client->findAll();

        self::assertCount(2, $list);
        $a = $list[0];
        $b = $list[1];

        self::assertSame('document-1', $a->id);
        self::assertSame('document-2', $b->id);
    }

    public function testDocumentByIdNotFound(): void
    {
        $this->http->addResponse($this->fixtureResponse(__DIR__ . '/../fixtures/repository-data.json'));
        $this->http->addResponse($this->fixtureResponse(__DIR__ . '/../fixtures/empty-result-set.json'));

        $this->expectException(RuntimeError::class);
        $this->expectExceptionMessage('Document with id "foo" cannot be found');
        $this->client->findById('foo');
    }

    public function testNonSuccessResponseIsExceptional(): void
    {
        $response = $this->fixtureResponse(__DIR__ . '/../fixtures/repository-data.json')
            ->withStatus(400);

        $this->http->addResponse(
            $response,
        );

        try {
            $this->client->findById('foo');
            self::fail();
        } catch (GenericRequestFailure $error) {
            self::assertSame($response, $error->response);
            self::assertEquals(
                'https://repo.cdn.prismic.io/api/v2?access_token=token',
                (string) $error->request->getUri(),
            );
        }
    }
}
