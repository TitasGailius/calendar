<?php

namespace TitasGailius\Calendar\Resources;

class Attendee extends Resource
{
    /**
     * Instantiate a new attendee instance.
     */
    public function __construct(
        public string $email,
        public ?Rsvp $rsvp = null,
    ) {}
}
