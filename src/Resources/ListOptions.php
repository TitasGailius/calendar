<?php

namespace TitasGailius\Calendar\Resources;

use DateTimeInterface;

class ListOptions extends Resource
{
    /**
     * Instantiate a new list options instance.
     */
    public function __construct(
        public readonly DateTimeInterface $start = null,
        public readonly DateTimeInterface $end = null,
        public readonly string $search = null,
    ) {}
}
