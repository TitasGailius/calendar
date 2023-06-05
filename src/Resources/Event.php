<?php

namespace TitasGailius\Calendar\Resources;

use DateTimeInterface;
use TitasGailius\Calendar\Resources\Attendee;
use TitasGailius\Calendar\Resources\Recurrence;

class Event extends Resource
{
    /**
     * Instantiate a new event instance.
     *
     * @param  \TitasGailius\Calendar\Resources\Attendee[]|string[]  $attendees
     * @param  mixed[]  $raw
     */
    public function __construct(
        public string $title,
        public DateTimeInterface $start,
        public DateTimeInterface $end,
        public ?Organiser $organiser = null,
        public array $attendees = [],
        public string $calendar = 'primary',
        public ?Recurrence $recurrence = null,
        public ?string $id = null,
        public array $raw = [],
    ) {}
}
