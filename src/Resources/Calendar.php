<?php

namespace TitasGailius\Calendar\Resources;

use DateTimeInterface;

class Calendar extends Resource
{
    /**
     * Instantiate a new calendar instance.
     *
     * @param  mixed[]  $raw
     */
    public function __construct(
        public readonly string $provider,
        public readonly string $id,
        public readonly string $name,
        public readonly array $raw,
    ) {}
}
