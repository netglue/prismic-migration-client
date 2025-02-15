<?php

declare(strict_types=1);

namespace Prismic\Migration\Model;

/** @psalm-api */
final readonly class MigrationResult
{
    /**
     * @param non-empty-string $id
     * @param non-empty-string $type
     * @param non-empty-string $lang
     */
    public function __construct(
        public string $id,
        public string $type,
        public string $lang,
        public string $title,
    ) {
    }
}
