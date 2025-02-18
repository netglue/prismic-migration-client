<?php

declare(strict_types=1);

namespace Prismic\Migration\Exception;

use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

use function json_decode;
use function json_encode;
use function json_validate;
use function sprintf;
use function str_contains;

use const JSON_THROW_ON_ERROR;
use const PHP_EOL;

final readonly class RequestFailureFactory
{
    public static function fromExchange(
        RequestInterface $request,
        ResponseInterface $response,
    ): RequestFailure {
        $stream = $response->getBody();
        if ($stream->isSeekable()) {
            $stream->rewind();
        }

        $body = $stream->getContents();

        if (
            $response->getStatusCode() === StatusCodeInterface::STATUS_NOT_FOUND
            &&
            str_contains($body, 'Assets with following ids not found')
        ) {
            $message = sprintf(
                'Document processing failed due to missing assets: %s',
                $body,
            );

            return new AssetNotFound(
                $request,
                $response,
                $message,
                $response->getStatusCode(),
            );
        }

        $responseBody = $body;

        if (json_validate($body)) {
            $responseBody = json_encode(
                json_decode($body, true, JSON_THROW_ON_ERROR),
                JSON_THROW_ON_ERROR,
            );
        }

        if ($response->getStatusCode() === StatusCodeInterface::STATUS_TOO_MANY_REQUESTS) {
            return new RateLimitExceeded(
                $request,
                $response,
                sprintf(
                    'Rate limit exceeded: %s',
                    $responseBody,
                ),
                $response->getStatusCode(),
            );
        }

        return new GenericRequestFailure(
            $request,
            $response,
            sprintf(
                'The request to %s failed with a %d error:%s%s',
                (string) $request->getUri(),
                $response->getStatusCode(),
                PHP_EOL,
                $responseBody,
            ),
            $response->getStatusCode(),
        );
    }
}
