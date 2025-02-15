<?php

declare(strict_types=1);

namespace Prismic\Migration;

use Prismic\Migration\Model\MigrationDocument;
use Prismic\Migration\Model\MigrationDocumentPatch;
use Prismic\Migration\Model\MigrationResult;

interface MigrationClient
{
    public const string DEFAULT_BASE_URI = 'https://migration.prismic.io';

    public function createDocument(MigrationDocument $document): MigrationResult;

    public function updateDocument(MigrationDocumentPatch $document): MigrationResult;
}
