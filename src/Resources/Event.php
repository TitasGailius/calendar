<?php

namespace TitasGailius\Calendar\Resources;

use DateTimeInterface;

class Event
{
    /**
     * Event ID.
     */
    public string $id;

    /**
     * Instantiate a new event instance.
     *
     * @param  array<int,\TitasGailius\Calendar\Attendee>
     */
    public function __construct(
        public string $subject,
        public array $attendees,
        public DateTimeInterface $start,
        public DateTimeInterface $end,
        public Organiser $organiser = null,
        public string $calendar = 'primary',
    ) {}

    /**
     * Set event ID.
     */
    public function setId(string $id): static
    {
        $this->id = $id;

        return $this;
    }
}
