<?php

declare(strict_types=1);

namespace Prismic\Migration\Model;

/**
 * Represents an available language
 *
 * phpcs:disable Squiz.NamingConventions.ValidVariableName
 *
 * @psalm-api
 */
final readonly class Language
{
    /**
     * @param non-empty-string $id
     * @param non-empty-string $name
     */
    public function __construct(
        public string $id,
        public string $name,
        public bool|null $is_master,
    ) {
    }
}
