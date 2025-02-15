<?php

declare(strict_types=1);

namespace Prismic\Migration\Model;

/**
 * Represents the payload that should be sent when creating a new document in the remote migration API
 *
 * @psalm-api
 */
final readonly class MigrationDocument
{
    /**
     * @param non-empty-string|null $uid
     * @param non-empty-string      $type
     * @param non-empty-string      $lang
     * @param array<string, mixed>  $data
     */
    public function __construct(
        public string $title,
        public string $type,
        public string|null $uid,
        public string $lang,
        public array $data,
    ) {
    }

    public static function fromExisting(Document $document): self
    {
        return new self(
            '', // We have no way of knowing the document title,
            $document->type,
            $document->uid,
            $document->lang,
            $document->data,
        );
    }
}
