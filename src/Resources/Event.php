<?php

namespace TitasGailius\Calendar\Resources;

use DateTimeInterface;

class Event extends Resource
{
    /**
     * Instantiate a new event instance.
     *
     * @param  array<int,\TitasGailius\Calendar\Resources\Attendee>  $attendees
     * @param  mixed[]  $raw
     */
    public function __construct(
        public ?string $title = null,
        public ?DateTimeInterface $start = null,
        public ?DateTimeInterface $end = null,
        public ?Organiser $organiser = null,
        public array $attendees = [],
        public string $calendar = 'primary',
        public ?string $id = null,
        public array $raw = [],
    ) {}

    /**
     * Parse the given event.
     */
    public static function parse(string|Event $event): Event
    {
        return is_string($event) ? new Event(id: $event) : $event;
    }
}
