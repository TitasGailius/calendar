<?php

namespace TitasGailius\Calendar\Resources;

use TitasGailius\Calendar\Contracts\Paginator;

/**
 * @template TCollection of \TitasGailius\Calendar\Resources\Collection
 * @implements \TitasGailius\Calendar\Contracts\Paginator<TCollection>
 */
abstract class CollectionPaginator implements Paginator
{
    /**
     * Determine if the next page exists.
     */
    protected bool $hasNextPage = true;

    /**
     * Next page options.
     *
     * @var mixed[]
     */
    protected array $nextPageOptions = [];

    /**
     * Get next page.
     *
     * @return \TitasGailius\Calendar\Resources\Page<TCollection>
     */
    abstract protected function nextPage(array $options): Page;

    /**
     * Reset the paginator.
     *
     * @return \TitasGailius\Calendar\Resources\CollectionPaginator<TCollection>
     */
    abstract protected function reset(): CollectionPaginator;

    /**
     * {@inheritdoc}
     */
    public function next(): ?Page
    {
        if (! $this->hasNextPage) {
            return null;
        }

        $page = $this->nextPage($this->nextPageOptions);

        $this->hasNextPage = $page->hasNextPage;
        $this->nextPageOptions = $page->nextPageOptions;

        return $page;
    }

    /**
     * {@inheritdoc}
     */
    public function each(callable $callback): static
    {
        $paginator = $this->reset();

        while ($page = $paginator->next()) {
            $page->items->each($callback);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function all(): Collection
    {
        $paginator = $this->reset();

        $result = $paginator->next()->items;

        while ($page = $paginator->next()) {
            $result = $result->merge($page->items);
        }

        return $result;
    }
}
