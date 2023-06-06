<?php

namespace TitasGailius\Calendar\Resources;

use Carbon\Carbon;
use ReflectionClass;
use InvalidArgumentException;

class Filters
{
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
        public readonly string $calendar = 'primary',
    ) {}

    /**
     * Parse the given filters.
     */
    public static function parse(string|Event|Filters|null $filters): Filters
    {
        return match (true) {
            is_null($filters) => new Filters,
            is_string($filters) => new Filters(id: $filters),
            $filters instanceof Event => new Filters(id: $filters->id, calendar: $filters->calendar),
            default => $filters,
        };
    }

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
                throw new InvalidArgumentException("Filters {$key} does not exist on the Filters instance.");
            }

            if (! $value = $this->{$key}) {
                continue;
            }

            $result = array_merge($result, $generator($value));
        }

        return $result;
    }
}
