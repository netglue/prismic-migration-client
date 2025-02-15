<?php

declare(strict_types=1);

namespace Prismic\Migration\Model;

/**
 * Represents a document search result set
 *
 * phpcs:disable Squiz.NamingConventions.ValidVariableName
 *
 * @psalm-api
 */
final readonly class DocumentResultSet
{
    /**
     * @param int<0, max>           $page
     * @param int<0, max>           $results_per_page
     * @param int<0, max>           $results_size
     * @param int<0, max>           $total_results_size
     * @param int<0, max>           $total_pages
     * @param non-empty-string|null $next_page
     * @param non-empty-string|null $prev_page
     * @param list<Document>        $results
     */
    public function __construct(
        public int $page,
        public int $results_per_page,
        public int $results_size,
        public int $total_results_size,
        public int $total_pages,
        public string|null $next_page,
        public string|null $prev_page,
        public array $results,
    ) {
    }
}
