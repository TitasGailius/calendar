<?php

namespace TitasGailius\Calendar\Resources;

use DateTimeInterface;
use ReflectionClass;
use InvalidArgumentException;

class Filters
{
    /**
     * Instantiate a new filters instance.
     */
    public function __construct(
        public readonly ?DateTimeInterface $start = null,
        public readonly ?DateTimeInterface $end = null,
        public readonly bool $expand = false,
        public readonly ?int $limit = null,
        public readonly ?string $search = null,
        public readonly string $calendar = 'primary',
    ) {}

    /**
     * Parse the given filters.
     */
    public static function parse(Filters|null $filters): Filters
    {
        return $filters ?? new Filters;
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
