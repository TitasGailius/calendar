<?php

namespace TitasGailius\Calendar\Resources;

use ArrayAccess;
use Countable;
use ArrayIterator;
use Traversable;
use IteratorAggregate;
use JsonSerializable;

/**
 * @template TValue
 *
 * @implements \ArrayAccess<int, TValue>
 * @implements \IteratorAggregate<int, TValue>
 */
abstract class Collection implements ArrayAccess, Countable, IteratorAggregate, JsonSerializable
{
    /**
     * Instantiate a new collection instance.
     *
     * @param  TValue[]  $items
     */
    final public function __construct(protected array $items)
    {
        $this->items = $items;
    }

    /**
     * Determine if the current collection is empty.
     *
     * @return boolean
     */
    public function isEmpty(): bool
    {
        return count($this->items) === 0;
    }

    /**
     * Determine if the current collection is not empty.
     *
     * @return boolean
     */
    public function isNotEmpty(): bool
    {
        return ! $this->isEmpty();
    }

    /**
     * Loop through each collection item.
     *
     * @param  callable(TValue): mixed  $callback
     * @return $this
     */
    public function each(callable $callback): static
    {
        foreach ($this->items as $item) {
            $callback($item);
        }

        return $this;
    }

    /**
     * Merge items to the current collection.
     *
     * @param  TValue[]|\TitasGailius\Calendar\Resources\Collection<TValue>  $items
     */
    public function merge(Collection|array $items): static
    {
        if ($items instanceof Collection) {
            $items = $items->all();
        }

        return new static(array_merge($this->items, $items));
    }

    /**
     * Make a new collection instance.
     *
     * @param  TValue[]  $items
     */
    public function newInstance(array $items = []): static
    {
        return new static($items);
    }

    /**
     * Get collection items.
     *
     * @return TValue[]
     */
    public function all(): array
    {
        return $this->items;
    }

    /**
     * Determine if an item exists at an offset.
     *
     * @param  int  $key
     * @return bool
     */
    public function offsetExists(mixed $key): bool
    {
        return isset($this->items[$key]);
    }

    /**
     * Get an item at a given offset.
     *
     * @param  int  $key
     * @return ?TValue
     */
    public function offsetGet(mixed $key): mixed
    {
        return $this->items[$key];
    }

    /**
     * Set the item at a given offset.
     *
     * @param  int|null  $key
     * @param  TValue  $value
     */
    public function offsetSet(mixed $key, mixed $value): void
    {
        if (is_null($key)) {
            $this->items[] = $value;
        } else {
            $this->items[$key] = $value;
        }
    }

    /**
     * Unset the item at a given offset.
     *
     * @param  int  $key
     */
    public function offsetUnset(mixed $key): void
    {
        unset($this->items[$key]);
    }

    /**
     * Count the number of events.
     */
    public function count(): int
    {
        return count($this->items);
    }

    /**
     * Get an iterator for the items.
     *
     * @return \ArrayIterator<int, TValue>
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->items);
    }

    /**
     * Serialize collection to JSON.
     */
    public function jsonSerialize(): array
    {
        return $this->all();
    }
}
