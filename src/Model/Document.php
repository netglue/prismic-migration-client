<?php

declare(strict_types=1);

namespace Prismic\Migration\Model;

/**
 * Document model for an existing document
 *
 * phpcs:disable Squiz.NamingConventions.ValidVariableName
 *
 * @psalm-api
 */
final readonly class Document
{
    /**
     * @param non-empty-string       $id
     * @param non-empty-string|null  $uid
     * @param non-empty-string       $type
     * @param non-empty-string       $lang
     * @param list<non-empty-string> $tags
     * @param array<string, mixed>   $data
     */
    public function __construct(
        public string $id,
        public string|null $uid,
        public string $type,
        public string $lang,
        public array $tags,
        public array $data,
    ) {
    }
}
