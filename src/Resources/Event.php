<?php

namespace TitasGailius\Calendar\Resources;

use DateTimeInterface;
use TitasGailius\Calendar\Resources\Attendee;
use TitasGailius\Calendar\Resources\Recurrence;

/**
 * @template TValue
 */
class Event extends Resource
{
    /**
     * Instantiate a new event instance.
     *
     * @param  \TitasGailius\Calendar\Resources\Attendee[]|string[]  $attendees
     * @param  TValue  $raw
     */
    public function __construct(
        public string $title,
        public DateTimeInterface $start,
        public DateTimeInterface $end,
        public string $calendar = 'primary',
        public array $attendees = [],
        public ?Recurrence $recurrence = null,
        public ?Organiser $organiser = null,
        public ?string $id = null,
        public ?string $provider = null,
        protected mixed $raw = null,
    ) {}

    /**
     * Get raw event data.
     *
     * @return TValue
     */
    public function getRaw(): mixed
    {
        return $this->raw;
    }
}
