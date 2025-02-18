<?php

declare(strict_types=1);

namespace Prismic\Migration;

use Prismic\Migration\Exception\CommunicationFailure;
use Prismic\Migration\Exception\RequestFailure;
use Prismic\Migration\Exception\UnexpectedResponse;
use Prismic\Migration\Model\MigrationDocument;
use Prismic\Migration\Model\MigrationDocumentPatch;
use Prismic\Migration\Model\MigrationResult;

interface MigrationClient
{
    public const string DEFAULT_BASE_URI = 'https://migration.prismic.io';

    /**
     * Create a new Document with the given payload
     *
     * @throws CommunicationFailure If it is not possible to communicate with the API.
     * @throws RequestFailure If the response indicates any kind of failure status code.
     * @throws UnexpectedResponse If the API returns something we're not expecting or cannot parse.
     */
    public function createDocument(MigrationDocument $document): MigrationResult;

    /**
     * Update an existing Document with the given payload
     *
     * @throws CommunicationFailure If it is not possible to communicate with the API.
     * @throws RequestFailure If the response indicates any kind of failure status code.
     * @throws UnexpectedResponse If the API returns something we're not expecting or cannot parse.
     */
    public function updateDocument(MigrationDocumentPatch $document): MigrationResult;
}
