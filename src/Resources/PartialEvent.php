<?php

namespace TitasGailius\Calendar\Resources;

use DateTimeInterface;
use TitasGailius\Calendar\Resources\Recurrence;

readonly class PartialEvent
{
    /**
     * Instantiate a new event instance.
     *
     * @param  \TitasGailius\Calendar\Resources\Attendee[]|string[]  $attendees
     * @param  array<string, string>  $metadata
     */
    public function __construct(
        public string $id,
        public string $calendar,
        public ?string $title,
        public ?DateTimeInterface $start,
        public ?DateTimeInterface $end,
        public array $attendees = [],
        public ?Recurrence $recurrence = null,
        public ?Organiser $organiser = null,
        public array $metadata = [],
    ) {}
}
