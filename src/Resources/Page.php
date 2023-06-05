<?php

namespace TitasGailius\Calendar\Resources;

/**
 * @template TCollection of \TitasGailius\Calendar\Resources\Collection
 */
class Page
{
    /**
     * Instantiate a new page instance.
     *
     * @param  TCollection  $items
     * @param  mixed[]  $nextPageOptions
     */
    public function __construct(
        public readonly mixed $raw,
        public readonly Collection $items,
        public readonly bool $hasNextPage,
        public readonly array $nextPageOptions,
    ) {}
}
