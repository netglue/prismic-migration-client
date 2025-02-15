<?php

declare(strict_types=1);

namespace Prismic\Migration\Model;

/** @psalm-api */
final readonly class Ref
{
    public bool $isMasterRef;

    /**
     * @param non-empty-string $id
     * @param non-empty-string $ref
     * @param non-empty-string $label
     */
    public function __construct(
        public string $id,
        public string $ref,
        public string $label,
        bool|null $isMasterRef,
    ) {
        $this->isMasterRef = $isMasterRef ?? false;
    }
}
