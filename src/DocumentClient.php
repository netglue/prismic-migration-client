<?php

declare(strict_types=1);

namespace Prismic\Migration;

use Prismic\Migration\Exception\CommunicationFailure;
use Prismic\Migration\Exception\RequestFailure;
use Prismic\Migration\Exception\UnexpectedResponse;
use Prismic\Migration\Model\Document;

interface DocumentClient
{
    /**
     * Fetch the document with the given id
     *
     * @param non-empty-string $id
     *
     * @throws CommunicationFailure If it is not possible to communicate with the API.
     * @throws RequestFailure If the response indicates any kind of failure status code.
     * @throws UnexpectedResponse If the API returns something we're not expecting or cannot parse.
     */
    public function findById(string $id): Document;

    /**
     * Fetch all available documents
     *
     * @return list<Document>
     *
     * @throws CommunicationFailure If it is not possible to communicate with the API.
     * @throws RequestFailure If the response indicates any kind of failure status code.
     * @throws UnexpectedResponse If the API returns something we're not expecting or cannot parse.
     */
    public function findAll(): array;
}
