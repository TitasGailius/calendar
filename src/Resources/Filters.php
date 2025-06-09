<?php

namespace TitasGailius\Calendar\Resources;

use DateTimeInterface;
use ReflectionClass;
use InvalidArgumentException;

readonly class Filters
{
    /**
     * Instantiate a new filters instance.
     *
     * @param  array<string, mixed>|null  $metadata
     */
    public function __construct(
        public ?DateTimeInterface $start = null,
        public ?DateTimeInterface $end = null,
        public bool $expand = false,
        public ?int $limit = null,
        public ?string $search = null,
        public ?array $metadata = null,
        public string $calendar = 'primary',
    ) {}

    /**
     * Apply options for the current filters.
     *
     * @param  array<string, callable(string): string>  $generators
     */
    public function toRequestOptions(array $generators): array
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
