<?php

namespace TitasGailius\Calendar\Resources;

use TitasGailius\Calendar\Contracts\Paginator as PaginatorContract;
use Closure;

/**
 * @template TValue
 * @template CValue
 * @implements \TitasGailius\Calendar\Contracts\Paginator<TValue>
 */
class Paginator implements PaginatorContract
{
    /**
     * Current page.
     *
     * @var CValue
     */
    protected $current;

    /**
     * Instantiate a new new paginator instance.
     *
     * @param  \Closure(?CValue): CValue  $next
     * @param  \Closure(CValue): TValue[]  $mapper
     */
    public function __construct(
        protected readonly Closure $next,
        protected readonly Closure $mapper,
    ) {}

    /**
     * Get next page.
     *
     * @return  TValue[]|null
     */
    public function next(): ?array
    {
        if (is_null($nextPage = call_user_func($this->next, $this->current))) {
            return null;
        }

        $this->current = $nextPage;

        return ($this->mapper)($this->current);
    }

    /**
     * Loop through each item.
     *
     * @param  callable(TValue): mixed  $callback
     * @return $this
     */
    public function each(callable $callback): static
    {
        while ($items = $this->next()) {
            foreach ($items as $item) {
                $callback($item);
            }
        }

        return $this;
    }

    /**
     * Collect items from all pages.
     *
     * @return TValue[]
     */
    public function all(): array
    {
        $results = [];

        while ($items = $this->next()) {
            array_push($results, ...$items);
        }

        return $results;
    }
}
