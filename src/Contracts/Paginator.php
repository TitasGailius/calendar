<?php

namespace TitasGailius\Calendar\Contracts;

/**
 * @template TValue
 */
interface Paginator
{
    /**
     * Get next page.
     *
     * @return  TValue[]|null
     */
    public function next(): ?array;

    /**
     * Loop through each item.
     *
     * @param  callable(TValue): mixed  $callback
     * @return $this
     */
    public function each(callable $callback): static;

    /**
     * Collect items from all pages.
     *
     * @return TValue[]
     */
    public function all(): array;
}
