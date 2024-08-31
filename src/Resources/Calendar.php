<?php

namespace TitasGailius\Calendar\Resources;

use DateTimeInterface;

/**
 * @template TValue
 */
class Calendar extends Resource
{
    /**
     * Instantiate a new calendar instance.
     *
     * @param  TValue $raw
     */
    public function __construct(
        public readonly string $provider,
        public readonly string $id,
        public readonly string $name,
        protected readonly mixed $raw = null,
    ) {}

    /**
     * Get raw event data.
     *
     * @return TValue
     */
    public function getRaw(): mixed
    {
        return $this->raw;
    }
}
