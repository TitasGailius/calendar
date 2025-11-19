<?php

namespace TitasGailius\Calendar\Exceptions;

use Exception;

class RefreshTokenExpiredException extends Exception
{
    /**
     * Instantiate a new exception instance.
     */
    public function __construct(public readonly string $provider)
    {
        parent::__construct(ucfirst($provider).' refresh token expired.');
    }
}
