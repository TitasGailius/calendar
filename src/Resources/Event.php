<?php

namespace TitasGailius\Calendar\Resources;

use DateTimeInterface;
use TitasGailius\Calendar\Resources\Recurrence;

class Event extends Resource
{
    /**
     * Event's id.
     */
    public string $id;

    /**
     * Event's provider.
     */
    public string $provider;

    /**
     * Raw data.
     */
    public mixed $raw;

    /**
     * Instantiate a new event instance.
     *
     * @param  \TitasGailius\Calendar\Resources\Attendee[]|string[]  $attendees
     */
    public function __construct(
        public string $title,
        public DateTimeInterface $start,
        public DateTimeInterface $end,
        public string $calendar = 'primary',
        public array $attendees = [],
        public ?Recurrence $recurrence = null,
        public ?Organiser $organiser = null,
        public array $metadata = [],
    ) {}

    /**
     * Indicate that the event is existing.
     *
     * @return $this
     */
    public function existing(string $id, string $provider, mixed $raw): static
    {
        $this->id = $id;
        $this->provider = $provider;
        $this->raw = $raw;

        return $this;
    }
}
