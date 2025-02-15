<?php

declare(strict_types=1);

namespace Prismic\Migration;

use Prismic\Migration\Model\Document;

interface DocumentClient
{
    public function findById(string $id): Document;

    /** @return list<Document> */
    public function findAll(): array;
}
