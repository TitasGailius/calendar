<?php

namespace TitasGailius\Calendar\Resources;

use Closure;
use TitasGailius\Calendar\Resources\CollectionPaginator;

/**
 * @template TCollection of \TitasGailius\Calendar\Resources\Collection
 *
 * @extends \TitasGailius\Calendar\Resources\CollectionPaginator<TCollection>
 */
class GeneralCollectionPaginator extends CollectionPaginator
{
    /**
     * Instantiate a new paginator instance.
     *
     * @param  \Closure(mixed[]): \TitasGailius\Calendar\Resources\Page<TCollection>  $next
     * @param  \Closure(): \TitasGailius\Calendar\Resources\CollectionPaginator<TCollection>  $reset
     */
    public function __construct(
        protected readonly Closure $next,
        protected readonly Closure $reset,
    ) {}

    /**
     * {@inheritdoc}
     */
    protected function nextPage(array $options): Page
    {
        return ($this->next)($options);
    }

    /**
     * {@inheritdoc}
     */
    protected function reset(): CollectionPaginator
    {
        return ($this->reset)();
    }
}
