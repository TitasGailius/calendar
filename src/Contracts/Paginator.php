<?php

namespace TitasGailius\Calendar\Contracts;

use TitasGailius\Calendar\Resources\Collection;
use TitasGailius\Calendar\Resources\Page;

/**
 * @template TCollection of \TitasGailius\Calendar\Resources\Collection
 */
interface Paginator
{
    /**
     * Get next page.
     *
     * @return Page<TCollection>|null
     */
    public function next(): ?Page;

    /**
     * Loop through each item.
     *
     * @param  callable  $callback
     * @return $this
     */
    public function each(callable $callback): static;

    /**
     * Collect items from all pages.
     *
     * @return TCollection
     */
    public function all(): Collection;
}
