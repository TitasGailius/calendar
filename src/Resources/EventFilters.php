<?php

namespace TitasGailius\Calendar\Resources;

use Carbon\Carbon;
use ReflectionClass;
use InvalidArgumentException;

class EventFilters
{
    /**
     * Parse the given filters.
     */
    public static function parse(string|Event|EventFilters|null $filters): EventFilters
    {
        return match (true) {
            is_null($filters) => new EventFilters,
            is_string($filters) => new EventFilters(id: $filters),
            $filters instanceof Event => new EventFilters(id: $filters->id, calendar: $filters->calendar),
            default => $filters,
        };
    }

    /**
     * Instantiate a new filters instance.
     */
    public function __construct(
        public readonly ?string $id = null,
        public readonly ?Carbon $start = null,
        public readonly ?Carbon $end = null,
        public readonly bool $expand = false,
        public readonly ?int $limit = null,
        public readonly ?string $search = null,
        public readonly array $options = [],
        public readonly string $calendar = 'primary',
    ) {}

    /**
     * Generate options for the current filters.
     *
     * @param  array<string, callable>  $generators
     */
    public function options(array $generators): array
    {
        $result = [];

        foreach ($generators as $key => $generator) {
            if (! property_exists($this, $key)) {
                throw new InvalidArgumentException("Filters {$key} does not exist on the EventFilters instance.");
            }

            if (! $value = $this->{$key}) {
                continue;
            }

            $result = array_merge($result, $generator($value));
        }

        return $result;
    }
}
