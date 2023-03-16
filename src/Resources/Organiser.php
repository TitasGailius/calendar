<?php

namespace TitasGailius\Calendar\Resources;

class Organiser
{
    /**
     * Instantiate a new organiser instance.
     */
    public function __construct(
        public readonly string $email,
    ) {}
}

