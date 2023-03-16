<?php

namespace TitasGailius\Calendar\Resources;

use DateTimeInterface;

class Calendar
{
    /**
     * Instantiate a new calendar instance.
     */
    public function __construct(
        public string $id,
        public string $name,
    ) {}
}
