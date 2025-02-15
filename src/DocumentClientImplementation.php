<?php

declare(strict_types=1);

namespace Prismic\Migration;

use CuyZ\Valinor\Mapper\Source\JsonSource;
use CuyZ\Valinor\MapperBuilder;
use Fig\Http\Message\RequestMethodInterface;
use Prismic\Migration\Exception\CommunicationFailure;
use Prismic\Migration\Exception\RequestFailure;
use Prismic\Migration\Exception\RuntimeError;
use Prismic\Migration\Exception\UnexpectedResponse;
use Prismic\Migration\Model\Document;
use Prismic\Migration\Model\DocumentResultSet;
use Prismic\Migration\Model\Repository;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriFactoryInterface;
use SensitiveParameter;
use Throwable;

use function array_merge;
use function count;
use function http_build_query;
use function ltrim;
use function rtrim;
use function sprintf;

/**
 * Simplified Document API Client
 *
 * phpcs:disable Squiz.NamingConventions.ValidVariableName
 */
final class DocumentClientImplementation implements DocumentClient
{
    private const int MAX_PAGE_SIZE = 100;

    /** @var non-empty-string */
    private readonly string $baseUri;

    private Repository|null $repositoryData = null;

    /**
     * @param non-empty-string|null $token
     * @param non-empty-string      $repository
     */
    public function __construct(
        #[SensitiveParameter]
        private readonly string|null $token,
        string $repository,
        private readonly ClientInterface $httpClient,
        private readonly RequestFactoryInterface $requestFactory,
        private readonly UriFactoryInterface $uriFactory,
    ) {
        $this->baseUri = sprintf('https://%s.cdn.prismic.io/api/v2', $repository);
    }

    private function repository(): Repository
    {
        if ($this->repositoryData !== null) {
            return $this->repositoryData;
        }

        $request = $this->createRequest(RequestMethodInterface::METHOD_GET, '');
        $response = $this->sendRequest($request);
        $body = (string) $response->getBody();

        try {
            $this->repositoryData = (new MapperBuilder())
                ->allowPermissiveTypes()
                ->allowSuperfluousKeys()
                ->enableFlexibleCasting()
                ->mapper()
                ->map(Repository::class, new JsonSource($body));
        } catch (Throwable $e) {
            throw new UnexpectedResponse('Failed to decode API payload', (int) $e->getCode(), $e);
        }

        return $this->repositoryData;
    }

    /** @return non-empty-string */
    private function masterRef(): string
    {
        return $this->repository()->masterRef();
    }

    public function findById(string $id): Document
    {
        $resultSet = $this->documentQuery(sprintf('[[at(document.id, "%s")]]', $id));
        if (count($resultSet->results) === 0) {
            throw new RuntimeError(sprintf('Document with id "%s" cannot be found', $id));
        }

        return $resultSet->results[0];
    }

    /** @inheritDoc */
    public function findAll(): array
    {
        $first = $this->documentQuery();
        $next = $first->page + 1;
        $results = $first->results;

        while ($next <= $first->total_pages) {
            $resultSet = $this->documentQuery(null, $next);
            $results = array_merge($results, $resultSet->results);
            $next++;
        }

        return $results;
    }

    /** @param non-empty-string|null $query */
    private function documentQuery(string|null $query = null, int $page = 1): DocumentResultSet
    {
        $parameters = [
            'ref' => $this->masterRef(),
            'pageSize' => self::MAX_PAGE_SIZE,
            'page' => $page,
        ];
        if ($query !== null) {
            $parameters['q'] = $query;
        }

        $request = $this->createRequest(RequestMethodInterface::METHOD_GET, '/documents/search', $parameters);

        $response = $this->sendRequest($request);
        $body = (string) $response->getBody();

        try {
            return (new MapperBuilder())
                ->enableFlexibleCasting()
                ->allowSuperfluousKeys()
                ->allowPermissiveTypes()
                ->mapper()
                ->map(DocumentResultSet::class, new JsonSource($body));
        } catch (Throwable $e) {
            throw new UnexpectedResponse('Failed to parse document result set', (int) $e->getCode(), $e);
        }
    }

    /**
     * @param non-empty-string      $method
     * @param array<string, scalar> $query
     */
    private function createRequest(string $method, string $path, array $query = []): RequestInterface
    {
        $uri = $this->uriFactory->createUri(rtrim(sprintf(
            '%s/%s',
            $this->baseUri,
            ltrim($path, '/'),
        ), '/'));

        if ($this->token !== null) {
            $query['access_token'] = $this->token;
        }

        if ($query !== []) {
            $uri = $uri->withQuery(http_build_query($query));
        }

        return $this->requestFactory->createRequest($method, $uri);
    }

    private function sendRequest(RequestInterface $request): ResponseInterface
    {
        try {
            $response = $this->httpClient->sendRequest($request);
        } catch (ClientExceptionInterface $error) {
            throw CommunicationFailure::fromPsrError($request, $error);
        }

        if ($response->getStatusCode() >= 400) {
            throw RequestFailure::fromExchange($request, $response);
        }

        return $response;
    }
}
