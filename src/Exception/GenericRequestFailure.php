<?php

declare(strict_types=1);

namespace Prismic\Migration\Exception;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;
use Throwable;

class GenericRequestFailure extends RuntimeException implements RequestFailure
{
    final public function __construct(
        public readonly RequestInterface $request,
        public readonly ResponseInterface $response,
        string $message,
        int $code,
        Throwable|null $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }
}
