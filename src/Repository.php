<?php

namespace TitasGailius\Calendar;

use TitasGailius\Calendar\Contracts\Provider;

class Repository
{
    /**
     * Instantiate a new repository instance.
     */
    public function __construct(
        protected Provider $provider,
    ) {}

    /**
     * Get the Provider instance.
     */
    public function getProvider(): Provider
    {
        return $this->provider;
    }
}
