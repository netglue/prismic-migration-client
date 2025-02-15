<?php

declare(strict_types=1);

namespace Prismic\Migration;

use CuyZ\Valinor\Mapper\Source\JsonSource;
use CuyZ\Valinor\MapperBuilder;
use Fig\Http\Message\RequestMethodInterface;
use Prismic\Migration\Exception\CommunicationFailure;
use Prismic\Migration\Exception\RequestFailure;
use Prismic\Migration\Exception\UnexpectedResponse;
use Prismic\Migration\Model\MigrationDocument;
use Prismic\Migration\Model\MigrationDocumentPatch;
use Prismic\Migration\Model\MigrationResult;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use SensitiveParameter;
use Throwable;

use function array_filter;
use function json_encode;
use function ltrim;
use function sprintf;

use const JSON_THROW_ON_ERROR;

final readonly class MigrationClientImplementation implements MigrationClient
{
    /**
     * @param non-empty-string $token
     * @param non-empty-string $repository
     * @param non-empty-string $apiKey
     */
    public function __construct(
        #[SensitiveParameter]
        private string $token,
        private string $repository,
        private string $apiKey, // One of the known "Demo" Api Keys
        private ClientInterface $httpClient,
        private RequestFactoryInterface $requestFactory,
        private UriFactoryInterface $uriFactory,
        private StreamFactoryInterface $streamFactory,
        private string $baseUri = MigrationClient::DEFAULT_BASE_URI,
    ) {
    }

    public function createDocument(MigrationDocument $document): MigrationResult
    {
        $request = $this->createRequest(
            RequestMethodInterface::METHOD_POST,
            '/documents',
        )->withBody($this->streamFactory->createStream(
            json_encode($document, JSON_THROW_ON_ERROR),
        ));

        $response = $this->sendRequest($request);

        try {
            return (new MapperBuilder())
                ->mapper()
                ->map(
                    MigrationResult::class,
                    new JsonSource((string) $response->getBody()),
                );
        } catch (Throwable $e) {
            throw new UnexpectedResponse('Failed to parse document migration result', (int) $e->getCode(), $e);
        }
    }

    public function updateDocument(MigrationDocumentPatch $document): MigrationResult
    {
        $body = array_filter([
            'uid' => $document->uid,
            'tags' => $document->tags,
            'data' => $document->data,
            'title' => $document->title,
        ], static fn (mixed $value): bool => $value !== null);

        $request = $this->createRequest(
            RequestMethodInterface::METHOD_PUT,
            sprintf('/documents/%s', $document->id),
        )->withBody($this->streamFactory->createStream(
            json_encode($body, JSON_THROW_ON_ERROR),
        ));

        $response = $this->sendRequest($request);

        try {
            return (new MapperBuilder())
                ->mapper()
                ->map(
                    MigrationResult::class,
                    new JsonSource((string) $response->getBody()),
                );
        } catch (Throwable $e) {
            throw new UnexpectedResponse('Failed to parse document migration result', (int) $e->getCode(), $e);
        }
    }

    /**
     * @param non-empty-string $method
     * @param non-empty-string $path
     */
    private function createRequest(string $method, string $path): RequestInterface
    {
        $uri = $this->uriFactory->createUri(sprintf(
            '%s/%s',
            $this->baseUri,
            ltrim($path, '/'),
        ));

        return $this->requestFactory->createRequest($method, $uri)
            ->withHeader('Content-Type', 'application/json')
            ->withHeader('Authorization', sprintf('Bearer %s', $this->token))
            ->withHeader('repository', $this->repository)
            ->withHeader('x-api-key', $this->apiKey);
    }

    private function sendRequest(RequestInterface $request): ResponseInterface
    {
        try {
            $response = $this->httpClient->sendRequest($request);
        } catch (ClientExceptionInterface $error) {
            throw CommunicationFailure::fromPsrError($request, $error);
        }

        $status = $response->getStatusCode();
        if ($status >= 400) {
            throw RequestFailure::fromExchange($request, $response);
        }

        return $response;
    }
}
