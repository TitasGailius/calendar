<?php

namespace TitasGailius\Calendar\Resources;

use DateTimeInterface;
use TitasGailius\Calendar\Contracts\Repository;
use TitasGailius\Calendar\Resources\Recurrence;

readonly class Event
{
    /**
     * Repository that retrieved the event.
     */
    protected Repository $repository;

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
     * @param  array<string, string>  $metadata
     */
    public function __construct(
        public string $title,
        public DateTimeInterface $start,
        public DateTimeInterface $end,
        public array $attendees = [],
        public Organiser|string $organiser,
        public ?Recurrence $recurrence = null,
        public array $metadata = [],
        public string $calendar = 'primary',
    ) {}

    /**
     * Indicate that the event is existing.
     *
     * @return $this
     */
    public function existing(Repository $repository, string $id, mixed $raw): static
    {
        $this->repository = $repository;
        $this->id = $id;
        $this->raw = $raw;
        $this->provider = $repository->getName();

        return $this;
    }
}
