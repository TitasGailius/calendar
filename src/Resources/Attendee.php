<?php

namespace TitasGailius\Calendar\Resources;

readonly class Attendee
{
    /**
     * Instantiate a new attendee instance.
     */
    public function __construct(
        public string $email,
        public ?string $name = null,
        public ?Rsvp $rsvp = null,
    ) {}
}
