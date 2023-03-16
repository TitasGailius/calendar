<?php

namespace TitasGailius\Calendar\Contracts;

interface Factory
{
    /**
     * Resolve provider.
     */
    public function provider(?string $name = null): Provider;
}
