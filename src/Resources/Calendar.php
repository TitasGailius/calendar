<?php

namespace TitasGailius\Calendar\Resources;

/**
 * @template TRaw
 */
readonly class Calendar
{
    /**
     * Instantiate a new calendar instance.
     *
     * @param  TRaw  $raw
     */
    public function __construct(
        public string $provider,
        public string $id,
        public string $name,
        protected mixed $raw = null,
    ) {}

    /**
     * Get raw event data.
     *
     * @return TRaw
     */
    public function getRaw(): mixed
    {
        return $this->raw;
    }
}
