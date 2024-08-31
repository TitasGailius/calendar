<?php

namespace TitasGailius\Calendar\Resources;

use Carbon\Carbon;
use ReflectionClass;
use InvalidArgumentException;

class Selector
{
    /**
     * Instantiate a new selector instance.
     */
    public function __construct(
        public readonly ?string $id = null,
        public readonly string $calendar = 'primary',
    ) {}

    /**
     * Parse the given filters.
     */
    public static function parse(string|Event|Selector $selector): Selector
    {
        return match (true) {
            is_string($selector) => new Selector(id: $selector),
            $selector instanceof Event => new Selector(id: $selector->id, calendar: $selector->calendar),
            default => $selector,
        };
    }
}
