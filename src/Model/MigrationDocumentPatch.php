<?php

declare(strict_types=1);

namespace Prismic\Migration\Model;

/** @psalm-api */
final readonly class MigrationDocumentPatch
{
    /**
     * @param non-empty-string      $id
     * @param non-empty-string|null $uid   Required if already set on the existing document
     * @param array<string, mixed>  $data  The full document body must be included
     * @param list<string>          $tags  Tags must be provided, or existing tags will be removed
     * @param string|null           $title Updated if non-null
     */
    public function __construct(
        public string $id,
        public string|null $uid,
        public array $data,
        public array $tags,
        public string|null $title = null,
    ) {
    }
}
