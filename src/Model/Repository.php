<?php

declare(strict_types=1);

namespace Prismic\Migration\Model;

use RuntimeException;

/** @psalm-api */
final readonly class Repository
{
    /**
     * @param list<Ref>      $refs
     * @param list<Language> $languages
     */
    public function __construct(
        public array $refs,
        public array $languages,
    ) {
    }

    /** @return non-empty-string */
    public function masterRef(): string
    {
        foreach ($this->refs as $ref) {
            if ($ref->isMasterRef) {
                return $ref->ref;
            }
        }

        throw new RuntimeException('A Master ref cannot be found');
    }
}
